<?php

if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";
require "funciones/desplegable.php";
require "funciones/textos.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];

$idconcepto=$_POST['IDConcepto']; if ($idconcepto=='') $idconcepto=$_GET['IDConcepto'];
$concepto=$_POST['Concepto'];
$consumo=$_POST['Consumo'];
$iva=$_POST['Iva'];
$precio=$_POST['Precio'];
$subeautomatico=$_POST['SubeAutomatico'];
$mediacion=$_POST['Mediacion'];
$diferencia=$_POST['Diferencia'];

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
        if ($precio=='') $precio=0;
        if ($concepto!='') {
            $concepto=CodificaTexto($concepto);
            $sql="UPDATE conceptos SET Concepto='$concepto', Consumo='$consumo', Iva='$iva', Precio='$precio', SubeAutomatico='$subeautomatico', CobroMediacion='$mediacion', Diferencia='$diferencia' WHERE IDConcepto='$idconcepto' limit 1;";
            $res=mysql_query($sql);
            if ($res) Limpia();
            else Mensaje ("No se han guardado los datos correctamente. Intentelo de nuevo.");
        } else Mensaje("Debe indicar un Concepto.");
        break;

	case 'Borrar':
        $mensaje="";
        $sql="select count(IDConcepto) from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".inmuebles_conceptos where IDConcepto='$idconcepto';";
        $res=mysql_query($sql);
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." inmuebles con ese concepto.";

        $sql="select count(IDConcepto) from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".recibos_lineas where IDConcepto='$idconcepto';";
        $res=mysql_query($sql);
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." recibos con ese concepto.";

        if ($mensaje!='') Mensaje($mensaje);
        else {
    		$sql="DELETE FROM conceptos WHERE IDConcepto='$idconcepto' LIMIT 1;";
	       	$res=mysql_query($sql);
    		if ($res) Limpia();
	       	else Mensaje("No se ha podido eliminar. Intentelo de nuevo");
        }
		break;

	case 'Crear':
        $idconcepto=str_pad($idconcepto,2,"0",STR_PAD_LEFT);
		if (($concepto!='') and (strlen($idconcepto)==2)) {
			$sql="INSERT INTO conceptos VALUES ('$idconcepto','$concepto','$consumo','$iva','$precio','$subeautomatico','$mediacion','$diferencia');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else {$id=''; Mensaje("No se ha podido insertar. Intentelo de nuevo.");}
		} else Mensaje("Debe indicar un codigo (2 digitos) y un concepto.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from conceptos where IDConcepto='$idconcepto'");
		$row=mysql_fetch_array($res);
		$idconcepto=$row['IDConcepto']; $concepto=DecodificaTexto($row['Concepto']); $consumo=$row['Consumo'];
        $iva=$row['Iva']; $precio=$row['Precio']; $subeautomatico=$row['SubeAutomatico'];
        $mediacion=$row['CobroMediacion']; $diferencia=$row['Diferencia'];
		break;
}

function Limpia(){
	global $idconcepto,$concepto,$consumo,$iva,$precio,$subeautomatico,$mediacion,$diferencia;
	$idconcepto='';$concepto='';$consumo='';$iva='';$precio='';$subeautomatico='';$mediacion='';$diferencia=0;
};

$parametros="";

function Listado(){
	global $pagina,$parametros,$id;
	$gris="#CCCCCC";
	$tampagina=DameParametro('LOPAP',15);
    		
	$sql="select * from ".$_SESSION['DBEMP'].".conceptos order by IDConcepto";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);

	$l['sql']=$sql;
	$l['titulo']="LISTADO DE CONCEPTOS";
    $l['columnas']="IDConcepto_T_2_N,Concepto_T_40_N,Consumo_T_1_N,Iva_T_1_N,Precio_N_0_N,SubeAutomatico_T_0_N,CobroMediacion_T_1_N";
	$l['filtro']="";

