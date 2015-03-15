<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];

require "funciones/comprobar_email.php";
require "funciones/textos.php";

$id=$_GET['id'];
$origen=$_GET['o'];

switch ($origen) {
	case 'albaran':
		$res=mysql_query("select IDCliente,Clave,IDAgente from albaranes_cab where IDAlbaran=".$id);
		$asunto="Envío de Albarán";
		$link="Ver Albarán";
		$script="impalbaran.php";
		break;
	
	case 'presupuesto':
		$res=mysql_query("select IDCliente,Clave,IDAgente from presupuestos_cab where IDPresupuesto=".$id);
		$asunto="Envío de Presupuesto";
		$link="Ver Presupuesto";
		$script="imppsto.php";
		break;
	
	case 'factura':
		$res=mysql_query("select IDCliente,Clave from femitidas_cab where IDFactura=".$id);
		$asunto="Envío de Factura";
		$link="Ver Factura";
		$script="impfacturaemitida.php";
		break;
}

$clienteclave=mysql_fetch_array($res);
$res=mysql_query("select EMail from clientes where IDCliente='".$clienteclave['IDCliente']."'");
$row=mysql_fetch_array($res);
$destinatario=$row['EMail'];

if (!comprobar_email($destinatario)) Atras("El Email: ".$destinatario." no es correcto.");

$res=mysql_query("select Nombre,EMail from ".$_SESSION['DBEMP'].".agentes where IDAgente=".$clienteclave['IDAgente']);
$agt=mysql_fetch_array($res);

$res=mysql_query("select * from parametros where IDParametro='PIEPR'");
$par=mysql_fetch_array($res);
$pie=DecodificaTexto($par['Valor']);

$res=mysql_query("select * from parametros where IDParametro='AVISO'");
$par=mysql_fetch_array($res);
$aviso=$par['Valor'];

$res=mysql_query("select * from parametros where IDParametro='URLWE'");
$par=mysql_fetch_array($res);
$urlweb=$par['Valor'];

$responder=$agt['EMail'];
$remite=$agt['EMail'];
$remitente=$agt['Nombre'];

//$cabecera ="Date: ".date("l j F Y, G:i")."\n"; 
$cabecera ="MIME-Version: 1.0\n"; 
$cabecera .="From: ".$remitente."<".$remite.">\n";
//$cabecera .="Cc: contac@apdo.com\n";
$cabecera .="Return-path: ".$remite."\n";
$cabecera .="Reply-To: ".$responder."\n";
$cabecera .="Sender: ".$remite."\n";
$cabecera .="X-Mailer: PHP/". phpversion()."\n";
$cabecera .="Content-Type: text/html; charset=\"ISO-8859-1\"\n";

$mensaje = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
<link href=\"http://".$urlweb."/estilos.css\" rel=\"stylesheet\" type=\"text/css\">
<title>Presupuesto</title>
<BASE href=http://".$urlweb."/".$script."?o=email&amp;clave=".$clienteclave['Clave'].">
</head>
<body background='images/fondo_cuadritos.gif'>
<br><br>
<table width='98%' class='formularios' bgcolor='#FFFFCC' align='center'>
<tr><td><img src='images/logo.jpg' border='0'><br><br></td></tr>\n
<tr><td>
<p>Adjunto le envío un enlace al presupuesto que usted me solicitó: 
<a href='http://".$urlweb."/".$script."?o=email&clave=".$clienteclave['Clave']."'>".$link."</a><br><br>\n".
$pie." <a href='mailto:".$agt['EMail']."'>".$agt['Nombre']."</a><br><br><br><br>\n".
DameDatosEmpresa()."<br><br><br>".
$aviso."
</p>
</td></tr></table>
</body>
</html>";

echo $cabecera,"<br>",$mensaje,"<br>";
if (@mail($destinatario, $asunto, $mensaje, $cabecera)) Atras("Correo enviado con éxito a: ".$destinatario);
else  Atras("Correo NO Enviado a: ".$destinatario);
?>
