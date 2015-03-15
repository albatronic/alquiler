<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "conecta.php";
include "modulos.php";

$pagina=$_POST['pagina'];
if ($pagina=='') $pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=1;

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
$tipo=$_POST['Tipo'];

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE inmuebles_tipos SET TipoInmueble='$tipo' WHERE IDTipo='$id'";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
        break;

	case 'Borrar':
        $res=mysql_query("select count(IDTipoInmueble) from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".inmuebles where IDTipoInmueble=$id");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) Mensaje("No se puede borrar porque hay ".$n[0]." inmuebles de ese tipo.");
        else {
    		$sql="DELETE FROM inmuebles_tipos WHERE IDTipo='$id' LIMIT 1;";
	       	$res=mysql_query($sql);
    		if ($res) Limpia();
	       	else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
        }
		break;

	case 'Crear':
		if ($tipo!='') {
			$sql="INSERT INTO inmuebles_tipos ( `IDTipo` , `TipoInmueble`)	VALUES ('', '$tipo');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar el nuevo tipo. Inténtelo de nuevo.");
		} else Mensaje("Debe indicar un Tipo de Inmueble.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from inmuebles_tipos where IDTipo='$id'");
		$row=mysql_fetch_array($res);
		$id=$row['IDTipo']; $tipo=$row['TipoInmueble'];
		break;
}

function Limpia(){
	global $id,$tipo;
	$id='';$tipo='';
};

$parametros="";
?>

<script language="JavaScript" type="text/javascript">
function CogeTipo(IDTIPO)
{
	window.opener.formulario.IDTipoInmueble.value=IDTIPO;
	window.close();
}
</script>

<?php

function Listado(){
	global $pagina,$parametros;
	
    $gris="#CCCCCC";
	$tampagina=15;
    		
	$sql="select * from inmuebles_tipos order by TipoInmueble";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
	<tr><td colspan="2">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"inmueblestipos.php?pagina=","left",$gris,'');?>
	</td></tr>
	<tr class="Formularios">
    <td></td>
    <td><strong>Descripción</strong></td>
  	</tr>
<?php	
	Subrrayado(2);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>">
		    	<td width="16"><a href="inmueblestipos.php?Accion=Editar&id=<?php echo $row['IDTipo'];?>&pagina=<?php echo $pagina;?>"><img src="images/botoneditar.png" border="0"</a></td>
			    <td
    				onClick="<?php echo "CogeTipo('",$row['IDTipo'],"')";?>"
	       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
		      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
                >
                    <?php echo $row['TipoInmueble'];?>
                </td>
			</tr>
<?php
			Subrrayado(2);
		}
	}?>

	<tr><td colspan="2">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"inmueblestipos.php?pagina=","right",$gris,'');?>
	</td></tr>
</table>		
<?php
}
?>


<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<link href="estilos.css" rel="stylesheet" type="text/css">
<title>:: Tipos de Inmuebles</title>
</head>
<body>

<?php
	Listado($filtro,$orden);
?>

<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Mantenimiento de Tipos</td></tr>
<tr><td>
<table width="100%" align="center" class="ComboFamilias">
  <form name="formulario" action="inmueblestipos.php" method="post">
  	<input name="id" type="hidden" value="<?php echo $id;?>">
  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
	<tr><TD>TITULO:</TD><td><input name="Tipo" type="text" size="20" maxlength="30" value="<?php echo $tipo;?>" class="formularios"></td></tr>

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
document.formulario.Tipo.focus();
</script>
</table>
</td></tr>
</table>

</body>
</html>
