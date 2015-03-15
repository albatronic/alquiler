<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "modulos.php";
require "conecta.php";
require "funciones/fechas.php";
require "funciones/formatos.php";

$orden=$_GET['orden'];
if($orden=='') $orden="ASC";
$columna=$_GET['columna'];
if($columna=='') $columna="recibos.IDRecibo";

$filtro="$columna>='".$_GET['d']."' and $columna<='".$_GET['h']."'";
if($_GET['fe']!='') $filtro="$filtro and DATE_FORMAT(Fecha,'%Y%m%d')='".AlmacenaFecha($_GET['fe'])."'";

$salto=$_GET['salto'];
if($salto=='') $salto=DameParametro('SAPAG','N');;

$emp=$_SESSION['DBEMP'];
$datos=$_SESSION['DBDAT'].$_SESSION['empresa'];

//DATOS DE LA EMPRESA
    $sql="select ".$_SESSION['DBEMP'].".empresas.*,".$_SESSION['DBEMP'].".provincias.NOMBRE as Provincia from ".$_SESSION['DBEMP'].".empresas,".$_SESSION['DBEMP'].".provincias where (IDEmpresa=".$_SESSION['empresa'].") and (IDProvincia=".$_SESSION['DBEMP'].".provincias.CODIGO)";
    $res=mysql_query($sql);
    $datosempresa=mysql_fetch_array($res);
	$de=$datosempresa['RazonSocial']."<br>";
	$de=$de."CIF: ".$datosempresa['Cif']."<br>";
	$de=$de.$datosempresa['Direccion']."<br>";
	$de=$de.$datosempresa['CodigoPostal']."&nbsp".$datosempresa['Poblacion']."<br>".$datosempresa['Provincia'];



