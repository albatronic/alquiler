<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];

$id=$_GET['id'];
if ($id=='') exit;

require "conecta.php";
//require "modulos.php";

$sql="select IDInquilino,RazonSocial,Direccion,Telefono,Fax,EMail,Movil from inquilinos where IDInquilino='$id'";
$res=mysql_query($sql);
if (!$res) exit;
$row=mysql_fetch_array($res);
?>

<html>
<head>
<title>Ficha de Inquilino</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#CCCCCC">
<table width="100%" align="center" class="formularios">
  <tr><td align="right">Código:</td><td><?php echo $row[0];?></td></tr>
  <tr><td align="right">Inquilino:</td><td><strong><?php echo $row[1];?></strong></td></tr>
  <tr><td align="right">Dirección:</td><td><?php echo $row[2];?></td></tr>
  <tr><td align="right">Teléfono:</td><td><?php echo $row[3];?></td></tr>
  <tr><td align="right">Móvil:</td><td><?php echo $row[6];?></td></tr>
  <tr><td align="right">Fax:</td><td><?php echo $row[4];?></td></tr>
  <tr><td align="right">EMail:</td><td><a href="mailto:<?php echo $row[5];?>"><?php echo $row[5];?></a></td></tr>
  <tr>
      <td colspan="2" align="center"><br>
		<a href="javascript:;" onclick="window.close()">Cerrar</a>
      </td>
  </tr>
</table>
</body>
</html>
