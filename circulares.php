<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";
require "funciones/textos.php";
require "funciones/desplegable.php";
require "funciones/variables.php";
require "funciones/fechas.php";
require "funciones/recibos.php";
require "../pdf/fpdf.php";

$ruta="plantillas";
$separador=DameParametro("SEPAR","#");
$e=$_SESSION['empresa'];
$EMP=$_SESSION['DBEMP'];
$DATOS=$_SESSION['DBDAT'].$_SESSION['empresa'];


$v=$_POST;
if($v=='') $v=$_GET;

if($v['tipo']=='') $v['tipo']="VCTO";

class PDF extends FPDF
{
//Cabecera de pagina
function Header(){
    global $VALORES,$e;

    $logo="images/logo$e.jpg";
    if(file_exists($logo)) $this->Image($logo,10,8,33);
    $this->SetFont('Arial','B',12);
    $this->Cell(0,8,$VALORES['empresas'][RazonSocial],0,1,"R");
    $this->SetFont('Arial','',8);
    $this->Cell(0,5,$VALORES['empresas'][Direccion],0,1,"R");
    $this->Cell(0,5,$VALORES['empresas'][CodigoPostal]." ".$VALORES['empresas'][PROVINCIA],0,1,"R");
    $this->Cell(0,5,"Tlf: ".$VALORES['empresas'][Telefono]." Fax: ".$VALORES['empresas'][Fax],0,1,"R");
    $this->Ln(10);
}
}

function CreaPdf(){
    global $pdf;

    $pdf=new PDF('P','mm','A4');
    $pdf->AliasNbPages();
    $pdf->SetFillColor(210);
    $pdf->SetFont('Arial','',10);
    $pdf->SetRightMargin(20);
    $pdf->SetLeftMargin(30);
}

function CreaCircularPdf($texto){
    global $pdf;

    $pdf->AddPage();
    $pdf->Multicell(0,4,$texto);
}


