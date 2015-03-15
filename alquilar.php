<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "engancha.php";
require "funciones/desplegable.php";
require "funciones/fechas.php";
require "funciones/textos.php";
require "funciones/recibos.php";


//RECOGIDA DE PARAMETROS
//-----------------------------------------------------------------------------
$pagina=$_GET['pagina'];
if (!isset($pagina)){
	$pagina=$_POST['pagina'];
	if (!isset($pagina)) $pagina=1;
}

$accion=$_POST['Accion'];
if ($accion=='') $accion=$_GET['Accion'];
//if ($accion=='') $accion="Consulta";

$estado=$_POST['estado'];
if($estado=='') $estado=$_GET['estado']; if($estado=='') $estado='V';
switch ($estado){
    case 'V': //Solo los vigentes
        $e="FechaFin>='".date('Y-m-d')."'";
        break;
    case 'T': //Todos
        $e="1";
        break;
    case 'N': //Solo los no vigentes
        $e="FechaFin<'".date('Y-m-d')."'";
        break;
    case 'VE': //Los que van a vencer
        $e="FechaFin>='".date('Y-m-d')."' and FechaFin<='".date("Y-m-d",mktime(0,0,0,date('m'),date('d')+45,date('Y')))."'";
        break;
    case 'SU': //Los que van a subir en este mes o el proximo
        $ms=date('m')+1;
        $as=date('Y');
        if($ms>12){$ms=1;$as=$as+1;}
        $e="(MONTH(FechaSubida)=".date('m')." and YEAR(FechaSubida)=".date('Y').") or ";
        $e.="(MONTH(FechaSubida)=".$ms." and YEAR(FechaSubida)=".$as.")";
        break;
}
        

$orden=$_POST['orden'];
if($orden=='') $orden=$_GET['orden'];
if($orden=='') $orden="inmuebles_inquilinos.IDInmueble";

$columna=$_POST['columna'];
if($columna=='') $columna=$_GET['columna'];

$valor=$_POST['valor'];
if($valor=='') $valor=$_GET['valor'];

$c=str_replace("?","%",$valor);
if (($c=='') or ($columna=='')) $c="1"; else $c=$columna." like '$c%'";

$inqui=$_SESSION['DBEMP'].".inquilinos";
$inmu="inmuebles";

$filtro="where ((".$c.") and (".$e.") and (".$inqui.".IDInquilino=inmuebles_inquilinos.IDInquilino) and (inmuebles.IDInmueble=inmuebles_inquilinos.IDInmueble))";
$parametros="columna=$columna&valor=$valor&estado=$estado&orden=$orden";
        	
//Parametros de formulario de Mantenimiento
$campos['idalquiler']=$_POST['IDAlquiler'];
if ($campos['idalquiler']=='') $campos['idalquiler']=$_GET['IDAlquiler'];
$campos['idinmueble']=$_POST['IDInmueble'];
$campos['idinquilino']=$_POST['IDInquilino'];
$campos['idiva']=$_POST['IDIva'];
$campos['retencion']=$_POST['Retencion'];
$campos['fechainicio']=$_POST['FechaInicio'];
$campos['fechafin']=$_POST['FechaFin'];
$campos['fechasubida']=$_POST['FechaSubida'];
$campos['porcentaje']=$_POST['Porcentaje'];
$campos['anos']=$_POST['Anos'];
$campos['periodo']=$_POST['Periodo'];
$campos['fianza']=$_POST['Fianza'];
$campos['direccion']=$_POST['Direccion'];

$inquilino="";
$inmueble="";
if ($campos['idalquiler']!=""){
    $res=mysql_query("select IDInquilino,IDInmueble from inmuebles_inquilinos where IDAlquiler=".$campos['idalquiler']);
    $alq=mysql_fetch_array($res);
    $res=mysql_query("select RazonSocial from ".$inqui." where IDInquilino='".$alq['IDInquilino']."';");
    $row=mysql_fetch_array($res);
    $inquilino="(".$alq['IDInquilino'].") - ".$row[0];

    $res=mysql_query("select Direccion,PlantillaContrato from ".$inmu." where IDInmueble='".$alq['IDInmueble']."';");
    $row=mysql_fetch_array($res);
    $inmueble="(".$alq['IDInmueble'].") - ".$row['Direccion'];
    $plantilla=$row['PlantillaContrato'];
}

