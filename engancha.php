<?php
/* SE CONECTA A MYSQL A LA BASE DE DATOS DE LA EMPRESAS SELECCIONADA */
/* ----------------------------------------------------------------- */
$conectID=@mysql_connect($_SESSION['SERVIDORDB'],$_SESSION['USUARIODB'],$_SESSION['PASSWORDDB']);
$a=@mysql_select_db($_SESSION['DBDAT'].$_SESSION['empresa']);

if ((!$conectID) or (!$a)) {?>
	<html>
	<link href="estilos.css" rel="stylesheet" type="text/css"> 
	<body>
	<table width="100%" align="center" border="0">
	  <tr align="center">
    	<td CLASS="ta18pxazul">GESTION WEB</td>
	  </tr>
	  <tr align="center" valign="middle" height="300">
    	<td class="ta18pxazul">EN ESTOS MOMENTOS ESTAMOS REALIZANDO LABORES DE MANTENIMIENTO EN NUESTRO SERVIDOR.<BR>
			INTENTE CONECTAR EN UNOS MINUTOS. DISCULPEN LAS MOLESTIAS. GRACIAS.
		</td>
	  </tr>
	</table>
	</body>
	</html>
<?php
	exit;
}
?>
