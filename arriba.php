<?php
session_start();
if ($_SESSION['iu']=='') exit;
$agente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";
require "funciones/fechas.php";
require "funciones/desplegable.php";

function Mensaje($mensaje){
	echo "<script language='JavaScript' type='text/JavaScript'>
			alert('$mensaje')
		</script>";
}

switch ($_POST['accion']) {
    case '': //Es la primera vez que entra
        $e=$_SESSION['empresa'];
        $s=$_SESSION['sucursal'];
        break;

    case 'ce': //Ha cambiado la empresa. Tengo que cambiar también la sucursal
        $e=$_POST['e'];
        $res=mysql_query("select IDSucursal from ".$_SESSION['DBEMP'].".sucursales where IDEmpresa=$e;");
        if ($row=@mysql_fetch_array($res)){
            $s=$row[0];
            $_SESSION['empresa']=$e;
            $_SESSION['sucursal']=$s;
        } else {
            Mensaje("ESA EMPRESA NO TIENE SUCURSALES.");
            $e=$_SESSION['empresa'];
            $s=$_SESSION['sucursal'];
        }
        break;

    case 'cs': //Mantiene la empresa, pero cambia la sucursal. Desactivo al caja que hubiera
        $s=$_POST['s'];
        if ($s!='') $_SESSION['sucursal']=$s; else $s=$_SESSION['sucursal'];
        $_SESSION['caja']='';
        break;
}

$nombre='';
if ($agente!=''){
    $res=mysql_query("select * from agentes where IDAgente=$agente");
    $row=mysql_fetch_array($res);
    $nombre=$row['Nombre'];
}

if (!$esadm){
    $res=mysql_query("select RazonSocial from ".$_SESSION['DBEMP'].".empresas where IDEmpresa=$e");
    $row=mysql_fetch_array($res);
    $nombreempresa=$row[0];

    $res=mysql_query("select Nombre from ".$_SESSION['DBEMP'].".sucursales where IDEmpresa=$e and IDSucursal=$s");
    $row=mysql_fetch_array($res);
    $nombresucursal=$row[0];
}
?>
<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <title>Arriba</title>
    <link href="estilos.css" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">
function CambiaEmpresa(){
    document.empresa.s.value='';
    document.empresa.accion.value='ce';
	document.empresa.submit();
}

function CambiaSucursal(){
    document.empresa.accion.value='cs';
    document.empresa.submit();
}

function CargaTapiz(){
	top.contenido.location='contenido.php?c=tapiz';
}

function Menu(url){
	top.submenu.location=url;
}
</script>

</head>
<body class="degradadoazul" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" class="menu">
    <tr>
        <td width="150" align="center" valign="center"><img src="images/logo<?php echo $e;?>.jpg" border="0" width="149" height="59"></td>
        <td align="center" valign="top" class="formularios">
			<table width="100%" class="formularios" align="center">
				<TR><TD align="center" class="ta18pxazul"><strong>GESTIÓN COMERCIAL ALBATRONIC</strong></TD></TR>
				<TR>
					<form name="empresa" action="arriba.php" method="post">
					<input name="accion" type="hidden" value="">
					<TD align="center">
                        <strong>Empresa:</strong>
                        <?php
                            if ($esadm) Desplegable('e',$_SESSION['DBEMP'].'.empresas','IDEmpresa','RazonSocial','RazonSocial',$e,'onchange="CambiaEmpresa(); CargaTapiz();"','','');
                            else echo $nombreempresa;
                        ?>
       
					   <strong>Sucursal:</strong>
                        <?php
                            if ($esadm)DesplegableSucursal('s',$e,$s,'onchange="CambiaSucursal(); CargaTapiz();"');
                            else echo $nombresucursal;
                        ?>
					</TD>
					</form>
				</TR>
			</table>	
		</td>
		<td width="15%" align="center" valign="middle" class="formularios">
			<table width="100%" class="formularios" align="center">
				<TR><TD align="center"><?php echo Date('d/M/Y');?></TD></TR>
				<TR><TD align="center"><?php echo $nombre;?></TD></TR>
			</table>	
		</td>
	</tr>

    <tr>
        <td width="150" height="16" align="center">
            <a href="contenido.php?c=inicial" target="contenido"><img src="images/noticias.jpg" height="16" border="0" alt="Tablón de Anuncios"></a>
        </td>

        <td colspan="1" height="16" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" height="16" width="100%" align="center">
            <tr align="center" height="16" class="menu">
            <?php
                $sql="select Administrador from agentes where IDAgente='$agente'";
                $res=mysql_query($sql);
                $row=mysql_fetch_array($res);
                if ($row[0]=='0') //No es administrador
                    $res=mysql_query("select * from menu where Administrador='0' order by Orden");
                if ($row[0]=='1') //Es adminsitrador: se muestran todas los opciones
                    $res=mysql_query("select * from menu order by Orden");
                $i=0;
                while ($row=mysql_fetch_array($res)){
                    if ($i==0){?><td height="16" align="left">&nbsp;</td><?}
                    else {?>
                        <td width="17"><img src="images/separadord.gif" width="17" height="16" border="0" alt=""></td>
                    <?php }?>
                    <td width="50" id="linea<?php echo $i;?>">
						<a href="javascript:;" onclick="Menu('submenu.php?id=<?php echo $row['IDOpcion'],'&t=',$row['Titulo'];?>'); CargaTapiz();"><?php echo $row['Titulo'];?></a>
					</td>
            <?php
                    $i=$i+1;
                }
            ?>
                <td height="16" align="left">&nbsp;</td>
            </tr>
            </table>
        </td>
		<TD align="center"><a href="index.php" target="_top">Cerrar Sesion</a></TD>
    </tr>
</table>
</body>
</html>
