<?php
session_start();
if ($_SESSION['iu']=='') exit;

require "funciones/desplegable.php";
require "funciones/fechas.php";
require "funciones/textos.php";
require('../pdf/fpdf.php');


$v=$_POST;
if($v[accion]==''){?>
    <table align="center"><tr height="100"><td colspan="2"></td></tr></table>

    <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Listado de Inquilinos</th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="lisinquilinos">
        <tr><td colspan="2">Desde Inquilino:<input name="desdei" VALUE="<?php echo $v[desdei];?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Inquilino:<input name="hastai" VALUE="<?php echo $v[hastai];?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Desde Fecha Fin Contrato:<input name="desdef" VALUE="<?php echo "01/01".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Fecha Fin Contrato:<input name="hastaf" VALUE="<?php echo "31/12".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr height="5"><td colspan="2"></td></tr>
        <tr><td colspan="2">
            Solo morosos:&nbsp;<?php DesplegableSN('morosos','N','formularios');?>
        </td></tr>
        <tr><td colspan="2">
            Solo activos:&nbsp;<?php DesplegableSN('activos','S','formularios');?>
        </td></tr>
        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top">
            <td align="right">Desea generar el listado?&nbsp;<input name="accion" value="Si" type="submit" class="formularios"></td>
            <script language="JavaScript" type="text/javascript">
            document.formulario.desdei.focus();
            </script>
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
    global $datosempresa,$e,$v;

    $titulo="LISTADO DE INQUILINOS";
    if($ptescobro=='S') $titulo.=" (Solo ptes de cobro)";

    $logo="images/logo$e.jpg";
    if(file_exists($logo)) $this->Image($logo,10,8,33);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$datosempresa[RazonSocial],0,1,"R");
    $this->SetFont('Arial','B',9);
    $this->Ln(10);
    $this->Cell(0,10,$titulo,0,1,"C");
    $this->Cell(0,5,"Desde Inquilino: ".$v[desdei]." a ".$v[hastai]." Desde Fin Contrato: ".$v[desdef]." a ".$v[hastaf],0,1,"C");
    $this->Cell(0,5,"Solo Morosos: ".$v[morosos]." Solo activos: ".$v[activos],0,1,"C");
    $this->Ln(5);

    //TITULOS DEL CUERPO
    $this->SetFont('Arial','',9);
    $this->Cell(15,5,"Codigo","B",0,"L");
    $this->Cell(90,5,"Nombre","B",0,"L");
    $this->Cell(15,5,"Piso","B",0,"L");
    $this->Cell(20,5,"Contrato","B",0,"L");
    $this->Cell(20,5,"Fin Cont","B",0,"L");
    $this->Cell(20,5,"Saldo","B",1,"L");
    $this->SetFont('Arial','',7);
}

//Pie de p�gina
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

$filtro="(t1.IDInquilino=t2.IDInquilino) and (t1.IDInmueble=t3.IDInmueble) and (t1.IDInquilino>='$v[desdei]') and (t1.IDInquilino<='$v[hastai]') and (t1.FechaFin>='".AlmacenaFecha($v[desdef])."') and (t1.FechaFin<='".AlmacenaFecha($v[hastaf])."')";
if($v[activos]=='S'){
    $filtro.=" and (t1.FechaFin>='".date(Ymd)."')";
}
$orden="t1.IDInquilino,t1.IDInmueble ASC";
$sql="select t1.*,t2.RazonSocial,t3.Direccion from inmuebles_inquilinos as t1,$EMP.inquilinos as t2, inmuebles as t3 where $filtro order by $orden";

//CREAR EL DOCUMENTO-------------------------------------------------------------
$pdf=new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetFillColor(210);
//$pdf->SetAutoPageBreak(1);
$pdf->SetFont('Arial','',7);
$pdf->AddPage();


$res=mysql_query($sql);
$saldotot=0;
while($row=mysql_fetch_array($res)){
    //Ver lo que debe
    $sql="select sum(Saldo) from recibos where IDInquilino='$row[IDInquilino]' and IDInmueble='$row[IDInmueble]';";
    $res1=mysql_query($sql);
    $saldo=mysql_fetch_array($res1);
    
    if(($v[morosos]=='N') or (($v[morosos]=='S') and ($saldo[0]!=0))){
        $pdf->Cell(15,4,$row[IDInquilino],"",0,"L");
        $pdf->Cell(90,4,$row[RazonSocial],"",0,"L");
        $pdf->Cell(15,4,$row[IDInmueble],"",0,"L");
        $pdf->Cell(20,4,FechaEspaniol($row[FechaInicio]),"",0,"L");
        $pdf->Cell(20,4,FechaEspaniol($row[FechaFin]),"",0,"L");
        $pdf->Cell(20,4,number_format($saldo[0],2),"",1,"R");
    }
    
    $saldotot=$saldotot+$saldo[0];
}


$pdf->Cell(180,5,number_format($saldotot,2),"B",1,"R");

$fichero="pdfs/ListadoInquilinos.pdf";
$pdf->Output($fichero,'F');
AbreVentana($fichero,"Listado","");
}
?>
