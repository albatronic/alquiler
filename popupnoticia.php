<?php
session_start();
if ($_SESSION['iu']=='') exit;
require "engancha.php";
$id=$_GET['id'];
$res=mysql_query("select Noticia from noticias where (IDNoticia=$id)");
$row=mysql_fetch_array($res);


?>
<html>
<head>
<title><?php echo $titulo;?></title>
<link href="estilos.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body background="images/fondo_cuadritos.gif">
<table width="100%" class="ta18pxazul">
<tr align="center"><td><?php echo $row['Noticia'];?></td></tr>
</table>
</body>
</html>
