<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "engancha.php";
require "funciones/desplegable.php";

$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
if ($id=="") {Mensaje("Se ha perdido la vinculación con el inmueble."); exit;}

//$res=mysql_query("select IDInmueble,Direccion from inmuebles where IDInmueble='$id';");
//$row=mysql_fetch_array($res);
//$titulo=$row[0]." -- ".$row[1];
?>

<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<title></title>
<link href="estilos.css" rel="stylesheet" type="text/css">

<SCRIPT LANGUAGE="JavaScript">
<!--

function Recarga(id){
	top.izquierda.location='frameinmuconce.php?id='+id;
    top.derecha.location='contenido.php?c=conceptos&id='+id;
	document.location.href='frameinmu.php?id='+id;
}
// -->
</SCRIPT>
</head>
<body>

<table width="100%" class="formularios">
<tr>
    <th><?echo $id; Desplegable('IDInmueble','inmuebles','IDInmueble','Direccion','IDInmueble',$id,'onchange="Recarga(IDInmueble.value);"','','');?></th>
</tr>
</table>

</body>
</html>
