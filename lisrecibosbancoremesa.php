<?php
session_start();
if ($_SESSION['iu']=='') exit;

require "funciones/desplegable.php";
require "funciones/fechas.php";
require "funciones/textos.php";
require('../pdf/fpdf.php');

$v = $_POST;

if($v['accion'] == ''){?>
    <table align="center"><tr height="100"><td colspan="2"></td></tr></table>
    <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Listado de Recibos por Banco/Remesa</th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="lisrecibosbancoremesa">
        <tr><td colspan="2">
            Desde Banco Remesa:
            <input name="desdeb" type="text" size="4" maxlength="4" class="formularios">
            <input name="desdeo" type="text" size="4" maxlength="4" class="formularios">
        </td></tr>
        <script language="JavaScript" type="text/javascript">
        document.formulario.desdeb.focus();
        </script>
        <tr><td colspan="2">
            Hasta Banco Remesa:
            <input name="hastab" type="text" size="4" maxlength="4" class="formularios">
            <input name="hastao" type="text" size="4" maxlength="4" class="formularios">
        </td></tr>
        <tr><td colspan="2">
            Fecha de Emision:
            <input name="fe" type="text" value="<?php echo date('d/m/Y');?>" size="10" maxlength="10" class="formularios">
        </td></tr>
        <tr><td colspan="2">Saltar P&aacute;gina?<?php DesplegableSN('saltopagina','N','formularios');?></td></tr>
        <tr><td colspan="2">Solo pendientes de cobro?<?php DesplegableSN('ptescobro','N','formularios');?></td></tr>

        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top">
            <td>
                Desea imprimir los recibos indicados?
                <input name="accion" value="Si" type="submit" class="formularios" accesskey="S">
            </td>
        </form>
        <td align="left">
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="inicial">
            <input name="accion" value="No" type="submit" class="formularios">
            </form>
        </td>
        </tr>
    </table>
<?php } else {

class PDF extends FPDF
{
//Cabecera de pagina
function Header(){
    global $datosempresa,$e,$fe,$ptescobro;

    $titulo="LISTADO DE RECIBOS EMITIDOS EL $fe POR BANCO-REMESA";
    if($ptescobro=='S') $titulo.=" (Solo ptes de cobro)";

    $logo="images/logo$e.jpg";
    if(file_exists($logo)) $this->Image($logo,10,8,33);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$datosempresa[RazonSocial],0,1,"R");
    $this->SetFont('Arial','B',9);
    $this->Ln(10);
    $this->Cell(0,10,$titulo,0,1,"C");
    $this->Ln(5);

    //TITULOS DEL CUERPO
    $this->SetFont('Arial','',9);
    $this->Cell(10,5,"Recibo","B",0,"C");
    $this->Cell(65,5,"Inmueble","B",0,"C");
    $this->Cell(55,5,"Inquilino","B",0,"C");
    $this->Cell(30,5,"Domiciliacion","B",0,"C");
    $this->Cell(15,5,"Importe","B",0,"C");
    $this->Cell(15,5,"Pendiente","B",1,"C");
    $this->SetFont('Arial','',7);
}

//Pie de pagina
function Footer(){
    $this->SetY(-15);
    $this->Cell(20,5,Date('d/m/Y H:i:s'),0,0);
    $this->Cell(0,5,"Pagina ".$this->PageNo().'/{nb}',0,0,"R");
}
}

$e=$_SESSION['empresa'];
$EMP=$_SESSION['DBEMP'];

$sql="select t1.*,t2.NOMBRE as Provincia from ".$_SESSION['DBEMP'].".empresas as t1,".$_SESSION['DBEMP'].".provincias as t2 where t1.IDEmpresa='$e' and t1.IDProvincia=t2.CODIGO";
$res=mysql_query($sql);
$datosempresa=mysql_fetch_array($res);

$filtro="(t1.IDInquilino=t2.IDInquilino) and (t1.IDInmueble=t3.IDInmueble) and (SUBSTRING(t1.CuentaAbono,1,8)>='{$v['desdeb']}{$v['desdeo']}') and (SUBSTRING(t1.CuentaAbono,1,8)<='{$v['hastab']}{$v['hastao']}') and (t1.Fecha='".AlmacenaFecha($v['fe'])."')";
$orden="t1.CuentaAbono,t1.IDRecibo ASC";
$sql="select t1.*,t2.RazonSocial,t3.Direccion from recibos as t1,$EMP.inquilinos as t2, inmuebles as t3 where $filtro order by $orden";

//CREAR EL DOCUMENTO-------------------------------------------------------------
$pdf=new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetFillColor(210);
//$pdf->SetAutoPageBreak(1);
$pdf->SetFont('Arial','',7);
$pdf->AddPage();


$res=mysql_query($sql);
$antbr="";
$totbr=0;
$ptebr=0;
$tottot=0;
$ptetot=0;
while($row=mysql_fetch_array($res)){
    $br=substr($row['CuentaAbono'],0,8);
    if($br!=$antbr){//Cambio de banco remesa
        if($antbr!=''){//Imrpimir subtotales del br
            $pdf->SetFont('Arial','B',7);
            $pdf->Cell(160,5,"TOTAL BANCO/REMESA",1,0,"R");
            $pdf->Cell(15,5,number_format($totbr,2),0,0,"R");
            $pdf->Cell(15,5,number_format($ptebr,2),0,1,"R");
            $pdf->SetFont('Arial','',7);
            $totbr=0;$ptebr=0;
            if($saltopagina=='S') $pdf->AddPage(); else $pdf->Ln(10);
        }
        $sql="select Direccion from $EMP.bancos_oficinas where IDBanco='".substr($br,0,4)."' and IDOficina='".substr($br,4,4)."';";
        $res1=mysql_query($sql);
        $texto=mysql_fetch_array($res1);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(0,5,substr($br,0,4)."-".substr($br,4,4)." ".$texto[0],0,1,'',1);
        $pdf->SetFont('Arial','',7);
    }
    $antbr=$br;
    if(($ptescobro=='N') or (($ptescobro=='S') and ($row['Saldo']>0))){
        $totbr=$totbr+$row['Total']; $ptebr=$ptebr+$row['Saldo'];
        $tottot=$tottot+$row['Total']; $ptetot=$ptetot+$row['Saldo'];
        $pdf->Cell(10,5,$row['IDRecibo'],0,0);
        $pdf->Cell(65,5,substr($row['IDInmueble']." ".DecodificaTexto($row['Direccion']),0,49),0,0);
        $pdf->Cell(55,5,substr($row['IDInquilino']." ".DecodificaTexto($row['RazonSocial']),0,35),0,0);
        $pdf->Cell(30,5,$row['CuentaCargo'],0,0);
        $pdf->Cell(15,5,$row['Total'],0,0,"R");
        $pdf->Cell(15,5,$row['Saldo'],0,1,"R");
    }
}
$pdf->SetFont('Arial','B',7);
$pdf->Cell(160,5,"TOTAL BANCO/REMESA",1,0,"R");
$pdf->Cell(15,5,number_format($totbr,2),0,0,"R");
$pdf->Cell(15,5,number_format($ptebr,2),0,1,"R");
if($saltopagina=='S') $pdf->AddPage(); else $pdf->Ln(10);

$pdf->Cell(160,5,"TOTAL GENERAL",1,0,"R");
$pdf->Cell(15,5,number_format($tottot,2),0,0,"R");
$pdf->Cell(15,5,number_format($ptetot,2),0,1,"R");

$fichero="pdfs/ListadoBancoRemesa.pdf";
$pdf->Output($fichero,'F');
AbreVentana($fichero,"Listado","");
}
?>