switch ($v['accion']) {
    case '':?>
    <table align="center"><tr height="100"><td colspan="2"></td></tr></table>

    <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Generaci&oacute;n de Circulares</th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="circulares">
        <tr><td colspan="2">Circular:
            <select name="tipo" class="formularios" onchange="submit();">
                <option value="VCTO" <?if($v['tipo']=="VCTO") echo "SELECTED";?>>Pr&oacute;ximos Vencimientos</option>
                <option value="MORO" <?if($v['tipo']=="MORO") echo "SELECTED";?>>Morosos</option>
                <option value="FINI" <?if($v['tipo']=="FINI") echo "SELECTED";?>>Finiquito</option>
            </select>
        </td></tr>
        <tr height="5"><td colspan="2"></td></tr>
        <?if($v['tipo']=="VCTO"){?>
        <tr><td colspan="2">Entre la Fecha:&nbsp;<input name="desde" VALUE="<?echo "01/01".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">y la Fecha:&nbsp;<input name="hasta" VALUE="<?echo "31/12".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <?}?>

        <?if($v['tipo']=="MORO"){?>
        <tr><td colspan="2">Desde Inquilino:&nbsp;<input name="desde" VALUE="" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Inquilino:&nbsp;<input name="hasta" VALUE="" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <?}?>

        <?if($v['tipo']=="FINI"){?>
        <tr><td colspan="2">Inmueble:&nbsp;<input name="inmu" VALUE="" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <?}?>

        <tr height="5"><td colspan="2"></td></tr>
        <tr>
            <td colspan="2">
                Plantilla:&nbsp;<?DesplegablePlantillas('fichero',$ruta,'','','formularios');?>
                <a href="javascript:;" onClick="window.open('plantillas/'+fichero.value,'Plantilla')"><img src="images/lupa.png" border="0" alt="Ver Plantilla"></a>
            </td>
        </tr>
        <tr height="5"><td colspan="2"></td></tr>
        <tr><td colspan="2">Generar documentos separados:&nbsp;<input name="separados" type="checkbox"></td></tr>
        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top">
            <td align="right">Desea generar las circulares?&nbsp;<input name="accion" value="Si" type="submit" class="formularios"></td>
            <script language="JavaScript" type="text/javascript">
            document.formulario.desdef.focus();
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
    <?php
        break;
        
    case 'Si':
        if($v['fichero']=='') {Atras("Debe seleccionar una plantilla"); exit;}

        //COGER LAS VARIABLES QUE TIENE EL DOCUMENTO BASE (PLANTILLA)
        $fi=$ruta."/".$v['fichero'];
        $texto=LeeFichero($fi);
        $variables=array();
        $variables=VariablesDocumento($fi);


        //MOSTRAR LAS VARIABLES QUE NO TIENEN CORRESPONDENCIA CON COLUMNAS DE LA BASE DE DATOS.
        if (is_array($variables)){?>
        <table class="formularios">
        <tr><th colspan="2">VALORES A SUSTITUIR EN LA PLANTILLA:<br><?echo $v['fichero'];?></th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="circulares">
        <input name="tipo" type="hidden" value="<?echo $v['tipo'];?>">
        <input name="desde" type="hidden" value="<?echo $v['desde'];?>">
        <input name="hasta" type="hidden" value="<?echo $v['hasta'];?>">
        <input name="inmu" type="hidden" value="<?echo $v['inmu'];?>">
        <input name="fichero" type="hidden" value="<?echo $v['fichero'];?>">
        <input name="separados" type="hidden" value="<?echo $v['separados'];?>">
        <?php
        Subrrayado(2);
        foreach ($variables as $key=>$value){
            if($value==''){?>
                <tr>
                    <td bgcolor='#CCCCCC'><b><?echo $key;?></b></td>
                    <td><input name="<?echo $key;?>" value="" type="text" size="50" class="formularios"></td>
                </tr>
            <?php } else {?>
                <input name="<?php echo "@@",$key;?>" value="<?echo $value;?>" type="hidden">
            <?}
        }?>
        <tr>
            <th colspan="2"><input name="accion" value="Hacer Circulares" type="submit" class="formularios"</th>
        </tr>
        </form>
        </table>
        <?php }
        break;
        
	case 'Hacer Circulares':

        switch ($v['tipo']){
            case 'VCTO':
                $sql="select IDAlquiler,IDInquilino,IDInmueble from $DATOS.inmuebles_inquilinos ".
                    "where $DATOS.inmuebles_inquilinos.FechaFin>='".AlmacenaFecha($v['desde'])."' and $DATOS.inmuebles_inquilinos.FechaFin<='".AlmacenaFecha($v['hasta'])."'";
                $fichero="pdfs/Circular_VCTO_".date('Ymd_His');
                break;

            case 'MORO':
                $sql="select t1.IDAlquiler,t2.IDInquilino,t2.IDInmueble,sum(t2.Saldo) as SALDO from $DATOS.inmuebles_inquilinos as t1,$DATOS.recibos as t2 ".
                    "where t2.Saldo>0 ".
                    "and t1.IDInquilino=t2.IDInquilino ".
                    "and t1.IDInmueble=t2.IDInmueble ".
                    "and t2.IDInquilino>='".$v['desde']."' ".
                    "and t2.IDInquilino<='".$v['hasta']."' ".
                    "GROUP BY t2.IDInquilino,t2.IDInmueble,t1.IDAlquiler ".
                    "order by t2.IDInquilino;";
                $fichero="pdfs/Circular_MOROSOS_".date('Ymd_His');
                break;

            case 'FINI':
                $sql="select IDAlquiler,IDInquilino,IDInmueble from $DATOS.inmuebles_inquilinos ".
                    "where $DATOS.inmuebles_inquilinos.IDInmueble='".$v['inmu']."' order by FechaFin DESC limit 1;";
                $fichero="pdfs/Circular_FINIQUITO_".date('Ymd_His');
                break;
        }
        
        $res=mysql_query("select t1.*,t2.NOMBRE as PROVINCIA from $EMP.empresas as t1,$EMP.provincias as t2 where t1.IDEmpresa='$e' and t1.IDProvincia=t2.CODIGO");
        $VALORES['empresas']=mysql_fetch_array($res);

        $fi=$ruta."/".$v['fichero'];
        $textoplantilla=LeeFichero($fi);

        $res=mysql_query($sql);$i=0;
        while($row=mysql_fetch_array($res)){
            $i++;
            $VALORES['inmuebles_inquilinos']=DameAlquiler($row[IDAlquiler]);
            $VALORES['inquilinos']=DameInquilino($row[IDInquilino]);
            $VALORES['inmuebles']=DameInmueble($row[IDInmueble]);
            $circular=GeneraDocumento($textoplantilla,$VALORES,$v,$separador);
            if($v['separados']==''){
                if($i==1) CreaPdf();
                CreaCircularPdf($circular);
            } else {
                CreaPdf();
                CreaCircularPdf($circular);
                $pdf->Output($fichero."_$i.pdf",'F');
            }
        }

        if(($v['separados']=='') and ($i>0)){
            $pdf->Output($fichero.".pdf",'F');
            AbreVentana($fichero.".pdf","Circulares","");
        }

        break;
}

function GeneraDocumento($texto,$VALORES,$VARIABLES,$separador){
//REEMPLAZA EN EL TEXTO 'texto' LAS VARIABLES, POR SUS VALORES.
//LAS VARIABLES DEL TEXTO VAN DELIMITADAS POR EL CARACTER '$seperador'

        if ($texto!=""){
            reset($VARIABLES);
            foreach ($VARIABLES as $key => $valor){
                if (($key!="c") and ($key!="fichero") and ($key!="accion") and ($key!="desde") and ($key!="hasta") and ($key!="tipo")){
                    //para cada una de las variables recogidas, voy reemplazando su valor
                    if(substr($key,0,2)=='@@'){ //Es un valor de una tabla
                        $key=str_replace("@","",$key);
                        list($tabla,$columna)=split(">",$valor);
                        $texto=str_replace($separador.$key.$separador,$VALORES[$tabla][$columna],$texto);
                        $texto=str_replace($separador.strtolower($key).$separador,$VALORES[$tabla][$columna],$texto);
                    } else {//Es un valor variable, que se ha introducido en el formulario
                        $texto=str_replace($separador.$key.$separador,$valor,$texto);
                        $texto=str_replace($separador.strtolower($key).$separador,$valor,$texto);
                    }
                }
            }
        }
        return($texto);
}

function DameInquilino($id){
    global $EMP,$DATOS;
    $sql="select $EMP.inquilinos.*,$EMP.provincias.NOMBRE as PROVINCIA,sum($DATOS.recibos.Saldo) as SALDO ".
        "from $EMP.inquilinos,$EMP.provincias,$DATOS.recibos ".
        "where $EMP.inquilinos.IDInquilino='$id' ".
        "and IDProvincia=CODIGO ".
        "and $DATOS.recibos.IDInquilino='$id' ".
        "group by $EMP.inquilinos.IDInquilino";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    return($row);
}

function DameInmueble($id){
    global $EMP,$DATOS;
    $sql="select $DATOS.inmuebles.*,$EMP.provincias.NOMBRE as PROVINCIA,sum($DATOS.recibos.Saldo) as SALDO ".
        "from $DATOS.inmuebles,$EMP.provincias,$DATOS.recibos ".
        "where $DATOS.inmuebles.IDInmueble='$id' ".
        "and IDProvincia=CODIGO ".
        "and $DATOS.recibos.IDInmueble='$id' ".
        "group by $DATOS.inmuebles.IDInmueble";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    return($row);
}

function DameAlquiler($id){
    gLobal $DATOS;
    $sql="select ALQ.*,sum(REC.Saldo) as SALDO ".
        "from $DATOS.inmuebles_inquilinos as ALQ,$DATOS.recibos as REC ".
        "where ALQ.IDAlquiler='$id' ".
        "and REC.IDInquilino=ALQ.IDInquilino ".
        "and REC.IDInmueble=ALQ.IDInmueble ".
        "group by ALQ.IDAlquiler";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    return($row);
}
?>
