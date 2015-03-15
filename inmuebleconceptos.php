<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "engancha.php";

$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
if ($id=="") {Mensaje("Se ha perdido la vinculación con el inmueble."); exit;}
?>


<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<title>:: Conceptos del Inmueble</title>
</head>

<frameset rows="22,*" border=0 frameborder=0 framespacing=0>
	<frame src="frameinmu.php?id=<?echo $id;?>" name="arriba" marginwidth=0 marginheight=0 scrolling=no>

    <frameset cols="330,*" border=0 frameborder=0 framespacing=0 name="centro">
	    <frame src="frameinmuconce.php?id=<?echo $id;?>" name="izquierda" scrolling="no" marginwidth=0 marginheight=0>
		<frame src="contenido.php?c=conceptos&id=<?echo $id;?>" name="derecha" scrolling="yes" marginwidth=0 marginheight=0>
	</frameset>
</frameset>

<noframes>
Tu navegador no soporta frames...
</noframes>

</html>
