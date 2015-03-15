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
        <tr><th class="blancoazul" colspan="2">Listado Detalle de Recibos</th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="lisrecibos">
        <tr><td colspan="2">Desde Inquilino:<input name="desdei" VALUE="<?php echo $v[desdei];?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Inquilino:<input name="hastai" VALUE="<?php echo $v[hastai];?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Desde Fecha:<input name="desdef" VALUE="<?php echo "01/01".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Fecha:<input name="hastaf" VALUE="<?php echo "31/12".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr height="5"><td colspan="2"></td></tr>
        <tr><td colspan="2">
            Saldo anterior:&nbsp;<?php DesplegableSN('saldoanterior','N','formularios');?>
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
//Cabecera de p�gina
function Header(){
    global $datosempresa,$e,$v;

    $titulo="LISTADO DETALLE DE RECIBOS";

    $logo="images/logo$e.jpg";
    if(file_exists($logo)) $this->Image($logo,10,8,33);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,10,$datosempresa[RazonSocial],0,1,"R");
    $this->SetFont('Arial','B',9);
    $this->Ln(10);
    $this->Cell(0,10,$titulo,0,1,"C");
    $this->Cell(0,5,"Desde Inquilino: ".$v[desdei]." a ".$v[hastai]." Desde Fecha: ".$v[desdef]." a ".$v[hastaf],0,1,"C");
    $this->Ln(5);

    //TITULOS DEL CUERPO
    $this->SetFont('Arial','B',8);
    $this->Cell(20,5,"Fecha","B",0,"L");
    $this->Cell(15,5,"Recibo","B",0,"L");
    $this->Cell(65,5,"Concepto","B",0,"L");
    $this->Cell(15,5,"Precio","B",0,"L");
    $this->Cell(30,5,"Lecturas","B",0,"C");
    $this->Cell(15,5,"Importe","B",0,"L");
    $this->Cell(15,5,"Retencion","B",0,"L");
    $this->Cell(15,5,"Cobrado","B",1,"L");
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

$filtro="(IDInquilino>='$v[desdei]') and (IDInquilino<='$v[hastai]') and (Fecha>='".AlmacenaFecha($v[desdef])."') and (Fecha<='".AlmacenaFecha($v[hastaf])."')";
$orden="IDInquilino,IDInmueble,Fecha,IDRecibo ASC";
$sql="select * from recibos where $filtro order by $orden";

//CREAR EL DOCUMENTO-------------------------------------------------------------
$pdf=new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->SetFillColor(210);
//$pdf->SetAutoPageBreak(1);
$pdf->SetFont('Arial','',7);
$pdf->AddPage();


$res=mysql_query($sql);
$cliant='';
$inmuant='';
$saldotot=0;
while($row=mysql_fetch_array($res)){
	$haycambio=(($cliant!=$row[IDInquilino]) or ($inmuant!=$row[IDInmueble]));
    if($haycambio){
        //Cambio de inquilino
        $sql="select * from ".$_SESSION[DBEMP].".inquilinos where IDInquilino='$row[IDInquilino]';";
        $res1=mysql_query($sql);
        $inq=mysql_fetch_array($res1);

        //Cambio de inmueble
        $sql="select * from inmuebles where IDInmueble='$row[IDInmueble]';";
        $res1=mysql_query($sql);
        $inm=mysql_fetch_array($res1);

        if($inmuant!=''){
            //Total Inmueble
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell(100,4,"Pendiente de Cobro: ".$saldo,"",0,"R");
            $pdf->Cell(60,4,$totimporte,"",0,"R");
            $pdf->Cell(30,4,$totcobrado,"",1,"R");
            $pdf->SetFont('Arial','',7);
            $pdf->AddPage();
        }

        $pdf->SetFont('Arial','B',7);
        $pdf->Cell(0,5,$row[IDInquilino]." ".$inq[RazonSocial],"",1,"L");
        $pdf->SetFont('Arial','',7);

        $pdf->SetFont('Arial','B',7);
        $pdf->Cell(0,5,$row[IDInmueble]." ".$inm[Direccion]." ".$inm[Poblacion],"",1,"L");
        $pdf->SetFont('Arial','',7);

        if($v[saldoanterior]=='S'){
            $sql="select sum(Saldo) from recibos where IDInquilino='".$row[IDInquilino]."' and IDInmueble='".$row[IDInmueble]."' and Fecha<'".AlmacenaFecha($v[desdef])."';";
            $res1=mysql_query($sql);
            $saldoanterior=mysql_fetch_array($res1);
            $pdf->Cell(0,5,"Saldo Anterior Inquilino e Inmueble: ".$saldoanterior[0],"",1,"C");
        }
        $totimporte=0;
        $totcobrado=0;
        $saldo=0;
    }

	
    $cliant=$row[IDInquilino];
    $inmuant=$row[IDInmueble];

    //Detalle de cada recibo
    $sql="select t1.*,t2.Concepto,t2.Consumo from recibos_lineas as t1, ".$_SESSION[DBEMP].".conceptos as t2 where t1.IDRecibo='$row[IDRecibo]' and t1.IDConcepto=t2.IDConcepto order by IDLinea;";
    $res1=mysql_query($sql);
    while($linea=mysql_fetch_array($res1)){
        if($row[Retencion]==0) $retencion=''; else $retencion=$row[Retencion];
        if($linea[Importe]<0){
            $importe='';$cobrado=-1*$linea[Importe];
            $totcobrado+=$cobrado;
        } else {
            $importe=$linea[Importe]; $cobrado='';
            $totimporte+=$importe;
        }
        if($linea[Consumo]=='S'){
            $precio=$linea[Precio];
            $anterior=$linea[ValorAnterior];
            $actual=$linea[ValorActual];
        } else {
            $precio='';
            $anterior='';
            $actual='';
        }
        $saldo+=$importe-$cobrado;
        $pdf->Cell(20,4,FechaEspaniol($linea[Fecha]),"",0,"L");
        $pdf->Cell(15,4,$linea[IDRecibo],"",0,"L");
        $pdf->Cell(65,4,$linea[Concepto],"",0,"L");
        $pdf->Cell(15,4,$precio,"",0,"R");
        $pdf->Cell(15,4,$anterior,"",0,"R");
        $pdf->Cell(15,4,$actual,"",0,"R");
        $pdf->Cell(15,4,$importe,"",0,"R");
        $pdf->Cell(15,4,$retencion,"",0,"R");
        $pdf->Cell(15,4,$cobrado,"",1,"R");
    }
    
    $saldotot=$saldotot+$saldo[0];
}

//Total Inmueble
$pdf->SetFont('Arial','B',9);
$pdf->Cell(100,4,"Pendiente de Cobro: ".$saldo,"",0,"R");
$pdf->Cell(60,4,$totimporte,"",0,"R");
$pdf->Cell(30,4,$totcobrado,"",1,"R");
$pdf->SetFont('Arial','',7);

$fichero="pdfs/ListadoRecibos.pdf";
$pdf->Output($fichero,'F');
AbreVentana($fichero,"Listado","");
}
?>