//VARIABLES DEL CRITERIO DE BUSQUEDA
$cb[0]=array('','::Todos');
$cb[1]=array($inqui.'.RazonSocial','Raz&oacute;n Social Inquilino');
$cb[2]=array($inqui.'.IDInquilino','C&oacute;digo Inquilino');
$cb[3]=array($inmu.'.IDInmueble','C&oacute;digo Inmueble');
$cb[4]=array($inmu.'.Direccion','Direcci&oacute;n Inmueble');
$cb[5]=array($inmu.'.Poblacion','Poblaci&oacute;n Inmueble');

?>

<table id="FORMULARIO_SELECCION" width="100%" align="center" valign="top" bgcolor="#CCCCCC">
<tr><td align="center" class="boxtitlewhite">CONSULTA DE INMUEBLES ALQUILADOS</td></tr>
<tr><td>
	<table align="center" class="formularios" BORDER="0">
	<tr>
		<form name="Consulta" action="contenido.php" method="post">
		<input name="c" value="alquilar" type="hidden">
		<input name="Accion" value="Consulta" type="hidden">
        <td align="center">Estado:
            <select name="estado" class="formularios" onchange="submit();">
                <option value="T" <?php if ($estado=="T") echo "selected";?>>::Todos</option>
                <option value="V" <?php if ($estado=="V") echo "selected";?>>Vigente</option>
                <option value="N" <?php if ($estado=="N") echo "selected";?> class="Rojo">No Vigente</option>
                <option value="VE" <?php if ($estado=="VE") echo "selected";?> class="Azul">Van a vencer</option>
                <option value="SU" <?php if ($estado=="SU") echo "selected";?> class="Verde">Van a subir</option>
            </select>
        </td>
		<td align="center">Buscar por:
			<select name="columna" class="formularios">
			<?php
                $i=0;
                while ($i<=5){
                    echo "<option value='",$cb[$i][0],"'";
                    if ($columna==$cb[$i][0]) echo "selected";
                    echo ">",$cb[$i][1],"</option>\n";
                    $i++;
                }
            ?>
			</select>
		</td>
		<td>
			Valor(?):<input name="valor" value="<?echo $valor;?>" type="text" size="30" maxlength="50" class="formularios">
		</td>
        <td align="center">Orden:
            <select name="orden" class="ComboFamilias">
                <option value="inmuebles_inquilinos.IDInquilino" <?php if ($orden=="inmuebles_inquilinos.IDInquilino") echo "selected";?>>Inquilino</option>
                <option value="inmuebles_inquilinos.IDInmueble" <?php if ($orden=="inmuebles_inquilinos.IDInmueble") echo "selected";?>>Inmueble</option>
            </select>
        </td>
		<td align="center">
			<input type="image" img src="images\lupa.png">
		</td>
		</form>
	</tr>
	</table>
</td></tr>
<?php Subrrayado(1);?>
</table>

