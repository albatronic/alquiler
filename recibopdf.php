<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "modulos.php";
require "conecta.php";
require "funciones/fechas.php";
require "funciones/formatos.php";
require "funciones/textos.php";
require "../pdf/fpdf.php";

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
$de=$datosempresa['RazonSocial']."\n";
$de=$de."CIF: ".$datosempresa['Cif']."\n";
$de=$de.$datosempresa['Direccion']."\n";
$de=$de.$datosempresa['CodigoPostal']." ".$datosempresa['Poblacion']."\n".$datosempresa['Provincia'];


//CREAR EL DOCUMENTO-------------------------------------------------------------
$pdf=new FPDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetFillColor(210);
$pdf->SetMargins(0,0,0);
$pdf->SetAutoPageBreak(1,0);
$pdf->SetFont('Arial','',7);
$pdf->AddPage();

$sql="select * from $datos.recibos where $filtro ORDER BY $columna $orden;";
$res=mysql_query($sql);
$i=0;
while ($recibo=mysql_fetch_array($res)){
    $i+=1;
    if($i>3){$i=1;$pdf->AddPage();}
    Recibo($recibo,$i);
}

$fichero="tmp/Recibos".$idagente.".pdf";
$pdf->Output($fichero,'F');
RecargaVentana($fichero);
exit;

