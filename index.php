<?php
session_start();

$i=-1;
$vconect=array();
if (file_exists("conexion.php")){
    $f=fopen("conexion.php","r");
    $kk=fgets($f,4096); //Me salto la primera l�nea = '<?php'
    while (!feof($f) and ($i<5)) {
        // Leo l�nea a l�nea y quito los 2 primeros caracteres (//) y los 2 �ltimos caracteres de la l�nea (CR+LF)
        $i++;
        $vconect[$i]=fgets($f,4096);
        $vconect[$i]=substr($vconect[$i],2,strlen($vconect[$i])-4);
    }
    fclose($f);
} else {echo "FALTAN LOS PARAMETROS DE CONEXION."; exit;}


$_SESSION['SERVIDORDB']=$vconect[0];
$_SESSION['USUARIODB']=$vconect[1];
$_SESSION['PASSWORDDB']=$vconect[2];
$_SESSION['DBEMP']=$vconect[3];
$_SESSION['DBDAT']=$vconect[4];

//print_r($_SESSION);

$accion=$_POST['accion'];

if ($accion=='login') {
	$login=$_POST['usuario'];
	$password=md5($_POST['password']);
	require "conecta.php";
	$res=mysql_query("select * from agentes where (Login='$login' and Activo=1)");
	if ($row=mysql_fetch_array($res)) {
		if ($row['Password']==$password) {
			$_SESSION['iu']=$row['IDAgente'];
			$_SESSION['login']=$row['Nombre'];
			$_SESSION['esadm']=($row['Administrador']==1);
			$_SESSION['empresa']=$row['IDEmpresa'];
			$_SESSION['sucursal']=$row['IDSucursal'];
			$_SESSION['caja']=10;
			$nlogin=$row['NLogin']+1;
			$ahora=date("Y-m-d H:i:s");
			$res=mysql_query("update agentes set NLogin=$nlogin, UltimoLogin='$ahora' where IDAgente=$row[0]");
			include "servlet.php";
			exit;
		}
		else echo "<script language='JavaScript' type='text/JavaScript'>
					alert('Password Incorrecta')
					</script>";	
	} else  echo "<script language='JavaScript' type='text/JavaScript'>
					alert('Usuario no registrado')
					</script>";
}
?>


<html>
<head>
<title>.:: Gestión de Alquileres ::.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td width="100%" height="100%" align="center" valign="middle">
<table width="295" height="315" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td colspan="5" width="295" height="1" class="dfondorojo"><img src="images/subrrayado.gif" width="1" height="1" border="0"></td>
  </tr>
  <tr> 
  	<td width="275" height="120" align="left" valign="top">
	<table width="275" height="120" border="0" cellspacing="0" cellpadding="0" background="images/fondo_cuadritos.gif" class="bordesazules">
              <form name="login" method="post" action="index.php">
				<input type="hidden" name="PHPSESSID" value="SESSION_ID()">
				<input type="hidden" name="accion" value="login">
              <tr>
                <td width="275" height="34" colspan="3"><img src="images/espacio.gif" width="1" height="1" border="0"></td>
              </tr>
              <tr>
                <td height="15" align="right" class="dtxrojo11">Usuario:</td>
                <td width="10" class="dtxrojo11"><img src="images/espacio.gif" width="10" height="15" border="0"></td>
				<td width="159"><input type="text" name="usuario" class="dcampostexto" size="15"></td>
              </tr>			  
              <tr>
                <td width="275" height="9" colspan="3"><img src="images/espacio.gif" width="1" height="9" border="0"></td>
              </tr>
              <tr>
                <td height="15" align="right" class="dtxrojo11">Password:</td>
                <td><img src="images/espacio.gif" width="10" height="15" border="0"></td>
				<td><input type="password" name="password" class="dcampostexto" size="15"></td>
              </tr>
              <tr>
                <td width="275" height="9" colspan="3"><img src="images/espacio.gif" width="1" height="9" border="0"></td>
              </tr>
			  <tr>
                <td height="13" align="right"><img src="images/espacio.gif" width="1" height="1" border="0"></td>
                <td><img src="images/espacio.gif" width="10" height="13" border="0"></td>
				<td><img src="images/espacio.gif" width="63" height="1" border="0"><input type="image" src="images/entrar.gif" width="40" height="13"></td>
              <tr>
                <td width="275" height="29" colspan="3"><img src="images/espacio.gif" width="1" height="29" border="0"></td>
              </tr>

<script language="JavaScript">
<!--

function SymError()
{
  return true;
}

window.onerror = SymError;

var SymRealWinOpen = window.open;

function SymWinOpen(url, name, attributes)
{
  return (new Object());
}

window.open = SymWinOpen;

//-->
</script>

</form>

<script language="JavaScript" type="text/javascript">
document.forms[0].usuario.focus();
</script>

</table>
</td>
</tr>
</table></td></tr></table>
</body>
</html>

<script language="JavaScript">
<!--
var SymRealOnLoad;
var SymRealOnUnload;

function SymOnUnload()
{
  window.open = SymWinOpen;
  if(SymRealOnUnload != null)
     SymRealOnUnload();
}

function SymOnLoad()
{
  if(SymRealOnLoad != null)
     SymRealOnLoad();
  window.open = SymRealWinOpen;
  SymRealOnUnload = window.onunload;
  window.onunload = SymOnUnload;
}

SymRealOnLoad = window.onload;
window.onload = SymOnLoad;

//-->
</script>

