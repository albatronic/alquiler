<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "engancha.php";
require "modulos.php";
require "funciones/fechas.php";
$lopag=DameParametro('LOPAI',42);

$accion=$_GET['accion'];
$desdei=$_GET['desdei'];
$hastai=$_GET['hastai'];
$mes=$_GET['mes'];

$e=$_SESSION['DBEMP'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];

$sql="select * from inmuebles_inquilinos WHERE ((IDInquilino>='$desdei') and (IDInquilino<='$hastai') and (month(FechaSubida)='$mes') and (year(FechaSubida)='".date('Y')."'));";
$res=mysql_query($sql);

function Cabecera($pag){
    global $columna,$valor;

if ($pag>1) {?></table><div style='page-break-after:always'></div><?php }?>

<table ID="CABECERA" width="100%" align="center" valign="top" bgcolor="" class="Formularios" border="0">
	<tr class="ta18pxazul">
        <td align="right">
            <?php if ($pag==1){?>
            <form action="" method="post" name="impresion" id="impresion">
               <input name="imprimir" type="button" id="imprimir" value="Imprimir" onclick="Imprimir()" class="formularios">
            </form>
            <?php } ?>
        </td>
        <td align="center">SUBIDA DEL IPC</td>
        <td align="left">
            <?php if ($pag==1){?>
            <form name="cierre" method="post" action="" id="cerrar">
                <input type="button" name="Submit" value="Cerrar" id="cerrar" onclick="window.close()" class="formularios">
            </form>
            <?php } ?>
        </td>
    </tr>
</table>

<table ID="FILTROS" width="100%" align="center" valign="top" bgcolor="" class="Formularios" border="0">
    <tr>
        <th align="left" width="50%">
            <p><?php echo DameDatosEmpresa();?></p>
        </th>
        <td align="left" width="50%">
            &nbsp
        </td>
    </tr>
    <tr height="20"><td></td></tr>
</table>

<table ID="TITULOS" width="50%" align="center" valign="top" bgcolor="" border="0" class="Formularios">
	<tr class="Formularios">
        <th>Inmueble</th>
        <th>Inquilino</th>
        <th>% Subida</th>
        <th>Fecha Subida</th>
  	</tr>

    <?php Subrrayado(4);
}
?>
<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<title>Cambios IPC</title>
<link href="estilos.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/JavaScript">
function Imprimir()
{
	document.impresion.imprimir.style.visibility="hidden";
	document.cierre.cerrar.style.visibility="hidden";
	window.print();
	document.impresion.imprimir.style.visibility="visible";
	document.cierre.cerrar.style.visibility="visible";
}
</SCRIPT>
</head>
<body topmargin="0"  bgproperties="fixed" bottommargin="0" marginheight="0">
<?
        $i=0;$linea=999999;$pag=0;    	
		while ($row=mysql_fetch_array($res)) {
            if ($linea>=$lopag) {$linea=0; $pag=$pag+1; Cabecera($pag);}
			$i=$i+1;$linea=$linea+1;
		?>
			<tr class='Formularios' id="linea<?php echo $i;?>"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
    			<td><?php echo $row['IDInmueble'];?></td>
		    	<td><?php echo $row['IDInquilino'];?></td>
		    	<td><?php echo $row['PorcentajeSubida'];?></td>
	    		<td><?php echo FechaEspaniol($row['FechaSubida']);?></td>
			</tr>
        <?}?>
</table>
</body>
</html>