//FUNCION PARA SACAR UN RECIBO
function Recibo($recibo,$n){

global $emp,$datos,$de,$datosempresa,$pdf;

$conceptocobro=DameParametro('COBRO','00');

$res=mysql_query("select inquilinos.*,provincias.NOMBRE as Provincia from $emp.inquilinos,$emp.provincias where $emp.inquilinos.IDProvincia=$emp.provincias.CODIGO and IDInquilino='".$recibo['IDInquilino']."';");
$inq=mysql_fetch_array($res);

$res=mysql_query("select * from $datos.inmuebles where IDInmueble='".$recibo['IDInmueble']."';");
$inm=mysql_fetch_array($res);

$sql="select $datos.inmuebles_inquilinos.DireccionRecibo,$emp.tipos_iva.Iva from $datos.inmuebles_inquilinos, $emp.tipos_iva where IDInmueble='".$row['IDInmueble']."' and IDInquilino='".$recibo['IDInquilino']."' and $emp.tipos_iva.IDIva=$datos.inmuebles_inquilinos.IDIva;";
$res=mysql_query($sql);
$dire=mysql_fetch_array($res);

//Datos del inquilino
$datosinqui=$inq['RazonSocial']."\n".$inq['Cif']."\n";
if ($dire[0]=='P'){
    $datosinqui=$datosinqui.$inm['Direccion']."\n".$inm['Poblacion']."\n";
    $datosinqui=$datosinqui.$inm['CodigoPostal']." ".$inm['Provincia'];
} else {
    $datosinqui=$datosinqui.$inq['Direccion']."\n".$inq['Poblacion']."\n";
    $datosinqui=$datosinqui.$inq['CodigoPostal']." ".$inq['Provincia'];
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
    $cuentacargo=$aux['Banco']." ".$aux1['Direccion']." ".$aux1['Poblacion']." ";
    $cuentacargo .= substr($inq['Iban'],0,4)." ".substr($inq['Iban'],4,4)." ".substr($inq['Iban'],8,4)." ".substr($inq['Iban'],12,4)." ".substr($inq['Iban'],16,4)." ".substr($inq['Iban'],20,4);
}


//Seleccionar la plantilla en funcion de si lleva iva o no según la serie del recibo
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

//Calcular la posicion del recibo
$alturarecibo=101;
$Y=13+($n-1)*$alturarecibo;
$pdf->SetFont("Arial","",8);
$pdf->SetXY(45,$Y);
$pdf->Cell(30,5,$recibo['IDRecibo'],0,0);
$pdf->Cell(70,5,$datosempresa['Poblacion'],0,0);
$pdf->SetFont("Arial","B",8);
$pdf->Cell(30,5,$recibo['Total'],0,0,"R");
$pdf->SetFont("Arial","",8);

$pdf->Ln(9);
$pdf->SetX(50);
$pdf->Cell(65,5,FechaEspaniol($recibo['Fecha']),0,0);
$pdf->Cell(65,5,"A la vista",0,0);

$pdf->Ln(8);
$pdf->SetLeftMargin(17);
$pdf->SetFont("Arial","B",8);
$pdf->Cell(90,4,"Concepto",0,0,"C");
$pdf->Cell(30,4,"Precio",0,0,"C");
$pdf->Cell(25,4,"Lecturas",0,0,"C");
$pdf->Cell(15,4,"Udes",0,0,"C");
$pdf->Cell(15,4,"Importe",0,1,"C");


$pdf->SetFont("Arial","",7);

$sql="select t1.*,t2.Concepto as Concepto,t2.CobroMediacion as Mediacion,t2.Consumo
                from $datos.recibos_lineas as t1,$emp.conceptos as t2
                where (t1.IDConcepto=t2.IDConcepto) and
                        (t1.IDRecibo='".$recibo['IDRecibo']."') and
                        (t1.IDConcepto<>$conceptocobro)
                order by t1.IDLinea;";
$res=mysql_query($sql);
while ($linea=mysql_fetch_array($res)){
    if ($linea['Mediacion']=="S") $aux="#"; else $aux="";
    $aux.=$linea['Concepto']." ".$linea['Periodo'];
    if ($linea['Consumo']=="S") $van=$linea['ValorAnterior']; else $van="";
    if ($linea['Consumo']=="S") $vac=$linea['ValorActual']; else $vac="";

    $pdf->Cell(90,4,$aux,0,0);
    $pdf->Cell(30,4,$linea['Precio'],0,0,"R");
    $pdf->Cell(12,4,$van,0,0,"R");
    $pdf->Cell(13,4,$vac,0,0,"R");
    $pdf->Cell(15,4,$linea['Unidades'],0,0,"R");
    $pdf->Cell(15,4,$linea['Importe'],0,1,"R");
    
    if($linea['Mediacion']=="N"){
    if ($recibo['Iva']>0){
        $pdf->Cell(90,4,$recibo['Iva']."% de Iva sobre ".$recibo['Base'],0,0);
        $pdf->Cell(85,4,round($recibo['Base']*$recibo['Iva']/100,2),0,1,"R");
    }
    if ($recibo['Recargo']>0){
        $pdf->Cell(90,4,$recibo['Recargo']."% de Recargo sobre ".$recibo['Base'],0,0);
        $pdf->Cell(85,4,round($recibo['Base']*$recibo['Recargo']/100,2),0,1,"R");
    }
    if ($recibo['Retencion']>0){
        $pdf->Cell(90,4,$recibo['Retencion']."% de Retencion sobre ".$recibo['Base'],0,0);
        $pdf->Cell(85,4,"-".round($recibo['Base']*$recibo['Retencion']/100,2),0,1,"R");
    }
    }
}

$pdf->SetY($Y+45);
$pdf->SetFont("Arial","",6);
$pdf->Cell(0,4,"#=Cobros por Mediacion",0,1);
$pdf->Ln(3);
$aux=$pdf->GetY();

$pdf->SetFont("Arial","",8);
$pdf->SetLeftMargin(43);
$pdf->MultiCell(80,4,$datosinqui,0,"L");

$pdf->SetY($aux);
$pdf->SetFont("Arial","",6);
$pdf->SetLeftMargin(130);
$pdf->MultiCell(60,3,$sacarempresa,0,"L");

$pdf->SetLeftMargin(0);
$pdf->SetRightMargin(6);
$pdf->SetXY(15,$Y+74);
$pdf->SetFont("Arial","I",6);
$pdf->Cell(0,4,DecodificaTexto($inm['Direccion']),0,0);
$pdf->SetX(15);
$pdf->Cell(0,4,DecodificaTexto($cuentacargo)."",0,0,"R");
}