?>
<table width="100%" align="center" class="formularios"><tr><td align="center" class="blancoazul">CONCEPTOS FACTURABLES</td></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="9">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=conceptos&id=$id&pagina=","left",$gris,$l);?>
	</td></tr>
	<tr class="Formularios">
        <th></th>
        <th>Cod</th>
        <th>Concepto</th>
        <th>Consumo?</th>
        <th>Iva?</th>
        <th>Precio Std</th>
        <th>Sube Auto?</th>
        <th>Mediacion?</th>
        <th>Diferencia</th>
  	</tr>
<?php	
	Subrrayado(9);
	$claro="#C0C0C0";
	$oscuro="#808080";
	
	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
            if ($row['Consumo']=="S") $color=$claro; else $color=$oscuro;
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>" bgcolor="<?php echo $color;?>"
       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$color,"');";?>"
            >
    		    <td width="16"><a href="frameinmuconce.php?Accion=Crear&id=<?php echo $id;?>&IDConcepto=<?php echo $row['IDConcepto'];?>&Importe=<?php echo $row['Precio'];?>" target="izquierda"><img src="images/insertar.png" border="0" alt="Asignar al Inmueble"></a></td>
		    	<td><a href="contenido.php?c=conceptos&Accion=Editar&IDConcepto=<?php echo $row['IDConcepto'];?>&pagina=<?php echo $pagina;?>&id=<?php echo $id;?>"><?php echo $row['IDConcepto'];?></a></td>
                <td><?php echo DecodificaTexto($row['Concepto']);?></td>
                <td align="center"><?php echo $row['Consumo'];?></td>
                <td align="center"><?php echo $row['Iva'];?></td>
                <td align="right"><?php echo $row['Precio'];?></td>
                <td align="center"><?php echo $row['SubeAutomatico'];?></td>
                <td align="center"><?php echo $row['CobroMediacion'];?></td>
                <td align="right"><?php echo $row['Diferencia'];?></td>
            </tr>
<?php
		}
	}?>

	<tr><td colspan="9">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=conceptos&id=$id&pagina=","right",$gris,$l);?>
	</td></tr>
<?php
}

function Formulario(){
global $idconcepto,$concepto,$consumo,$iva,$precio,$subeautomatico,$mediacion,$diferencia,$pagina,$id;

if ($precio=='') $precio=0;
?>
<tr><td align="center" class="BlancoAzul" colspan="9">Mantenimiento de Conceptos</td></tr>
<tr>
  <form name="formulario" action="contenido.php" method="post">
  	<input name="c" type="hidden" value="conceptos">
  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
  	<input name="id" type="hidden" value="<?php echo $id;?>">
    <td></td>
    <td><input name="IDConcepto" type="text" size="2" maxlength="2" value="<?php echo $idconcepto;?>" class="formularios" <?php if ($idconcepto!='') echo "readonly";?>></td>
	<td><input name="Concepto" type="text" size="32" maxlength="50" value="<?php echo $concepto;?>" class="formularios"></td>
	<td align="center"><?php DesplegableSN("Consumo",$consumo,"formularios");?></td>
	<td align="center"><?php DesplegableSN("Iva",$iva,"formularios");?></td>
	<td align="center"><input name="Precio" type="text" size="10" maxlength="11" value="<?php echo $precio;?>" class="formularios"></td>
	<td align="center"><?php DesplegableSN("SubeAutomatico",$subeautomatico,"formularios");?></td>
	<td align="center"><?php DesplegableSN("Mediacion",$mediacion,"formularios");?></td>
	<td align="center"><input name="Diferencia" type="text" size="10" maxlength="11" value="<?php echo $diferencia;?>" class="formularios"></td>
</tr>

	<tr><td colspan="9" align="center" class="blancoazul">
		<?php if ($idconcepto!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea ELIMINAR el concepto ",$concepto;?>');">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.IDConcepto.focus();
</script>
</table>
<?php }

Listado();
Formulario();
?>