<?PHP
//CONTROL DE LA ACCION A REALIZAR
//-------------------------------------------------------
switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$error=Validar();
		if ($error!='') Mensaje($error);
		else {
	        $sql="update inmuebles_inquilinos set ".
                    "IDInmueble='".$campos['idinmueble']."',".
                    "IDInquilino='".$campos['idinquilino']."',".
                    "IDIva='".$campos['idiva']."',".
                    "Retencion='".$campos['retencion']."',".
                    "FechaInicio='".AlmacenaFecha($campos['fechainicio'])."',".
                    "FechaFin='".AlmacenaFecha($campos['fechafin'])."',".
                    "FechaSubida='".AlmacenaFecha($campos['fechasubida'])."',".
                    "PorcentajeSubida='".$campos['porcentaje']."',".
                    "AnosSubida='".$campos['anos']."',".
                    "PeriodoCobro='".$campos['periodo']."',".
                    "Fianza='".$campos['fianza']."',".
                    "DireccionRecibo='".$campos['direccion']."'".
                    " where (IDAlquiler='".$campos['idalquiler']."');";
	        $res=mysql_query($sql);
			if ($res){ //Poner el codigo de inquilino en el inmueble
			    $nuevoinqui="'".$campos['idinquilino']."'";
			    if(AlmacenaFecha($campos['fechafin'])<date('Ymd')) $nuevoinqui='NULL';
			    $res=mysql_query("update inmuebles set IDInquilino=$nuevoinqui where IDInmueble='".$campos['idinmueble']."' limit 1;");
			    if(!$res) Mensaje("Se han guardado los cambios en el alquiler, pero no se ha actualizado el inmueble con el codigo de inquilino");
            } else Mensaje("No se han podido actualizar los datos. Intentelo de nuevo");
		}
		break;

	case 'Borrar':
		if (BorrarAlquiler($campos['idalquiler'],$campos['idinmueble'],$campos['idinquilino'])) Limpia();
		break;

	case 'Crear':
            $error="";
            if(!Libre($campos['idinmueble'],ValidaFecha($campos['fechainicio']))) $error="El Inmueble esta ocupado por otro inquilino.";

            $error=$error.Validar();
            if ($error!='') Mensaje($error);
            else {
                    $valores="'".$campos['idinmueble']."','"
                .$campos['idinquilino']."','"
                .$campos['idiva']."','"
                .$campos['retencion']."','"
                .AlmacenaFecha($campos['fechainicio'])."','"
                .AlmacenaFecha($campos['fechafin'])."','"
                .AlmacenaFecha($campos['fechasubida'])."','"
                .$campos['porcentaje']."','"
                .$campos['anos']."','"
                .$campos['periodo']."','"
                .$campos['fianza']."','"
                .$campos['direccion']."'";

            $sql="INSERT INTO inmuebles_inquilinos (IDInmueble,IDInquilino,IDIva,Retencion,FechaInicio,FechaFin,FechaSubida,PorcentajeSubida,AnosSubida,PeriodoCobro,Fianza,DireccionRecibo) VALUES (".$valores.");";
                    $res=mysql_query($sql);
                    if (!$res) Mensaje("No se ha podido crear. Intentelo de nuevo.");
                    else {
            //Marcar el inmueble como alquilado por el inquilino en cuestion.
            $sql="UPDATE inmuebles SET IDInquilino='".$campos['idinquilino']."' WHERE IDInmueble='".$campos['idinmueble']."' LIMIT 1;";
            mysql_query($sql);
            Limpia();
                    }
            }
            break;
		
	case 'Editar':
            $res=mysql_query("select * from inmuebles_inquilinos where (IDAlquiler='".$campos['idalquiler']."');");
            $row=mysql_fetch_array($res);
            $campos['idinquilino']=$row['IDInquilino'];
            $campos['idinmueble']=$row['IDInmueble'];
            $campos['idiva']=$row['IDIva'];
            $campos['retencion']=$row['Retencion'];
            $campos['fechainicio']=FechaEspaniol($row['FechaInicio']);
            $campos['fechafin']=FechaEspaniol($row['FechaFin']);
            $campos['fechasubida']=FechaEspaniol($row['FechaSubida']);
            $campos['porcentaje']=$row['PorcentajeSubida'];
            $campos['anos']=$row['AnosSubida'];
            $campos['periodo']=$row['PeriodoCobro'];
            $campos['fianza']=$row['Fianza'];
            $campos['direccion']=$row['DireccionRecibo'];
    	break;

	case 'Finalizar':
            $error="";
            $ffin=AlmacenaFecha($_POST['finalizar']);
            $res=mysql_query("update inmuebles_inquilinos set FechaFin='$ffin',FechaSubida='0000-00-00' where (IDAlquiler='".$campos['idalquiler']."') limit 1;");
            if($res){
                $res=mysql_query("update inmuebles set IDInquilino=NULL where (IDInmueble='".$campos['idinmueble']."' and IDInquilino='".$campos['idinquilino']."') limit 1;");
                if(!$res) $error="Se ha finalizado el contrato pero no se ha desmarcado el codigo de inquilino del inmueble";
                $campos['fechafin']=$_POST['finalizar'];
            } else $error="No se ha podido actualizar el alquiler";
            if($error!="") Mensaje($error);
            break;
}

function Validar(){
    global $campos;

    $error="";
	if ($campos['idinmueble']=='') $error="Debe indicar un inmueble.";
	if ($campos['idinquilino']=='') $error="Debe indicar un inquilino.";
	if ($campos['idiva']=='') $error="Debe indicar un tipo de iva.";
	if ($campos['fechainicio']=='') $error="Debe indicar la fecha de inicio del contrato.";
	if ($campos['fechafin']=='') $error="Debe indicar la fecha de finalizaci�n del contrato.";
	if ($campos['fechasubida']=='') $error="Debe indicar la fecha de la siguiente subida.";
        if (AlmacenaFecha($campos['fechafin'])<AlmacenaFecha($campos['fechainicio'])) $error="La fecha de finalizaci�n debe ser mayor que la de inicio.";
        if (AlmacenaFecha($campos['fechasubida'])<AlmacenaFecha($campos['fechainicio'])) $error="La fecha de subida debe ser mayor o igual que la de inicio.";

	return($error);
}
	
