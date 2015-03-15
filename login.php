<?php
//session_start();
$accion=$_POST['accion'];

if ($accion=='login') {
	$login=$_POST['usuario'];
	$password=md5($_POST['password']);
	require "engancha.php";
	$res=mysql_query("select * from agentes where (Login='$login' and Activo=1)");
	if ($row=mysql_fetch_array($res)) {
		if ($row['Password']==$password) {
			$_SESSION['iu']=$row[0];
			$_SESSION['esadm']=($row['Administrador']==1);
			$_SESSION['almacen']=1;
			$_SESSION['caja']=10;
			$nlogin=$row['NLogin']+1;
			$ahora=date("Y-m-d H:i:s");
			$res=mysql_query("update agentes set NLogin=$nlogin, UltimoLogin='$ahora' where IDAgente=$row[0]");
			CierraVentana();
            RecargaVentana("servlet.php");
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



<table width="100%" height="100%" border="0" valign="middle" cellspacing="0" cellpadding="0">
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

