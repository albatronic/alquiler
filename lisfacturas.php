<?php
session_start();
if ($_SESSION['iu']=='') exit;

require "engancha.php";
require "modulos.php";
require "funciones/fechas.php";
require "funciones/textos.php";
require "funciones/formatos.php";
require "funciones/desplegable.php";

//Coger las medidas del papel a utilizar en el listado
$papel=$_GET['papel']; if ($papel=='') $papel=$_POST['papel']; if ($papel=='') $papel="A4";
$MEDIDAS=MedidasPapel($papel);
if ($MEDIDAS['ALTO']<0){
    echo "No se han definido los tipos de papel a utilizar.";
    exit;
}
$lopag=$MEDIDAS['LOPAG'];


//Parametros del listado
$desdef=AlmacenaFecha($_POST['desdef']);
$hastaf=AlmacenaFecha($_POST['hastaf']);
$desdes=$_POST['desdes'];
$hastas=$_POST['hastas'];
$titulo=$_POST['titulo'];


if ($desdef>$hastaf) {echo "EL RANGO DE FECHAS ES INCORRECTO"; exit;}
$filtrofecha="(DATE_FORMAT(Fecha,'%Y%m%d')>='$desdef') and (DATE_FORMAT(Fecha,'%Y%m%d')<='$hastaf')";
$filtroserie=" and (SUBSTRING(IDRecibo,1,1)>='$desdes') and (SUBSTRING(IDRecibo,1,1)<='$hastas')";


$e=$_SESSION['DBEMP'];
$filtro=$filtrofecha.$filtroserie." and (t1.IDInquilino=t2.IDInquilino)";
$sql="select t1.*,t2.RazonSocial from recibos as t1,$e.inquilinos as t2 where $filtro order by Fecha,IDRecibo;";

$res=mysql_query($sql);
//Calcular el numero de registros y el n. total de pqginas
list($kk,$nregistros,$totpaginas)=Paginar($sql,1,$lopag);

function Cabecera($pag){
    global $nregistros,$totpaginas,$papel,$MEDIDAS;

    $ALTOCABECERA=149;
    $ALTOPIE=15;
    $ALTOCUERPO=$MEDIDAS['ALTO']-$ALTOCABECERA-$ALTOPIE;
    
if ($pag>1) {?></table></div></div><div style="page-break-after:always"></div><?php }?>

<div id="FOLIO<?php echo $pag;?>" style="position:relative; top:<?php echo $MEDIDAS['MARGENSUP'];?>; left:<?php echo $MEDIDAS['MARGENIZQ'];?>; width:<?php echo $MEDIDAS['ANCHO'];?>; height:<?php echo $MEDIDAS['ALTO'];?>; z-index:<?php echo $pag;?>;">

<div id="CABECERA<?php echo $pag;?>" class="formularios" style="position:absolute; display:block; top:0; left:0; width:<?php echo $MEDIDAS['ANCHO'];?>; height:<?php echo $ALTOCABECERA;?>; z-index:<?php echo $pag;?>;">
<table ID="CABECERA" width="100%" align="center" valign="top" bgcolor="" class="Formularios" border="0">
<?php if ($pag<1){?>
    <tr>
        <td align="left" height="20">
            <form action="lisfacturas.php" method="post" name="impresion" id="impresion">
                <input name="desdef" value="<? echo $_POST['desdef'];?>" type="hidden">
                <input name="hastaf" value="<? echo $_POST['hastaf'];?>" type="hidden">
                <input name="desdes" value="<? echo $_POST['desdes'];?>" type="hidden">
                <input name="hastas" value="<? echo $_POST['hastas'];?>" type="hidden">
                <input name="titulo" value="<? echo $_POST['titulo'];?>" type="hidden">

                <?php Desplegable("papel",$_SESSION['DBEMP'].".tipos_papel","IDPapel","Tipo","IDPapel",$papel,"onchange='submit();'","combofamilias","");?>
    			<input type="button" value="I" id="imprimir" onclick="Imprimir()" class="formularios">
            </form>
        </td>
        <td align="left" height="20">
            <form name="cierre" method="post" action="" id="cerrar">
                <input type="button" value="C" id="cerrar" onclick="window.close()" class="formularios">
            </form>
        </td>
    </tr>
<?php } else {?>
    <tr><td colspan="2" height="20"></td></tr>
<?php }?>

	<tr valign="top">
        <td width=149><img src="images/logo<?php echo $_SESSION['empresa'],".jpg";?>" border=0 width="125"></td>
        <td align="center" colspan="2">
            <span class="ta18pxazul"><?php echo $_POST['titulo'];?></span><br>
            Periodo:<?php echo $_POST['desdef'];?>&nbspa&nbsp<?php echo $_POST['hastaf'];?><br>
            Series:<?php echo $_POST['desdes'];?>&nbsp-&nbsp<?php echo $_POST['hastas'];?>
        </td>
    </tr>
</table>
</div>

<div id="PIE<?php echo $pag;?>" class="letrapeq" style="position:absolute; display:block; top:<?php echo $ALTOCABECERA+$ALTOCUERPO;?>; left:0; width:<?php echo $MEDIDAS['ANCHO'];?>; height:<?php echo $ALTOPIE;?>; z-index:<?php echo $pag;?>;">
<TABLE width="100%" id="PIE" class="pielistados" border="0">
    <tr valign="middle">
        <td width=50% align="left" height="<?php echo $ALTOPIE;?>"><? echo date("d/m/Y H:i:s")," (",$_SESSION['login'],")";?></td>
        <td width=50% align="right" height="<?php echo $ALTOPIE;?>"><? echo "P�gina $pag de $totpaginas";?></td>
    </tr>
</TABLE>
</div>

<div id="CUERPO<?php echo $pag;?>" class="formularios" style="position:absolute; display:block; top:<?php echo $ALTOCABECERA;?>; left:0; width:<?php echo $MEDIDAS['ANCHO'];?>; height:<?php echo $ALTOCUERPO;?>; z-index:<?php echo $pag;?>;">
<?php if($pag>0){?>
<table ID="TITULOS" width="100%" align="center" valign="top" bgcolor="" border="0" class="Formularios">
	<tr class='tituloslistados'>
        <th>N.Fact</th><th>Fecha</th><th>Cliente</th><th>Mediacion</th><th>Base Impo</th><th>%IVA</th><th>Cuota IVA</th><th>%RET</th><th>Cuota RET</th><th>TOTAL</th>
	</tr>
<?php }
}?>