function Libre($idinmu,$fechainicio){
    $sql="select IDInquilino from inmuebles where IDInmueble='$idinmu';";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    $sinmarcar=(''==$row[0]);

    $sql="select count(IDAlquiler) from inmuebles_inquilinos where (IDInmueble='$idinmu' and FechaFin>='$fechainicio');";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    $libre=($row[0]==0);
    
    return($sinmarcar and $libre);
}

function Limpia(){
    global $campos,$inquilino,$inmueble;
    $campos=""; $inquilino=""; $inmueble="";
};

function BorrarAlquiler($id,$idinmu,$idinqui){
    $ok=0;
    $m="";

    if($m==""){
    $ok=mysql_query("delete from inmuebles_inquilinos where IDAlquiler=$id limit 1;");
    if ($ok){//Quitar la marca de alquilado en el inmueble
        $ok=mysql_query("update inmuebles set IDInquilino=NULL where (IDInmueble='$idinmu' and IDInquilino='$idinqui') limit 1;");
    }
} else Mensaje($m);

    return($ok);
};

function Listado(){
    global $pagina,$filtro,$parametros,$columna,$valor,$estado,$orden,$inqui,$inmu;
    $gris="#CCCCCC";
    $tampagina=DameParametro('LOPAP',15);
    $foco=$_POST['foco'];
    if ($foco=='') $foco=1;

    $sql="select * from $inqui,$inmu,inmuebles_inquilinos $filtro ORDER BY $orden ASC;";
    list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="" class="formularios">
    <tr><th colspan="2" class="boxtitlewhite">Listado de Alquileres</th></tr>
	<tr><td colspan="2">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=alquilar&".$parametros."&Accion=Consulta&pagina=","left",$gris,"");?>
	</td></tr>

	<tr class="Formularios">
    <th></th>
    <th>Inquilino / Inmueble</th>
  	</tr>
        <form name="listado" action="contenido.php" method="post">
        <input name="c" value="alquilar" type="hidden">
        <input name="Accion" value="Editar" type="hidden">
        <input name="IDAlquiler" value="" type="hidden">
        <input name="pagina" value="<?echo $pagina;?>" type="hidden">
        <input name="estado" value="<?echo $estado;?>" type="hidden">
        <input name="columna" value="<?echo $columna;?>" type="hidden">
        <input name="valor" value="<?echo $valor;?>" type="hidden">
        <input name="foco" value="" type="hidden">

<?php	
	Subrrayado(2);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=0;
		while ($row=mysql_fetch_array($res) and ($i<$tampagina)) {
			$i=$i+1;
            $color="";
            if ($row['FechaFin']<date('Y-m-d')) $color="Rojo";
            if (($row['FechaFin']>=date('Y-m-d')) and ($row['FechaFin']<=date('Y-m-d',mktime(0,0,0,date('m'),date('d')+45,date('Y'))))) $color="Azul";
            if (substr($row['FechaSubida'],0,7)==date('Y-m')) $color="Verde";
            if (substr($row['FechaSubida'],0,7)==date('Y-m',mktime(0,0,0,date('m')+1,date('d'),date('Y')))) $color="Verde";
		?>
            <tr class='formularios' id="linea<?php echo $i;?>"
                    onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
                    onmouseout="<?php echo "cambiacolor('linea",$i,"','');";?>"
            >
                <td>
                    <input name="l<?php echo $i;?>" type="button" onfocus="value=''" onblur="value=''" onclick="IDAlquiler.value='<?php echo $row['IDAlquiler'];?>';foco.value='<?php echo $i;?>';submit();" class="formularios">
                </td>
                <td class="<?php echo $color;?>">
                <?php echo "(",$row['IDInquilino'],") ",substr(DecodificaTexto($row['RazonSocial']),0,40)," ",FechaEspaniol($row['FechaFin'])," ",FechaEspaniol($row['FechaSubida']);?><br>
                <?php echo "<b>(",$row['IDInmueble'],") ",substr(DecodificaTexto($row['Direccion']),0,40),"</b>";?>
                </td>
            </tr>
<?php
			Subrrayado(2);
		}?>
	<?php }?>
	<script language="JavaScript" type="text/javascript">
        document.listado.l<?echo $foco;?>.focus();
    </script>
    </form>
	<tr><td colspan="2">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=alquilar&".$parametros."&Accion=Consulta&pagina=","right",$gris,"");?>
	</td></tr>
