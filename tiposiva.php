<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
$tipo=$_POST['Tipo'];
$iva=$_POST['Iva'];
$recargo=$_POST['Recargo'];
$retencion=$_POST['Retencion'];

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE tipos_iva SET Tipo='$tipo', Iva='$iva', Recargo='$recargo', Retencion='$retencion' WHERE IDIva='$id'";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
        break;

	case 'Borrar':
        $res=mysql_query("select count(IDIva) from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".inmuebles_inquilinos where IDIva=$id");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." contratos con ese tipo de iva.";
        if ($mensaje!='') Mensaje($mensaje);
        else {
    		$sql="DELETE FROM tipos_iva WHERE IDIva='$id' LIMIT 1;";
	       	$res=mysql_query($sql);
    		if ($res) Limpia();
	       	else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
        }
		break;

	case 'Crear':
		if ($tipo!='') {
			$sql="INSERT INTO tipos_iva (`Tipo`,`Iva`, `Recargo`, `Retencion`) VALUES ('$tipo','$iva','$recargo','$retencion');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar el nuevo tipo. Inténtelo de nuevo.");
		} else Mensaje("Debe indicar una descripción del tipo.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from tipos_iva where IDIva='$id'");
		$row=mysql_fetch_array($res);
		$id=$row['IDIva']; $tipo=$row['Tipo']; $iva=$row['Iva']; $recargo=$row['Recargo'];
		$retencion=$row['Retencion'];
		break;
}

function Limpia(){
	global $id,$tipo,$iva,$recargo,$retencion;
	$id='';$tipo='';$iva=0;$recargo=0;$retencion=0;
};

$parametros="";

function Listado(){
	global $pagina,$parametros;
	$gris="#CCCCCC";
	$tampagina=15;
    		
	$sql="select * from tipos_iva order by IDIva";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios"><tr><td align="center" class="blancoazul">TIPOS DE IVA</td></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="5">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=tiposiva&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
        <th width="16"></th>
        <th>Tipo</th>
        <th>Iva</th>
        <th>Recargo</th>
        <th>Retencion</th>
  	</tr>
<?php	
	Subrrayado(5);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>"
       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
		    	<td width="16"><a href="contenido.php?c=tiposiva&Accion=Editar&id=<?php echo $row['IDIva'];?>&pagina=<?php echo $pagina;?>"><img src="images/botoneditar.png" border="0"</a></td>
                <td><?php echo $row['Tipo'];?></td>
                <td align="right"><?php echo $row['Iva'];?></td>
                <td align="right"><?php echo $row['Recargo'];?></td>
                <td align="right"><?php echo $row['Retencion'];?></td>
			</tr>
<?php
			Subrrayado(5);
		}
	}?>

	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=tiposiva&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?php
}

function Formulario(){
global $id,$tipo,$iva,$recargo,$retencion,$pagina;
?>
<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Mantenimiento de Tipos de IVA</td></tr>
<tr><td>
<table width="100%" align="center" class="ComboFamilias">
  <form name="formulario" action="contenido.php" method="post">
    <input name="id" type="hidden" value="<?php echo $id;?>">
  	<input name="c" type="hidden" value="tiposiva">
  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
	<tr><TD>Tipo:</TD><td><input name="Tipo" type="text" size="32" maxlength="30" value="<?php echo $tipo;?>" class="formularios"></td></tr>
	<tr><TD>Iva:</TD><td><input name="Iva" type="text" size="6" maxlength="6" value="<?php echo $iva;?>" class="formularios"></td></tr>
	<tr><TD>Recargo:</TD><td><input name="Recargo" type="text" size="6" maxlength="6" value="<?php echo $recargo;?>" class="formularios"></td></tr>
	<tr><TD>Retencion:</TD><td><input name="Retencion" type="text" size="6" maxlength="6" value="<?php echo $retencion;?>" class="formularios"></td></tr>
	<tr><td colspan="2" align="center">
		<?php if ($id!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar el tipo de iva ",$tipo;?>');">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.Tipo.focus();
</script>
</table>
</td></tr>
</table>
<?php
}
?>


<table width="100%">
    <tr valign="top">
        <td width="50%"><?php Listado();?></td>
        <td width="50%"><?php Formulario();?></td>
    </tr>
</table>
