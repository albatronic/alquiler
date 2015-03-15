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
$nombre=$_POST['Nombre'];

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE provincias SET NOMBRE='$nombre' WHERE CODIGO='$id' limit 1;";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
        break;

	case 'Borrar':
        $res=mysql_query("select count(IDProvincia) from clientes where IDProvincia=$id");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." clientes de esa provincia.";
        $res=mysql_query("select count(IDProvincia) from proveedores where IDProvincia=$id");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." proveedores de esa provincia.";
        $res=mysql_query("select count(IDProvincia) from sucursales where IDProvincia=$id");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." sucursales de esa provincia.";

        if ($mensaje!='') Mensaje($mensaje);
        else {
    		$sql="DELETE FROM provincias WHERE CODIGO='$id' LIMIT 1;";
	       	$res=mysql_query($sql);
    		if ($res) Limpia();
	       	else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
        }
		break;

	case 'Crear':
		if (($nombre!='') and ($id!='')) {
			$sql="INSERT INTO provincias (`CODIGO`,`NOMBRE`) VALUES ('$id','$nombre');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar la nueva provincia. Inténtelo de nuevo.");
		} else Mensaje("Debe indicar un código y un nombre.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from provincias where CODIGO='$id'");
		$row=mysql_fetch_array($res);
		$id=$row['CODIGO']; $nombre=$row['NOMBRE'];
		break;
}

function Limpia(){
	global $id,$nombre;
	$id='';$nombre='';
};

$parametros="";

function Listado(){
	global $pagina,$parametros;
	$gris="#CCCCCC";
	$tampagina=15;;
    		
	$sql="select * from provincias order by CODIGO";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios"><tr><td align="center" class="blancoazul">PROVINCIAS</td></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=provincias&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
        <td width="16"></td>
        <td><strong>Código</strong></td>
        <td><strong>Provincia</strong></td>
  	</tr>
<?php	
	Subrrayado(3);

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
		    	<td width="16"><a href="contenido.php?c=provincias&Accion=Editar&id=<?php echo $row['CODIGO'];?>&pagina=<?php echo $pagina;?>"><img src="images/botoneditar.png" border="0"</a></td>
                <td><?php echo $row['CODIGO'];?></td>
                <td><?php echo $row['NOMBRE'];?></td>
            </tr>
<?php
			Subrrayado(3);
		}
	}?>

	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=provincias&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?php
}

function Formulario(){
global $id,$nombre,$pagina;
?>
<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Mantenimiento de Provincias</td></tr>
<tr><td>
<table width="100%" align="center" class="ComboFamilias">
  <form name="formulario" action="contenido.php" method="post">
  	<input name="c" type="hidden" value="provincias">
  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
    <tr><TD>CODIGO:</TD><td><input name="id" type="text" size="2" maxlength="2" value="<?php echo $id;?>" class="formularios" <?php if ($id!='') echo "readonly";?>></td></tr>
	<tr><TD>TITULO:</TD><td><input name="Nombre" type="text" size="32" maxlength="30" value="<?php echo $nombre;?>" class="formularios"></td></tr>

	<tr><td colspan="2" align="center">
		<?php if ($id!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.id.focus();
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