</table>
<?php }


function Formulario(){
global $campos,$pagina,$columna,$valor,$inquilino,$estado,$orden,$inmueble,$plantilla;

// PONER VALORES POR DEFECTO------------------------------------------

if ($campos['idiva']=="") $campos['idiva']="6";
if ($campos['retencion']=="") $campos['retencion']="N";
if ($campos['fechainicio']=="") $campos['fechainicio']=date("d/m/Y");
if ($campos['fechafin']=="") $campos['fechafin']=date("d/m/Y",mktime(0,0,0,date('m'),date('d')+364,date('Y')));
if ($campos['fechasubida']=="") $campos['fechasubida']=$campos['fechafin'];
if ($campos['anos']=="") $campos['anos']="1";
if ($campos['porcentaje']=="") $campos['porcentaje']="0";
if ($campos['periodo']=="") $campos['periodo']="1";
if ($campos['fianza']=="") $campos['fianza']="0";

//--------------------------------------------------------------------
?>

<table ID="MANTENIMIENTO" width="100%" border="0" class="formularios"  bgcolor="">
	<tr ID="TITULO">
            <th colspan="2" class="boxtitlewhite">Mantenimiento de Inmuebles Alquilados</th>
	</tr>
	
	<form action="contenido.php" method="post" name="formulario">
        <input name="c" value="alquilar" type="hidden">
        <input name="columna" value="<?echo $columna;?>" type="hidden">
        <input name="valor" value="<?echo $valor;?>" type="hidden">
        <input name="pagina" value="<?echo $pagina;?>" type="hidden">
        <input name="estado" value="<?echo $estado;?>" type="hidden">
        <input name="orden" value="<?echo $orden;?>" type="hidden">
        <input name="IDAlquiler" value="<?echo $campos['idalquiler'];?>" type="hidden">
        <input name="IDInmueble" value="<?echo $campos['idinmueble'];?>" type="hidden">
        <input name="IDInquilino" value="<?echo $campos['idinquilino'];?>" type="hidden">

	<TR ID="CUERPO">
            <TD WIDTH="100%">
            Inquilino:<input name="Inquilino" type="text" size="50" maxlength="50" value="<?echo $inquilino;?>" class="formularios" readonly>
            <a href="javascript:;" onClick="MuestraInquilinos('formulario','IDInquilino','Inquilino')"><img src="images/lupa.png" width="16" height="16" border=0></a>
            <br>
            Inmueble:<input name="Inmueble" type="text" size="51" maxlength="50" value="<?echo $inmueble;?>" class="formularios" readonly>
            <a href="javascript:;" onClick="MuestraInmuebles('formulario','IDInmueble','Inmueble')"><img src="images/lupa.png" width="16" height="16" border=0></a>
            <br>
            Iva:<?php Desplegable("IDIva",$_SESSION['DBEMP'].".tipos_iva","IDIva","Tipo","IDIva",$campos['idiva'],"","formularios","");?>
            Retencion:<?php DesplegableSN("Retencion",$campos['retencion'],"formularios");?>
            <br>
            F.Inicio:<input name="FechaInicio" type="text" size="10" maxlength="10" value="<?echo $campos['fechainicio'];?>" class="formularios">
            F.Fin:<input name="FechaFin" type="text" size="10" maxlength="10" value="<?echo $campos['fechafin'];?>" class="formularios">
            F.Subida:<input name="FechaSubida" type="text" size="10" maxlength="10" value="<?echo $campos['fechasubida'];?>" class="formularios">
            <br>
            A&ntilde;os entre subidas:<input name="Anos" type="text" size="2" maxlength="2" value="<?echo $campos['anos'];?>" class="formularios">
            Meses a cobrar:<input name="Periodo" type="text" size="2" maxlength="2" value="<?echo $campos['periodo'];?>" class="formularios">
            %Subida:<input name="Porcentaje" type="text" size="5" maxlength="5" value="<? echo $campos['porcentaje'];?>" class="formularios">
            Fianza:<input name="Fianza" type="text" size="7" maxlength="7" value="<?echo $campos['fianza'];?>" class="formularios">
            Direccion Recibo:
            <select name="Direccion" class="formularios">
                <option value="P" <?php if ($campos['direccion']=='P') echo "SELECTED";?>>Piso</option>
                <option value="I" <?php if ($campos['direccion']=='I') echo "SELECTED";?>>Inquilino</option>
            </select>&nbsp;
            Saldo:&nbsp;<input type="text" size="10" readonly value="<?echo SaldoAlquiler($campos['idinquilino'],$campos['idinmueble']);?>" class="BlancoFondoRojo">
            </td>
	</tr>

	<?php Subrrayado(2);?>
    <table id="PIE" width="100%" class="formularios">
	<tr>
            <td width="100%" align="right" class="boxtitlewhite">
                <?php if ($campos['idalquiler']!='') {?>
                <input name="Accion" type="submit" value="Guardar" class="formularios">
                <input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?echo "Desea eliminar el alquiler (",$campos['idinmueble'],") ",$campos['idinquilino'];?>');">
                <?php } else {?>
                <input name="Accion" type="submit" value="Crear" class="formularios">
                <?php }?>
                <input name="Accion" type="submit" value="Limpiar" class="formularios">
            </td>
	</tr>
        <tr>
            <td align="left">
                    <?php if ($campos['idalquiler']!='') { $contrato="contratos/".$campos['idinmueble']."_".$campos['idinquilino']."_".AlmacenaFecha($campos['fechainicio']).".htm";?>
                            <input type="button" onClick="window.open('inmuebleconceptos.php?id=<?echo $campos['idinmueble'];?>','<?echo $campos['idinmueble'];?>','width=980,height=580,resizable=yes,scrollbars=yes')" value="Conceptos" class="formularios" accesskey="C">
                            <?php
                                if (($plantilla!="") and (file_exists("plantillas/".$plantilla))){?>
                    <a href="contenido.php?c=editor&accion=Abrir&fichero=<?echo $plantilla;?>&idalquiler=<?echo $campos['idalquiler'];?>&idinmu=<?echo $campos['idinmueble'];?>&idinqui=<?echo $campos['idinquilino'];?>&fguardar=<?php echo $campos['idinmueble'],"_",$campos['idinquilino'],"_",AlmacenaFecha($campos['fechainicio']);?>" alt="<?php echo $plantilla;?>" target="_blank">Hacer Contrato</a>
                    <a href="contenido.php?c=mandatopdf&idalquiler=<?echo $campos['idalquiler'];?>&idinmu=<?echo $campos['idinmueble'];?>&idinqui=<?echo $campos['idinquilino'];?>" alt="Imprimir Mandato" target="_blank">Imprimir mandato</a>
            <?php } else echo "No tiene plantilla!!";
                                if (file_exists($contrato)){?>
                    <input type="button" onclick="window.open('<?echo $contrato;?>')" value="Ver Contrato" class="formularios" accesskey="V">
            <?php }?>
            <input type="button" onclick="window.open('contenido.php?c=consultarecibos&t=Consulta de Recibos&campo=recibos.IDInquilino&desde=<?echo $campos['idinquilino'];?>&hasta=<? echo $campos['idinquilino'];?>&resumido=N&Accion=Consulta','Recibos','width=900,height=620,resizable=yes,scrollbars=yes')" value="Recibos" class="formularios" accesskey="R">
            <br>
            Dar por finalizado el alquiler el dia:
            <input type="text" name="finalizar" value="<?echo date('d/m/Y');?>" size="10" maxlength="10" class="formularios">
            <input type="submit" name="Accion" value="Finalizar" class="formularios">
        <?php }?>
            </td>
    </tr>
	<?php Subrrayado(2);?>
	</form>
	<script language="JavaScript" type="text/javascript">
	document.formulario.Inquilino.focus();
	</script>
    <table>
    
</table>

<?php }?>

<table width="100%">
    <tr valign="top">
        <td width="50%" bgcolor=""><?php Listado();?></td>
        <td bgcolor="#CCCCCC"><?php Formulario();?></td>
    </tr>
</table>
