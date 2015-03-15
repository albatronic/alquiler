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
        <tr><th class="blancoazul" colspan="2">Listado Inmuebles-Conceptos</th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="lisconceptos">
        <tr><td colspan="2">Desde Inmueble:&nbsp;<input name="desdei" VALUE="<?php echo $v[desdei];?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Inmueble:&nbsp;<input name="hastai" VALUE="<?php echo $v[hastai];?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
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

    $titulo="LISTADO DE INMUEBLES - CONCEPTOS";

    $logo="images/logo$e.jpg";
    if(file_exists($logo)) $this->Image($logo,10,8,33);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$datosempresa[RazonSocial],0,1,"R");
    $this->SetFont('Arial','B',9);
    $this->Ln(10);
    $this->Cell(0,10,$titulo,0,1,"C");
    $this->Cell(0,5,"Desde Inmueble: ".$v[desdei]." a ".$v[hastai],0,1,"C");
    $this->Ln(5);

    //TITULOS DEL CUERPO
    $this->SetFont('Arial','B',8);
    $this->Cell(100,5,"Inmueble","B",0,"L");
    $this->Cell(75,5,"Concepto","B",0,"L");
    $this->Cell(20,5,"Importe","B",1,"L");
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

$filtro="(t1.IDInmueble>='$v[desdei]') and (t1.IDInmueble<='$v[hastai]') and (t1.IDConcepto=t2.IDConcepto)";
$orden="IDInmueble,IDConcepto ASC";
$sql="select t1.*,t2.Concepto from inmuebles_conceptos as t1,".$_SESSION['DBEMP'].".conceptos as t2 where $filtro order by $orden";

//CREAR EL DOCUMENTO-------------------------------------------------------------
$pdf=new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetFillColor(210);
//$pdf->SetAutoPageBreak(1);
$pdf->SetFont('Arial','',7);
$pdf->AddPage();


$res=mysql_query($sql);
$inmuant='';
while($row=mysql_fetch_array($res)){
	$haycambio=($inmuant!=$row[IDInmueble]);
    if($haycambio){
        //Cambio de inmueble
        $sql="select * from inmuebles where IDInmueble='$row[IDInmueble]';";
        $res1=mysql_query($sql);
        $inm=mysql_fetch_array($res1);

        $pdf->SetFont('Arial','B',7);
        $pdf->Cell(0,5,$row[IDInmueble]." ".$inm[Direccion]." ".$inm[Poblacion],"",1,"L");
        $pdf->SetFont('Arial','',7);
    }

    $inmuant=$row[IDInmueble];

    $pdf->Cell(100,4,"","",0,"L");
    $pdf->Cell(75,4,$row[IDConcepto]."-".$row[Concepto],"",0,"L");
    $pdf->Cell(20,4,$row[Importe],"",1,"R");
}

$fichero="pdfs/ListadoConceptos.pdf";
$pdf->Output($fichero,'F');
AbreVentana($fichero,"Listado","");
}
?>
