<?
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
$columna=$_POST['Columna'];

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE variables SET Columna='$columna' WHERE IDVariable='$id' limit 1;";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
        break;

	case 'Borrar':
    	$sql="DELETE FROM variables WHERE IDVariable='$id' LIMIT 1;";
	    $res=mysql_query($sql);
    	if ($res) Limpia();
	    else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
		break;

	case 'Crear':
		if ($id!='') {
			$sql="INSERT INTO variables (`IDVariable`,`Columna`) VALUES ('$id','$columna');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar la nueva variable. Inténtelo de nuevo.");
		} else Mensaje("Debe indicar un código y un nombre de columna.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from variables where IDVariable='$id'");
		$row=mysql_fetch_array($res);
		$id=$row['IDVariable']; $columna=$row['Columna'];
		break;
}

function Limpia(){
	global $id,$columna;
	$id='';$columna='';
};

$parametros="";

function Listado(){
	global $pagina,$parametros;
	$gris="#CCCCCC";
	$tampagina=15;;
    		
	$sql="select * from variables order by IDVariable";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios"><tr><td align="center" class="blancoazul">VARIABLES PARA LOS CONTRATOS</td></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="3">
	<? Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=variables&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
        <td width="16"></td>
        <td><strong>Código</strong></td>
        <td><strong>Columna</strong></td>
  	</tr>
<?	
	Subrrayado(3);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class="Formularios" id="linea<? echo $i;?>"
       			onmouseover="<? echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      		onmouseout="<? echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
		    	<td width="16"><a href="contenido.php?c=variables&Accion=Editar&id=<? echo $row['IDVariable'];?>&pagina=<? echo $pagina;?>"><img src="images/botoneditar.png" border="0"</a></td>
                <td><? echo $row['IDVariable'];?></td>
                <td><? echo $row['Columna'];?></td>
            </tr>
<?
			Subrrayado(3);
		}
	}?>

	<tr><td colspan="3">
	<? Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=variables&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?
}

function ListaCamposTablas($campo){
//Genera una lista desplegable con los campos de las tablas 'inquilinos','inmuebles' e 'inmuebles_inquilinos'
?>

<select name="Columna" class="formularios">
    <option value=""></option>
    <option value="" class="rojo">DATOS DE LA EMPRESA</option>
<?
    $sql="SHOW COLUMNS FROM ".$_SESSION['DBEMP'].".empresas";
    $res=mysql_query($sql);
    while ($row=mysql_fetch_array($res)){?>
        <option value="empresas><?echo $row[0];?>" <?if($campo=='empresas>'.$row[0]) echo "SELECTED";?>>&nbsp;&nbsp;<?echo $row[0];?></option>
<? }?>
        <option value="empresas>PROVINCIA" <?if($campo=='empresas>PROVINCIA') echo "SELECTED";?>>&nbsp;&nbsp;PROVINCIA</option>

    <option value=""></option>
    <option value="" class="rojo">DATOS DEL INQUILINO</option>
<?
    $sql="SHOW COLUMNS FROM ".$_SESSION['DBEMP'].".inquilinos";
    $res=mysql_query($sql);
    while ($row=mysql_fetch_array($res)){?>
        <option value="inquilinos><?echo $row[0];?>" <?if($campo=='inquilinos>'.$row[0]) echo "SELECTED";?>>&nbsp;&nbsp;<?echo $row[0];?></option>
<? }?>
        <option value="inquilinos>PROVINCIA" <?if($campo=='inquilinos>PROVINCIA') echo "SELECTED";?>>&nbsp;&nbsp;PROVINCIA</option>
        <option value="inquilinos>SALDO" <?if($campo=='inquilinos>SALDO') echo "SELECTED";?>>&nbsp;&nbsp;SALDO</option>

    <option value=""></option>
    <option value="" class="rojo">DATOS DEL INMUEBLE</option>
<?
    $sql="SHOW COLUMNS FROM ".$_SESSION['DBDAT'].$_SESSION['empresa'].".inmuebles";
    $res=mysql_query($sql);
    while ($row=mysql_fetch_array($res)){?>
        <option value="inmuebles><?echo $row[0];?>" <?if($campo=='inmuebles>'.$row[0]) echo "SELECTED";?>>&nbsp;&nbsp;<?echo $row[0];?></option>
<? }?>
        <option value="inmuebles>PROVINCIA" <?if($campo=='inquilinos>PROVINCIA') echo "SELECTED";?>>&nbsp;&nbsp;PROVINCIA</option>
        <option value="inmuebles>SALDO" <?if($campo=='inmuebles>SALDO') echo "SELECTED";?>>&nbsp;&nbsp;SALDO</option>

    <option value=""></option>
    <option value="" class="rojo">DATOS DEL CONTRATO</option>
<?
    $sql="SHOW COLUMNS FROM ".$_SESSION['DBDAT'].$_SESSION['empresa'].".inmuebles_inquilinos";
    $res=mysql_query($sql);
    while ($row=mysql_fetch_array($res)){?>
        <option value="inmuebles_inquilinos><?echo $row[0];?>" <?if($campo=='inmuebles_inquilinos>'.$row[0]) echo "SELECTED";?>>&nbsp;&nbsp;<?echo $row[0];?></option>
<? }?>
        <option value="inmuebles_inquilinos>SALDO" <?if($campo=='inmuebles_inquilinos>SALDO') echo "SELECTED";?>>&nbsp;&nbsp;SALDO</option>
</select>
<?
}

function Formulario(){
global $id,$columna,$pagina;
?>
<table width="100%" border="3" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul" colspan="2">Mantenimiento de Variables</td></tr>
<form name="formulario" action="contenido.php" method="post">
<input name="c" type="hidden" value="variables">
<input name="pagina" type="hidden" value="<? echo $pagina;?>">
<tr>
    <TD>Código:&nbsp&nbsp<input name="id" type="text" size="25" maxlength="25" value="<? echo $id;?>" class="formularios" <? if ($id!='') echo "readonly";?>></td>
	<TD>Columna:&nbsp&nbsp<?ListaCamposTablas($columna);?></td>
</tr>
<tr>
    <td colspan="2" align="center">
		<? if ($id!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios">				
		<? } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<? }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td>
</tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.id.focus();
</script>
</table>
<?
}
?>


<table width="100%">
    <tr><td><? Listado();?></td></tr>
    <tr><td><? Formulario();?></td></tr>
</table>
