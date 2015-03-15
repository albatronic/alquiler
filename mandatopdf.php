<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

//require "modulos.php";
require "conecta.php";
require "funciones/fechas.php";
require "funciones/formatos.php";
require "funciones/textos.php";
require "../pdf/fpdf.php";

$v = $_GET;

$emp=$_SESSION['DBEMP'];
$datos=$_SESSION['DBDAT'].$_SESSION['empresa'];

//DATOS DE LA EMPRESA
$sql="select ".$_SESSION['DBEMP'].".empresas.*,".$_SESSION['DBEMP'].".provincias.NOMBRE as Provincia from ".$_SESSION['DBEMP'].".empresas,".$_SESSION['DBEMP'].".provincias where (IDEmpresa=".$_SESSION['empresa'].") and (IDProvincia=".$_SESSION['DBEMP'].".provincias.CODIGO)";
$res=mysql_query($sql);
$datosempresa=mysql_fetch_array($res);

//DATOS DEL INMUEBLE
$sql = "select * from {$datos}.inmuebles WHERE IDInmueble='{$v['idinmu']}'";
$res=mysql_query($sql);
$inmueble=mysql_fetch_array($res);

//DATOS DEL INQUILINO
$sql = "select i.*,p.NOMBRE as Provincia from {$emp}.inquilinos i, {$emp}.provincias p WHERE i.IDInquilino='{$v['idinqui']}' and i.IDProvincia=p.CODIGO";
$res=mysql_query($sql);
$inquilino=mysql_fetch_array($res);

//DATOS DEL ALQUILER
$sql = "select * from {$datos}.inmuebles_inquilinos WHERE IDAlquiler='{$v['idalquiler']}'";
$res=mysql_query($sql);
$alquiler=mysql_fetch_array($res);

//CREAR EL DOCUMENTO-------------------------------------------------------------
class PDF extends FPDF {

    function Rotate($angle,$x=-1,$y=-1) {

        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }
    
    function footer() {
        
    }
} 

$pdf=new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetFillColor(210);
$pdf->SetMargins(0,0,0);
$pdf->SetAutoPageBreak(1,0);
$pdf->SetFont('Arial','',7);
$pdf->AddPage();

//Rectangulos
$pdf->Rect(20,20,170,260);
$pdf->SetLineWidth(0.4);
$pdf->Rect(25,45,160,50);
$pdf->Rect(25,125,160,115);

$pdf->setLeftMargin(25);
$pdf->SetRightMargin(25);

//Cabecera
$pdf->Image("images/logo{$_SESSION['empresa']}.JPG",25,22,25);
$titulo = "Orden de domiciliación de adeudo directo SEPA";
$pdf->SetY(30);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,5,$titulo,0,1,"C");
$pdf->SetFont('Arial','',7);
$pdf->Cell(0,4,"SEPA Direct Debit Mandate",0,1,"C");

//Caja acreedor
$pdf->SetXY(21,87);
$pdf->SetFont('Arial','B',7);
$pdf->Rotate(90);
$pdf->Cell(0,3,"A cumplimentar por el acreedor");
$pdf->Rotate(0);
$pdf->SetY(47);
$pdf->setLeftMargin(27);
$pdf->SetFont('Arial','',7);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,4,"Referencia de la orden de domiciliación: {$inmueble['Mandato']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Mandate Reference",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,4,"Identificador del acreedor: {$datosempresa['Cif']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Creditor Identifier",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,4,"Nombre del acreedor: {$datosempresa['RazonSocial']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Creditor's name",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,4,"Dirección: {$datosempresa['Direccion']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Address",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,4,"Código postal - Población - Provincia: " . $datosempresa['CodigoPostal'] . " " . $datosempresa['Poblacion'] . " " . $datosempresa['Provincia'],0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Postal Code - City - Town",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,4,"País: España",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,3,"Country",0,1);

//Texto central
$textoCentral1 = utf8_encode("Mediante la firma de esta orden de domiciliación, el deudor autoriza (A) al acreedor a enviar instrucciones a la entidad del deudor para adeudar su cuenta y (B) a la entidad para efectuar los adeudos en su cuenta siguiendo las instrucciones del acreedor. Como parte de sus derechos, el deudor está legitimado al reembolso por su entidad en los términos y condiciones del contrato suscrito con la misma. La solicitud de reembolso deberá efectuarse dentro de las ocho semanas que siguen a la fecha de adeudo en cuenta. Puede obtener información adicional sobre sus derechos en su entidad financiera.");
$textoCentral2 = utf8_encode("By signing this mandate form, you authorise (A) the Creditor to send instructions to your bank to debit your acount and (B) your bank to debit your account in accordance with the instructions from the Creditor. As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within eight weeks sttarting from the date on which your account was debited. Your rights are explained in a statement that you can obtain from your bank.");
$pdf->SetFont('Arial','',7);
$pdf->SetXY(25,97);
$pdf->MultiCell(0,3,$textoCentral1);
$pdf->SetFont('Arial','',5);
$pdf->MultiCell(0,3,$textoCentral2);

//Caja deudor
$pdf->SetFont('Arial','B',7);
$pdf->SetXY(21,190);
$pdf->Rotate(90);
$pdf->Cell(0,3,"A cumplimentar por el deudor");
$pdf->Rotate(0);
$pdf->SetY(127);
$pdf->setLeftMargin(27);
$pdf->SetFont('Arial','',7);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Nombre del deudor/es: {$inquilino['RazonSocial']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"(titulares de la cuenta de cargo) / Debor's name",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Dirección del deudor: {$inquilino['Direccion']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Address of the debor",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Código postal - Población - Provincia: " . $inquilino['CodigoPostal'] . " " . $inquilino['Poblacion'] . " " . $inquilino['Provincia'],0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Postal Code - City - Town",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"País: España",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Country",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Swift BIC: {$inquilino['Bic']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"(Puede contener 8 u 11 posiciones) / Swift BIC up to 8 or 11 characters",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Número de cuenta - IBAN: {$inquilino['Iban']}",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Account number - IBAN",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Tipo de pago: RECURRENTE",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Type of payment - Recurrent payment",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Fecha - Localidad: " . FechaEspaniol($alquiler['FechaInicio']) . " - " . $datosempresa['Poblacion'],0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Date and location in wich you are signing",0,1);
$pdf->SetFont('Arial','',9);$pdf->Cell(0,6,"Firma del deudor",0,1);
$pdf->SetFont('Arial','',6);$pdf->Cell(0,4,"Signature of the deudor",0,1);

//Texto pie
$pdf->SetXY(25,245);
$pdf->SetFont('Arial','',7);
$pdf->Cell(0,3,'TODOS LOS CAMPOS HAN DE SER CUMPLIMENTADOS OBLIGATORIAMENTE.',0,1,'C');
$pdf->Cell(0,3,'UNA VEZ FIRMADA ESTA ORDEN DE DOMICILIACION DEBE SER ENVIADA AL ACREEDOR PARA SU CUSTODIA.',0,1,'C');
$pdf->SetFont('Arial','',5);
$pdf->Cell(0,3,'ALL GAPS ARE MANDATORY. ONCE THIS MANDATE HAS BEEN SIGNED MUST BE SENT TO CREDITOR FOR STORAGE.',0,1,'C');
 

$fichero="pdfs/mandato_{$v['idinmu']}_{$v['idinqui']}.pdf";echo $fichero;
$pdf->Output($fichero,'F');
RecargaVentana($fichero);
exit;