function Recibo($recibo){

global $emp,$datos,$de,$datosempresa,$salto;

$conceptocobro=DameParametro('COBRO','00');

$res=mysql_query("select inquilinos.*,provincias.NOMBRE as Provincia from $emp.inquilinos,$emp.provincias where $emp.inquilinos.IDProvincia=$emp.provincias.CODIGO and IDInquilino='".$recibo['IDInquilino']."';");
$inq=mysql_fetch_array($res);

$res=mysql_query("select * from $datos.inmuebles where IDInmueble='".$recibo['IDInmueble']."';");
$inm=mysql_fetch_array($res);

$sql="select $datos.inmuebles_inquilinos.DireccionRecibo,$emp.tipos_iva.Iva from $datos.inmuebles_inquilinos, $emp.tipos_iva where IDInmueble='".$row['IDInmueble']."' and IDInquilino='".$recibo['IDInquilino']."' and $emp.tipos_iva.IDIva=$datos.inmuebles_inquilinos.IDIva;";
$res=mysql_query($sql);
$dire=mysql_fetch_array($res);

//Datos del inquilino
$datosinqui=$inq['RazonSocial']."<br>".$inq['Cif']."<br>";
if ($dire[0]=='P'){
    $datosinqui=$datosinqui.$inm['Direccion']."<br>".$inm['Poblacion']."<br>";
    $datosinqui=$datosinqui.$inm['CodigoPostal']."&nbsp".$inm['Provincia'];
} else {
    $datosinqui=$datosinqui.$inq['Direccion']."<br>".$inq['Poblacion']."<br>";
    $datosinqui=$datosinqui.$inq['CodigoPostal']."&nbsp".$inq['Provincia'];
}

//Datos del pie con el banco, oficina y cuenta corriente
$cuentacargo="";
$sql="select Banco from $emp.bancos where IDBanco='".$inq['IDBanco']."'";
$res=mysql_query($sql);
$aux=mysql_fetch_array($res);
$sql="select * from $emp.bancos_oficinas where IDBanco='".$inq['IDBanco']."' and IDOficina='".$inq['IDOficina']."'";
$res=mysql_query($sql);
$aux1=mysql_fetch_array($res);
if ($inq['IDBanco']!='0000') {
    $cuentacargo=$aux['Banco']."&nbsp".$aux1['Direccion']."&nbsp".$aux1['Poblacion']."&nbsp";
    $cuentacargo .= substr($inq['Iban'],0,4)." ".substr($inq['Iban'],4,4)." ".substr($inq['Iban'],8,4)." ".substr($inq['Iban'],12,4)." ".substr($inq['Iban'],16,4)." ".substr($inq['Iban'],20,4);
}


//Seleccionar la plantilla en funcion de si lleva iva o no seg�n la serie del recibo
$plantillaconiva=DameParametro('PRECI','0.gif');
$plantillasiniva=DameParametro('PRESI','0.gif');
$sql="select ConIva from $emp.series_recibos where IDEmpresa='".$_SESSION['empresa']."' and IDSerie='".substr($recibo['IDRecibo'],0,1)."';";
$res=mysql_query($sql);
$coniva=mysql_fetch_array($res);
if ($coniva[0]=='S') {
    $plantilla=$plantillaconiva;
    $sacarempresa=$de;
} else {
    $plantilla=$plantillasiniva;
    $sacarempresa="";
}

?>
<table>
<!--printLines=1-->
<tr>
<td>
<div id="Cuerpo" style="position:relative; left:0; top:0; width:740; height:354; z-index:1">
	<div id="fondo" style="position:absolute; left:0; top:0; width:740; height:0; z-index:2">
		<img src="images/<?php echo $plantilla;?>" width="740" height="354">
	</div>

	<div id="cabecera"" class="formularios" style="position:absolute; display:block; top:35; left:0; width:737; height:0; z-index:4;">
		<table cellspacing="0" border="0" width="634">
            <tr>
                <td width=144 />
                <td width=84 align="left"><span class="formularios"><?php echo $recibo['IDRecibo'];?></span></td>
                <td width=15 />
                <td width=238 align="left"><span class="formularios"><?php echo $datosempresa['Poblacion'];?></span></td>
                <td width=16 />
                <td align="right" width="113"><span class="formularios"><?php echo $recibo['Total'];?><u></u>�</span></td>
            </tr>
        </table>
		<table cellspacing="0" border="0" width="634">
            <tr><td colspan="4" height="10">&nbsp</td></tr>
            <tr>
                <td width=126 />
                <td width=102 align="left"><span class="formularios"><?php echo FechaEspaniol($recibo['Fecha']);?></span></td>
                <td width=75 />
                <td width=178 align="left"><span class="formularios">A la vista</span></td>
            </tr>
		</table>
    </div>

	<div id="conceptos"" style="position:absolute; display:block; top:95; left:0; width:737; height:0; z-index:4;">
		<table cellspacing="0" border="0" width="90%" class="formularios">
            <tr><td width=4% /><th>Concepto</th><th>Precio</th><th colspan="2">Lecturas</th><th>Udes</th><th>Importe</th></tr>
            <?php
            $sql="select t1.*,t2.Concepto as Concepto,t2.CobroMediacion as Mediacion,t2.Consumo
                from $datos.recibos_lineas as t1,$emp.conceptos as t2
                where (t1.IDConcepto=t2.IDConcepto) and
                        (t1.IDRecibo='".$recibo['IDRecibo']."') and
                        (t1.IDConcepto<>$conceptocobro)
                order by t1.IDLinea;";
            $res=mysql_query($sql);
            while ($linea=mysql_fetch_array($res)){?>
			<tr>
                <td width=4% />
                <td align="left"><?php if ($linea['Mediacion']=="S") echo "#"; echo $linea['Concepto']," ",$linea['Periodo'];?></td>
                <td align="right"><?php echo $linea['Precio'];?></td>
                <td align="right"><?php if ($linea['Consumo']=="S") echo $linea['ValorAnterior'];?></td>
                <td align="right"><?php if ($linea['Consumo']=="S") echo $linea['ValorActual'];?></td>
                <td align="right"><?php echo $linea['Unidades'];?></td>
                <td align="right"><?php echo $linea['Importe'];?></td>
            </tr>
            <?php}
            if ($recibo['Iva']>0){?>
                <tr>
                    <td width=4% />
                    <td align="left"><?php echo $recibo['Iva'];?>% de Iva sobre <?php echo $recibo['Base'];?></td>
                    <td align="right" colspan="5"><?php echo round($recibo['Base']*$recibo['Iva']/100,2);?></td>
                </tr>
            <?php }
            if ($recibo['Recargo']>0){?>
                <tr>
                    <td width=4% />
                    <td align="left"><?php echo $recibo['Recargo'];?>% de Recargo sobre <?php echo $recibo['Base'];?></td>
                    <td align="right" colspan="5"><?php echo round($recibo['Base']*$recibo['Recargo']/100,2);?></td>
                </tr>
            <?php }
            if ($recibo['Retencion']>0){?>
                <tr>
                    <td width=4% />
                    <td align="left"><?php echo $recibo['Retencion'];?>% de Retenci�n sobre <?php echo $recibo['Base'];?></td>
                    <td align="right" colspan="5">-<?php echo round($recibo['Base']*$recibo['Retencion']/100,2);?></td>
                </tr>
            <?php }?>
		</table>	
    </div>

	<div id="mediacion" style="position:absolute; top:220; left:20;width:737; height:0; z-index:3;">
	<span class="vInput" style="font-size:8px">#=Cobros por Mediacion</span>
	</div>
	
	<div id="inquilino" style="position:absolute; top:260; left:0;width:737; height:0; z-index:3;">
		<table cellspacing="0" border="0" width="90%">
            <tr>
                <td width=22% />
                <td width="45%" align="left"><span class="formularios"><?php echo $datosinqui;?></span></td>
                <td width=3% />
                <td align="left"><span class="formularios" style="font-size:8px"><?php echo $sacarempresa;?></span></td>
            </tr>
		</table>
	</div>
	
	<div id="pie" style="position:absolute; top:340; left:0;width:737; height:0; z-index:3;">
		<table cellspacing="0" border="0" width="98%">
			<tr>
                <td width=2% />
                <td align="left"><span class="vInput" style="font-size:8px"><?php echo $inm['Direccion'];?></span></td>
                <td width=1% />
                <td align="right"><span class="vInput" style="font-size:8px"><?php echo $cuentacargo;?></span></td>
            </tr>
		</table>
	</div>

    <div id=layer10 style="position:relative; top:0; left:5; width:737; height:354; z-index:2;">
    </div>
</td>
</tr>
</table>

<?php if($salto=='S'){?><div style="page-break-after:always"></div><?php }?>
<?php }
?>

<html>
<head>
<title>Recibo</title>
<link href="estilosrecibos.css" rel="stylesheet" type="text/css">
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<!--printtableWidth=752-->

<body  bgcolor="" leftmargin="10" onload="Centrar();">
<?php
    $sql="select * from $datos.recibos where $filtro ORDER BY $columna $orden;";
    $res=mysql_query($sql);
    while ($recibo=mysql_fetch_array($res)) Recibo($recibo);
?>
</body>
</html>