<?php
function Totales(){
global $desdes,$hastas,$filtrofecha,$filtroserie;

$sql="select sum(BaseMediacion) as Mediacion from recibos where $filtrofecha$filtroserie;";
$res=mysql_query($sql);
$row=mysql_fetch_array($res);
$totmediacion=$row[0];
?>

<table width="70%" class="formularios" align="center">
<caption>
    <b>RESUMEN DE FACTURAS</b><br>
</caption>
<tr><th></th><th>Bases</th><th>%IVA</th><th>Cuota IVA</th><th>%RET</th><th>Cuota RET</th><th>TOTAL</th></tr>
<?php Subrrayado(7);?>
<tr><td>MEDIACION</td><td align="right"><?php echo $totmediacion;?></td><td colspan="6" align="right"><?php echo $totmediacion;?></td></tr>
<?php
//CALCULAR TOTALES

$sql="select sum(Base) as Base,Iva,Retencion from recibos where $filtrofecha$filtroserie group by Iva,Retencion;";
$res=mysql_query($sql);
$totbase=$totmediacion;
$tottot=$totmediacion;

while ($row=mysql_fetch_array($res)){
    $cuotaiva=$row['Base']*$row['Iva']/100;
    $cuotaretencion=$row['Base']*$row['Retencion']/100;
    $total=round($row['Base']+$cuotaiva-$cuotaretencion,2);

    $totbase=$totbase+$row['Base'];
    $totiva=$totiva+$cuotaiva;
    $totret=$totret+$cuotaretencion;
    $tottot=$tottot+$total;

?>
    <tr>
        <td></td>
        <td align="right"><?php echo $row['Base'];?></td>
        <td align="right"><?php echo $row['Iva'];?></td>
        <td align="right"><?php echo round($cuotaiva,2);?></td>
        <td align="right"><?php echo $row['Retencion'];?></td>
        <td align="right"><?php echo round($cuotaretencion,2);?></td>
        <td align="right"><?php echo $total;?></td>
    </tr>
<?php }?>
    <?php Subrrayado(7);?>
    <tr>
        <td>SUMAS</td>
        <td align="right"><?php echo $totbase;?></td>
        <td align="right"></td>
        <td align="right"><?php echo round($totiva,2);?></td>
        <td align="right"></td>
        <td align="right"><?php echo round($totret,2);?></td>
        <td align="right"><?php echo $tottot;?></td>
    </tr>
</table>
<?php }?>

<html>
<head>
<meta http-equiv="Content-Language" content="es">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<meta name="GENERATOR" content="Dev-PHP 1.9.4">
<title><?php echo $titulo;?></title>
<link href="estilos.css" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/JavaScript">
function Imprimir()
{
	document.impresion.imprimir.style.visibility="hidden";
    document.impresion.papel.style.visibility="hidden";
	document.cierre.cerrar.style.visibility="hidden";
	window.print();
	document.impresion.imprimir.style.visibility="visible";
    document.impresion.papel.style.visibility="visible";
	document.cierre.cerrar.style.visibility="visible";
}
</script>
</head>
<body topmargin="0"  bgproperties="fixed" bottommargin="0" marginheight="0">
    <?php Cabecera(0);Totales();?>
    </div>
</div>
<?php
        $i=0;$linea=999999;$pag=0;
        $totmediacion=0;
		while ($row=mysql_fetch_array($res)) {
            if ($linea>=$lopag) {$linea=0; $pag=$pag+1; Cabecera($pag);}

			$i=$i+1;$linea=$linea+1;
            $cuotaiva=round($row['Base']*$row['Iva']/100,2);
            $cuotaret=round($row['Base']*$row['Retencion']/100,2);
            $totrecibo=$row['BaseMediacion']+$row['Base']+$cuotaiva-$cuotaret;

            $totmediacion=$totmediacion+$row['BaseMediacion'];
		?>
			<tr class='formularios' id='linea<?php echo $i;?>' height="10"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
                <td><?php echo $row['IDRecibo'];?></td>
                <td><?php echo FechaEspaniol($row['Fecha']);?></td>
                <td><?php echo substr($row['RazonSocial'],0,35);?></td>
                <td align="right"><?php if($row['BaseMediacion']!=0) echo $row['BaseMediacion'];?></td>
                <td align="right"><?php echo $row['Base'];?></td>
                <td align="right"><?php if($row['Iva']!=0) echo $row['Iva'];?></td>
                <td align="right"><?php if($cuotaiva!=0) echo $cuotaiva;?></td>
                <td align="right"><?php if($row['Retencion']!=0) echo $row['Retencion'];?></td>
                <td align="right"><?php if($cuotaret!=0) echo $cuotaret;?></td>
                <td align="right"><?php echo $row['Total'];//if($totrecibo!=$row['Total']) echo "*";?></td>
			</tr>
        <?php }?>
    </table>
    </div>
</div>


</body>
</html>
