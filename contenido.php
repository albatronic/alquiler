<?php
    session_start();
    require "modulos.php";
    require "engancha.php";
	$contenido=$_GET['c'];
	if ($contenido=='') $contenido=$_POST['c'];
	if ($contenido=='') $contenido='login';
	$t=$_GET['t'];
?>
<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <title><?echo $t;?></title>
    <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body background="">
<table width="100%" border="0">
    <tr>
		<td width="100%" align="center" valign="top" id="CONTENIDO" bgcolor="#FFFFFF" class="formularios">
    	  <?php
            if (file_exists($contenido.".php")) require $contenido.".php";
            else echo "<b>NO ESTA DISPONIBLE LA OPCION INDICADA. CONSULTE AL ADMINISTRADOR</b>";
          ?>
	    </td>
	</tr>
</table>

<script type="text/javascript" language="javascript" src="funciones/tooltip.js"></script>
</body>
</html>
