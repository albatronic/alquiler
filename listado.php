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

//La setencia sql que genera el listado
$sql=$_POST['LISs'];
if ($sql==''){
    echo "No se ha indicando qu� informaci�n hay que listar.";
    exit;
}

//Quita la barra invertida que viene en la url
$sql=str_replace('\\','',$sql);

//Las columnas a listar vienen separadas por ','
$campos=$_POST['LISc'];
$aux=split(',',$campos);
$ncampos=count($aux);
if ($ncampos==0) {echo "no hay campos para listar."; exit;}

//Las propiedades de cada columna vienen con el formato:
//            nombre_tipo_ancho_sumatorio
//Tipo debe ser:
//  T:texto,N:Numerico,M:Moneda,F:Fecha
//
//Ancho debe ser un valor n�merico e indica el n� de caracteres a mostrar de cada columna
//Si Ancho es 0, se entiende que se mostraran todos
//
//Sumatorio debe ser: S � N.
//Si se indica S se calcular�n los totales para esa columna

$i=0;
while ($i<$ncampos){
    $columnas[$i]=split('_',$aux[$i]);
    $i=$i+1;
}


$titulo=$_POST['LISt'];
$filtro=$_POST['LISf'];

$res=mysql_query($sql);
//Calcular el n�mero de registros y el n� total de p�ginas
list($kk,$nregistros,$totpaginas)=Paginar($sql,1,$lopag);

function Cabecera($pag){
    global $sql,$campos,$columnas,$titulo,$ncampos,$filtro,$nregistros,$totpaginas,$papel,$MEDIDAS;

    $ALTOCABECERA=149;
    $ALTOPIE=15;
    $ALTOCUERPO=$MEDIDAS['ALTO']-$ALTOCABECERA-$ALTOPIE;
    
if ($pag>1) {?></table></div></div><div style="page-break-after:always"></div><?php }?>

<div id="FOLIO<?php echo $pag;?>" style="position:relative; top:<?php echo $MEDIDAS['MARGENSUP'];?>; left:<?php echo $MEDIDAS['MARGENIZQ'];?>; width:<?php echo $MEDIDAS['ANCHO'];?>; height:<?php echo $MEDIDAS['ALTO'];?>; z-index:<?php echo $pag;?>;">

<div id="CABECERA<?php echo $pag;?>" class="formularios" style="position:absolute; display:block; top:0; left:0; width:<?php echo $MEDIDAS['ANCHO'];?>; height:<?php echo $ALTOCABECERA;?>; z-index:<?php echo $pag;?>;">
<table ID="CABECERA" width="100%" align="center" valign="top" bgcolor="" class="Formularios" border="0">
<?php if ($pag==1){?>
    <tr>
        <td align="left" height="20">
            <form action="listado.php" method="post" name="impresion" id="impresion">
                <input name="LISs" value="<? echo $sql;?>" type="hidden">
                <input name="LISc" value="<? echo $campos;?>" type="hidden">
                <input name="LISt" value="<? echo $titulo;?>" type="hidden">
                <input name="LISf" value="<? echo $filtro;?>" type="hidden">
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
        <td width=149><img src="images/logo<?php echo $_SESSION['empresa'],".jpg";?>" border=0 width="95"></td>
        <td align="center" colspan="2">
            <span class="ta18pxazul"><?php echo $titulo;?></span><br>
            <span class="formularios"><?php echo $filtro;?></span>
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
<table ID="TITULOS" width="100%" align="center" valign="top" bgcolor="" border="0" class="Formularios">
	<tr class='tituloslistados'>
	<?php
    $i=0;
    while ($i<$ncampos){
		echo "<th>",$columnas[$i][0],"</th>";
        $i=$i+1;
	}?>
	</tr>
<?php
}
?>

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

<?php
        $i=0;$linea=999999;$pag=0;
        
        //Poner columnas de totales a 0...............
        
		while ($row=mysql_fetch_array($res)) {
            if ($linea>=$lopag) {$linea=0; $pag=$pag+1; Cabecera($pag);}
			$i=$i+1;$linea=$linea+1;
			//Incrementar las columnas de totales.....................
            //$base=$base+$row['BaseImponible']; $total=$total+$row['Total'];
            //$iva=$iva+$row['CuotaIva']; $recargo=$recargo+$row['CuotaRecargo'];
		?>
			<tr class='formularios' id='linea<?php echo $i;?>' height="10"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
			<?php
                $j=0;
                while ($j<$ncampos){
                    //Alineacion del campo
                    $tipo=$columnas[$j][1];
                    if (($tipo=='N') or ($tipo=='M')) $align="right"; else $align="left";
                    $valor=$row[$columnas[$j][0]];
                    //Formato del campo
                    if ($tipo=='M') $valor=moneda($valor);
                    if ($tipo=='F') $valor=FechaEspaniol($valor);
                    //Tama�o del campo
                    if ($columnas[$j][2]>0) $valor=substr($valor,0,$columnas[$j][2]);

                    echo "<td align=$align>$valor</td>";

                    //Calculo de totales
                    if ($columnas[$j][3]=='S') $totales[$j]=$totales[$j]+$row[$columnas[$j][0]];

                    $j=$j+1;
                }
            ?>
			</tr>
        <?php }?>
        
    <tr>
        <?php//SACAR LOS TOTALES
        $j=0;
        while ($j<$ncampos){
            if ($columnas[$j][1]=='M') $valor=moneda($totales[$j]); else $valor=$totales[$j];
            echo "<td align='right' class='tituloslistados'><b>$valor</b></td>";
            $j=$j+1;
        }?>
    </tr>

    </table>
    </div>
</div>
</body>
</html>
