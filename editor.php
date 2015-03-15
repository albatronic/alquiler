<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";
require "funciones/textos.php";
require "funciones/desplegable.php";
require "funciones/variables.php";

$ruta="plantillas";
$separador=DameParametro("SEPAR","#");
$EMP=$_SESSION['DBEMP'];
$DATOS=$_SESSION['DBDAT'].$_SESSION['empresa'];


$accion=$_POST['accion']; if ($accion=="") $accion=$_GET['accion'];
$fichero=$_POST['fichero']; if ($fichero=="") $fichero=$_GET['fichero'];

$idinqui=$_POST['idinqui']; if ($idinqui=="") $idinqui=$_GET['idinqui'];
$idinmu=$_POST['idinmu']; if ($idinmu=="") $idinmu=$_GET['idinmu'];
$idalquiler=$_POST['idalquiler']; if ($idalquiler=="") $idalquiler=$_GET['idalquiler'];
$fguardar=$_POST['fguardar']; if ($fguardar=="") $fguardar=$_GET['fguardar'];
if ($fguardar=="") $fguardar=$idinmu."_".$idinqui."_".date("Ymd");


switch ($accion) {
    case 'Guardar':
        GeneraDocumento();
        GuardaDocumento();
        break;

    case 'Guardar e Imprimir':
        GeneraDocumento();
        if (GuardaDocumento()) AbreVentana("contratos/$fguardar.rtf","Contrato","");   
        break;
        
    case 'Generar Documento':
        GeneraDocumento();
        break;

    case 'Abrir':
        $fi=$ruta."/".$fichero;
        $texto=LeeFichero($fi);
        $variables=array();
        $variables=VariablesDocumento($fi);
        
        //LEER DE LA BASE DE DATOS
        $sql="select $EMP.empresas.*,$EMP.provincias.NOMBRE as PROVINCIA from $EMP.empresas,$EMP.provincias where IDEmpresa='".$_SESSION['empresa']."' and IDProvincia=CODIGO";
        $res=mysql_query($sql);
        $EMPRESA=mysql_fetch_array($res);
        
        $sql="select $EMP.inquilinos.*,$EMP.provincias.NOMBRE as PROVINCIA from $EMP.inquilinos,$EMP.provincias where IDInquilino='$idinqui' and IDProvincia=CODIGO";
        $res=mysql_query($sql);
        $INQUILINO=mysql_fetch_array($res);

        $sql="select $DATOS.inmuebles.*,$EMP.provincias.NOMBRE as PROVINCIA from $DATOS.inmuebles,$EMP.provincias where IDInmueble='$idinmu' and IDProvincia=CODIGO";
        $res=mysql_query($sql);
        $INMUEBLE=mysql_fetch_array($res);

        $sql="select * from $DATOS.inmuebles_inquilinos where IDAlquiler='$idalquiler'";
        $res=mysql_query($sql);
        $ALQUILER=mysql_fetch_array($res);
        break;
}

function GuardaDocumento(){
    global $texto,$fguardar;

    $ok=0;
    if (($texto!="") and ($fguardar!="")){
        $fi="contratos/".$fguardar.".rtf";
        $f=fopen($fi,"wb");
        if (fwrite($f,$texto)) {$ok=1; Mensaje("Fichero (".$fguardar.") guardado con EXITO");}
        else Mensaje("OCURRIO UN ERROR AL GUARDAR EL FICHERO: ".$fguardar);
        fclose($f);
        return($ok);
    }
}

function GeneraDocumento(){
    global $ruta,$fichero,$texto,$_POST,$separador;

    $fi=$ruta."/".$fichero;
    $texto=LeeFichero($fi);
    if ($texto!=""){
        reset($_POST);
        foreach ($_POST as $key => $valor) {
            if (($key!="c") and ($key!="fichero") and ($key!="accion") and ($key!="fguardar") and ($key!="idinqui") and ($key!="idinmu")){
                //para cada una de las variables recogidas, voy reemplazando su valor
                $valor=str_replace("<br>","\par }{\f1\fs24\insrsid11365308",$valor);
                $texto=str_replace($separador.$key.$separador,$valor,$texto);
                $texto=str_replace($separador.strtolower($key).$separador,$valor,$texto);
            }
        }
    }
}
?>


<form name="form" action="contenido.php" method="POST">
<input name="c" value="editor" type="hidden">
<input name="accion" value="Abrir" type="hidden">
<input name="fguardar" value="<?php echo $fguardar;?>" type="hidden">
<input name="idalquiler" value="<?php echo $idalquiler;?>" type="hidden">
<table width="90%" align="center" class="formularios">
<tr class="boxtitlewhite">
    <td colspan="2" align="center">
        Plantilla Contrato:&nbsp;<?php DesplegablePlantillas("fichero",$ruta,$fichero,'onchange="submit();"',"formularios");?>
    </td>
</tr>
<tr>
    <td colspan=2">Inquilino:&nbsp;<?php Desplegable('idinqui','inquilinos','IDInquilino','RazonSocial','RazonSocial',$idinqui,'onchange="submit();"','formularios','');?></td>
</tr>
<tr>
    <td colspan=2">Inmueble:&nbsp;<?php Desplegable('idinmu',$_SESSION['DBDAT'].$_SESSION['empresa'].'.inmuebles','IDInmueble','Direccion','IDInmueble',$idinmu,'onchange="submit();"','formularios','');?></td>
</tr>
<?php
//GENERAR LAS FILAS CON LAS VARIABLES A SOLICITAR:
Subrrayado(2);

if (is_array($variables)){?>
    <tr><th colspan="2">VALORES A SUSTITUIR EN LA PLANTILLA</th></tr>
    <?php
    Subrrayado(2);$nvar=0;
    foreach ($variables as $key=>$value){
        $nvar++;
        //buscar el valor
        $valor="";
        list($tabla,$columna)=split(">",$value);
        switch ($tabla){
            case 'empresas':
                $valor=$EMPRESA[$columna];
                break;
            case 'inquilinos':
                $valor=$INQUILINO[$columna];
                break;
            case 'inmuebles':
                $valor=$INMUEBLE[$columna];
                break;
            case 'inmuebles_inquilinos':
                $valor=$ALQUILER[$columna];
                break;
        }
        //$valor=DecodificaTexto($valor);
        ?>
        <tr>
            <td bgcolor='#CCCCCC'><b><?php echo $key;?></b></td>
            <td><input name="<?php echo $key;?>" value="<?php echo $valor;?>" type="text" size="50" class="formularios"></td>
        </tr>
    <?php }
}
?>
</table>

<?php
if ($nvar==0) {
    echo "<table width='100%'><tr><td colspan='3' align='center'>NO HAY VARIABLES DEFINIDAS EN LA PLANTILLA UTILIZANDO EL SEPARADOR ",$separador,"</td></tr></table>";
} else {php ?>
    <table width="100%">
    <tr class="boxtitlewhite">
        <?php if ($fichero!=""){?>
            <td align="center">
                <input name="accion" value="Generar Documento" type="submit" class="formularios">
            </td>
            <td align="center">
                Indique un nombre:&nbsp;&nbsp;<input name="fguardar" type="text" value="<?php echo $fguardar;?>" class="formularios">
                &nbsp;&nbsp;y pulse&nbsp;&nbsp;<input name="accion" value="Guardar" type="submit" class="formularios">
                &nbsp;&nbsp;o&nbsp;&nbsp;<input name="accion" value="Guardar e Imprimir" type="submit" class="formularios">
            </td>
            <?php }?>
    </tr>
    <?php Subrrayado(3);?>
    </table>
<?php }?>
</form>