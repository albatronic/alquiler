<?php
session_start();
if ($_SESSION['iu']=='') $contenido='login';
else {
	$contenido=$_GET['c'];
	if ($contenido=='') $contenido=$_POST['c'];
	if ($contenido=='') $contenido='inicial';
}
?>

<HTML>
<head>
<title>Web de Gestión de Alquileres</title>
<META  name="description" content="Software de Gestion desarrollado por INFORMATICA ALBATRONIC, SL 958410343">
</head>
<frameset rows="85,*" border=0 frameborder=0 framespacing=0>
	<frame src="arriba.php" marginwidth=0 marginheight=0 scrolling=no name="arriba">

	<frameset cols="150,*" border=0 frameborder=0 framespacing=0 name="centro">
	    <frame src="submenu.php" name="submenu" scrolling="no" marginwidth=0 marginheight=0>
		<frame src="contenido.php?c=<?php echo $contenido;?>" marginwidth=0 marginheight=0 name="contenido">
	</frameset>
</frameset>

<noframes>
Tu navegador no soporta frames...
</noframes>
</HTML>
