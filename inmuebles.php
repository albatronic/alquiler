<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "engancha.php";
require "funciones/textos.php";
require "funciones/desplegable.php";
require "funciones/recibos.php";
require "funciones/fechas.php";

//COGER LA CUENTA DE ABONO DE LOS RECIBOS POR DEFECTO.
$sql="select * from ".$_SESSION['DBEMP'].".empresas where IDEmpresa='".$_SESSION['empresa']."';";
$res=mysql_query($sql);
$row=mysql_fetch_array($res);
$cuentadefecto=$row['IDBanco'].$row['IDOficina'].$row['Digito'].$row['Cuenta'];
$ibanDefecto = $row['Iban'];
$bicDefecto = $row['Bic'];

//RECOGIDA DE PARAMETROS
//-----------------------------------------------------------------------------
$pagina=$_GET['pagina'];
if (!isset($pagina)){
	$pagina=$_POST['pagina'];
	if (!isset($pagina)) $pagina=1;
}

$accion=$_POST['Accion'];
if ($accion=='') $accion=$_GET['Accion'];
if ($accion=='') $accion="Consulta";

$orden=$_POST['orden'];
if($orden=='') $orden=$_GET['orden'];
if($orden=='') $orden="IDInmueble";

$columna=$_POST['columna'];
if($columna=='') $columna=$_GET['columna'];

$valor=$_POST['valor'];
if($valor=='') $valor=$_GET['valor'];

$estado=$_POST['estado'];
if($estado=='') $estado=$_GET['estado'];

$tipo=$_POST['tipo'];
if($tipo=='') $tipo=$_GET['tipo'];

$c=str_replace("?","%",$valor);
if ($c=='') $c="1"; else $c=$columna." like '$c%'";

if ($tipo=='') $t="1"; else $t="IDTipoInmueble='$tipo'";
if ($estado=='') $estado="1";

$filtro="where ((".$c.") and (".$estado.") and (".$t."))";
$parametros="estado=$estado&tipo=$tipo&columna=$columna&valor=$valor&orden=$orden";
        	
//Par�metros de formulario de Mantenimiento
$campos['idinmueble']=$_POST['IDInmueble'];
if ($campos['idinmueble']=='') $campos['idinmueble']=$_GET['IDInmueble'];
$campos['direccion']=$_POST['Direccion'];
$campos['codigopostal']=$_POST['CodigoPostal'];
$campos['poblacion']=$_POST['Poblacion'];
$campos['idprovincia']=$_POST['IDProvincia'];
$campos['idinquilino']=$_POST['IDInquilino'];
$campos['idtipoinmueble']=$_POST['IDTipoInmueble'];
$campos['idbanco']=$_POST['IDBanco'];
$campos['idoficina']=$_POST['IDOficina'];
$campos['digito']=$_POST['Digito'];
$campos['cuenta']=$_POST['Cuenta'];
$campos['mobiliario']=$_POST['Mobiliario'];
$campos['plantillacontrato']=$_POST['PlantillaContrato'];
$campos['iban'] = $_POST['Iban'];
$campos['bic'] = $_POST['Bic'];
$campos['mandato'] = $_POST['Mandato'];
$campos['fechaMandato'] = $_POST['FechaMandato'];
?>

<table id="FORMULARIO_SELECCION" width="100%" align="center" valign="top" bgcolor="#CCCCCC">
<tr><td align="center" class="boxtitlewhite">CONSULTA DE INMUEBLES</td></tr>
<tr><td>
	<table align="center" class="formularios" BORDER="0">
	<tr>
		<form name="Consulta" action="contenido.php" method="post">
		<input name="c" value="inmuebles" type="hidden">
		<input name="Accion" value="Consulta" type="hidden">
        <td align="center">Estado:
            <select name="estado" class="ComboFamilias" onchange="submit();">
                <option value="1" <?php if ($estado=="1") echo "selected";?>>Todos</option>
                <option value="ISNULL(IDInquilino)" <?php if ($estado=="ISNULL(IDInquilino)") echo "selected";?>>Libre</option>
                <option value="NOT ISNULL(IDInquilino)" <?php if ($estado=="NOT ISNULL(IDInquilino)") echo "selected";?>>Ocupado</option>
            </select>
        </td>
        <td align="center">Tipo:<?php Desplegable('tipo',$_SESSION['DBEMP'].'.inmuebles_tipos','IDTipo','TipoInmueble','TipoInmueble',$tipo,"onchange='submit();'",'','');?></td>
		<td align="center">Buscar por:
			<select name="columna" class="ComboFamilias">
                            <option value="Direccion" <?php if ($columna=="Direccion") echo "selected";?>>Direcci&oacute;n</option>
                <option value="IDInmueble" <?php if ($columna=="IDInmueble") echo "selected";?>>C&oacute;digo</option>
                <option value="Poblacion" <?php if ($columna=="Poblacion") echo "selected";?>>Poblaci&oacute;n</option>
			</select>
		</td>
		<td>
			Valor(?):<input name="valor" value="<?php echo $valor;?>" type="text" size="30" maxlength="50" class="formularios">
		</td>
        <td align="center">Orden:
            <select name="orden" class="ComboFamilias" onchange="submit();">
                <option value="IDInmueble" <?php if ($orden=="IDInmueble") echo "selected";?>>C&oacute;digo</option>
                <option value="Direccion" <?php if ($orden=="Direccion") echo "selected";?>>Direcci&oacute;n</option>
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
		$error='';
		//SI NO INDICO LA CUENTA DE ABONO, TOMO LA POR DEFECTO PARA LA EMPRESA
		$kk=$campos['idbanco'].$campos['idoficina'].$campos['digito'].$campos['cuenta'];
		if (($kk=='') or ($kk=='00000000000000000000')){
                    $campos['idbanco']=substr($cuentadefecto,0,4);
                    $campos['idoficina']=substr($cuentadefecto,4,4);
                    $campos['digito']=substr($cuentadefecto,8,2);
                    $campos['cuenta']=substr($cuentadefecto,10,10);
                    $campos['iban'] = $ibanDefecto;
                    $campos['bic'] = $bicDefecto;
		}
		$error=ValidaCC($campos['idbanco'],$campos['idoficina'],$campos['digito'],$campos['cuenta']);
		if (strlen($error)==2){$campos['digito']=$error;$error="";}
		if ($campos['direccion']=='') $error="Debe indicar la Direccion del inmueble.";
		if ($campos['poblacion']=='') $error="Debe indicar la Poblacion del inmueble.";
		if ($campos['idprovincia']=='') $error="Debe indicar una provincia.";
		if ($campos['idtipoinmueble']=='') $error="Debe indicar un tipo de Inmueble.";
		if ($error!='') Mensaje($error);
		else {
	        $sql="update inmuebles set Direccion='".CodificaTexto($campos['direccion']).
                    "', CodigoPostal='".$campos['codigopostal'].
                    "', Poblacion='".$campos['poblacion'].
                    "', IDProvincia='".$campos['idprovincia'].
                    //"', IDInquilino='".$campos['idinquilino'].
                    "', IDTipoInmueble='".$campos['idtipoinmueble'].
                    "', IDBanco='".$campos['idbanco'].
                    "', IDOficina='".$campos['idoficina'].
                    "', Digito='".$campos['digito'].
                    "', Cuenta='".$campos['cuenta'].
                    "', Mobiliario='".CodificaTexto($campos['mobiliario']).
                    "', PlantillaContrato='".$campos['plantillacontrato'].
                    "', Iban='" . $campos['iban'] .
                    "', Bic='" . $campos['bic'] .
                    "', Mandato='" . $campos['mandato'] .
                    "', FechaMandato='" . AlmacenaFecha($campos['fechaMandato']) .                       
                    "' where IDInmueble='".$campos['idinmueble']."'";
	        $res=mysql_query($sql);
			if (!$res) Mensaje("No se han podido actualizar los datos. Intentelo de nuevo");
		}
		break;

	case 'Borrar':
		if (BorrarInmueble($campos['idinmueble'])) Limpia();
		break;

	case 'Crear':
		$error='';
		//SI NO INDICO LA CUENTA DE ABONO, TOMO LA POR DEFECTO PARA LA EMPRESA
		$kk=$campos['idbanco'].$campos['idoficina'].$campos['digito'].$campos['cuenta'];
		if (($kk=='') or ($kk=='00000000000000000000')){
                    $campos['idbanco']=substr($cuentadefecto,0,4);
                    $campos['idoficina']=substr($cuentadefecto,4,4);
                    $campos['digito']=substr($cuentadefecto,8,2);
                    $campos['cuenta']=substr($cuentadefecto,10,10);
                    $campos['iban'] = $ibanDefecto;
                    $campos['bic'] = $bicDefecto;                    
		}
		$error=ValidaCC($campos['idbanco'],$campos['idoficina'],$campos['digito'],$campos['cuenta']);
		if (strlen($error)==2){$campos['digito']=$error;$error="";}
		if ($campos['direccion']=='') $error="Debe indicar la Direccion del inmueble.";
		if ($campos['poblacion']=='') $error="Debe indicar la Poblacion del inmueble.";
		if ($campos['idprovincia']=='') $error="Debe indicar una provincia.";
		if ($campos['idtipoinmueble']=='') $error="Debe indicar un tipo de Inmueble.";
		if ($error!='') Mensaje($error);
		else {
			if ($campos['idinmueble']=='') {
				$res=mysql_query("select MAX(IDInmueble) from inmuebles");
				if ($res) $cod=mysql_fetch_array($res); else $cod[0]=0;
				$campos['idinmueble']=$cod[0]+1;
			}
			//$campos['idinmueble']=str_pad($campos['idinmueble'], 10, "0", STR_PAD_RIGHT);
			$valores="'".$campos['idinmueble']."','"
                        .CodificaTexto($campos['direccion'])."','"
                        .$campos['codigopostal']."','"
                        .$campos['poblacion']."','"
                        .$campos['idprovincia']."','"
                        .$campos['idtipoinmueble']."','"
                        .$campos['idbanco']."','"
                        .$campos['idoficina']."','"
                        .$campos['digito']."','"
                        .$campos['cuenta']."','"
                        .CodificaTexto($campos['mobiliario'])."','"
                        .$campos['plantillacontrato']."','"
                        . $campos['iban'] . "','"
                        . $campos['bic'] . "','"
                        . $campos['mandato'] . "','"
                        . AlmacenaFecha($campos['fechaMandato']) . "'";                                

	    	$sql="INSERT INTO inmuebles (IDInmueble,Direccion,CodigoPostal,Poblacion,IDProvincia,IDTipoInmueble,IDBanco,IDOficina,Digito,Cuenta,Mobiliario,PlantillaContrato,Iban,Bic,Mandato,FechaMandato) VALUES (".$valores.");";
			$res=mysql_query($sql);
			if (!$res) Mensaje("No se ha podido crear. Intentelo de nuevo.");
		}
		break;
		
	case 'Editar':
		$res=mysql_query("select * from inmuebles where (IDInmueble='".$campos['idinmueble']."')");
		$row=mysql_fetch_array($res);
		$campos['direccion']=DecodificaTexto($row['Direccion']);
		$campos['poblacion']=$row['Poblacion'];
		$campos['idprovincia']=$row['IDProvincia'];
		$campos['codigopostal']=$row['CodigoPostal'];
		$campos['idinquilino']=$row['IDInquilino'];
		$campos['idtipoinmueble']=$row['IDTipoInmueble'];
		$campos['idbanco']=$row['IDBanco'];
		$campos['idoficina']=$row['IDOficina'];
		$campos['digito']=$row['Digito'];
		$campos['cuenta']=$row['Cuenta'];
		$campos['mobiliario']=DecodificaTexto($row['Mobiliario']);
		$campos['plantillacontrato']=$row['PlantillaContrato'];
                $campos['iban'] = $row['Iban'];
                $campos['bic'] = $row['Bic'];
                $campos['mandato'] = $row['Mandato'];
                $campos['fechaMandato'] = FechaEspaniol($row['FechaMandato']);                
		
		//Buscar el inquilino actual
		if ($campos['idinquilino']!='') {
		    $res=mysql_query("select IDInquilino, RazonSocial from ".$_SESSION['DBEMP'].".inquilinos WHERE (IDInquilino='".$campos['idinquilino']."');");
            $row=mysql_fetch_array($res);
            if ($row[0]!='') $inquilino=$row[1]."(".$row[0].")";
            else $inquilino="SE HA PERDIDO LA VINCULACION CON EL INQUILINO ".$campos['idinquilino'];
        }
		break;

	case 'Consulta':
		break;
}

function Limpia(){
	global $campos,$inquilino;
	$campos=""; $inquilino="";
};

function BorrarInmueble($id){
	$ok=0;
	$m="";
	
	//buscar relaciones con otras tablas: inmuebles_inquilinos,....
    $res=mysql_query("select IDInmueble from inmuebles_inquilinos where IDinmueble='$id'");
	$n=mysql_num_rows($res);
	if ($n) $m="No se puede borrar: ESTA RELACIONADO CON $n INQUILINOS.";

    $res=mysql_query("select IDInmueble from inmuebles_conceptos where IDinmueble='$id'");
	$n=mysql_num_rows($res);
	if ($n) $m="No se puede borrar: TIENE $n CONCEPTOS.";

    $res=mysql_query("select IDInmueble from recibos where IDinmueble='$id'");
	$n=mysql_num_rows($res);
	if ($n) $m="No se puede borrar: TIENE $n RECIBOS.";

	if($m=="") $ok=mysql_query("delete from inmuebles where IDInmueble='$id' limit 1;");
	else Mensaje($m);

	return($ok);
};

function Listado(){
	global $pagina,$filtro,$parametros,$columna,$valor,$estado,$tipo,$orden;
	$gris="#CCCCCC";
	$tampagina=DameParametro('LOPAP',15);
	$foco=$_POST['foco'];
	if ($foco=='') $foco=1;
    	
	$sql="select * from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".inmuebles ".$filtro." order by $orden";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);

	$l['sql']=$sql;
	$l['titulo']="LISTADO DE INMUEBLES";
    $l['columnas']="IDInmueble_T_0_N,Direccion_T_40_N,Poblacion_T_10_N,IDInquilino_T_0_N,IDBanco_T_4_N,IDOficina_T_4_N,Digito_T_2_N,Cuenta_T_10_N";
	$l['filtro']="[Tipo=$tipo] [Estado=$estado] [$columna=$valor] [Orden=$orden]";

?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" class="Formularios">
	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=inmuebles&".$parametros."&Accion=Consulta&pagina=","left",$gris,$l);?>
	</td></tr>

	<tr class="Formularios">
            <th>C&oacute;digo</th>
            <th>Direcci&oacute;n</th>
            <th></th>
  	</tr>
        <form name="listado" action="contenido.php" method="post">
        <input name="c" value="inmuebles" type="hidden">
        <input name="Accion" value="Editar" type="hidden">
        <input name="IDInmueble" value="" type="hidden">
        <input name="orden" value="<?echo $orden;?>" type="hidden">
        <input name="pagina" value="<?echo $pagina;?>" type="hidden">
        <input name="columna" value="<?echo $columna;?>" type="hidden">
        <input name="valor" value="<?echo $valor;?>" type="hidden">
        <input name="estado" value="<?echo $estado;?>" type="hidden">
        <input name="tipo" value="<?echo $tipo;?>" type="hidden">
        <input name="foco" value="" type="hidden">

<?php	
	Subrrayado(3);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=0;
		while ($row=mysql_fetch_array($res) and ($i<$tampagina)) {
			$i=$i+1;
		?>
			<tr class='Formularios' id="linea<?php echo $i;?>"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
    			<td>
                    <input name="l<?echo $i;?>" type="button" value="<?echo $row['IDInmueble'];?>" onclick="IDInmueble.value='<?echo $row['IDInmueble'];?>';foco.value='<?echo $i;?>';submit();" class="formularios">
                </td>
		    	<td><?php echo DecodificaTexto($row['Direccion']);?></td>
                <td>
                    <?php if ($row['IDInquilino']=='') {?>
                            <img src="images/libre.png" height="13" border="0" alt="Est� disponible">
                    <?php } else {?>
                            <img src="images/ocupado.png" height="13" border="0" alt="Est� Ocupado">
                    <?php }?>
                </td>
			</tr>
		<?php }?>
		<script language="JavaScript" type="text/javascript">
        document.listado.l<?echo $foco;?>.focus();
        </script>
        </form>
	<?php }?>

	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=inmuebles&".$parametros."&Accion=Consulta&pagina=","right",$gris,$l);?>
	</td></tr>
</table>
<?php }


function Formulario(){
global $campos,$pagina,$columna,$valor,$inquilino,$estado,$tipo,$orden;

// PONER VALORES POR DEFECTO------------------------------------------

if ($campos['idprovincia']=="") $campos['idprovincia']="18";
//--------------------------------------------------------------------
?>

<table ID="MANTENIMIENTO" width="100%" border="0" class="formularios"  bgcolor="">
	<tr ID="TITULO">
		<td align="center" class="boxtitlewhite">Mantenimiento de Inmuebles</td>
	</tr>
	
	<form action="contenido.php" method="post" name="formulario">
		<input name="c" value="inmuebles" type="hidden">
        <input name="orden" value="<?echo $orden;?>" type="hidden">
        <input name="columna" value="<? echo $columna;?>" type="hidden">
        <input name="valor" value="<? echo $valor;?>" type="hidden">
        <input name="estado" value="<? echo $estado;?>" type="hidden">
        <input name="tipo" value="<? echo $tipo;?>" type="hidden">
        <input name="pagina" value="<?php echo $pagina;?>" type="hidden">
	<TR ID="CUERPO">
		<TD WIDTH="100%">
                    C&oacute;digo:<input name="IDInmueble" type="text" size="6" maxlength="6" value="<?php echo $campos['idinmueble'];?>" class="formularios" <?php if ($campos['idinmueble']!='') echo "readonly";?>>&nbsp
            Saldo:&nbsp;<input type="text" size="10" readonly value="<?echo SaldoInmueble($campos['idinmueble']);?>" class="BlancoFondoRojo">
            <br>
			Direccion:<input name="Direccion" type="text" size="50" maxlength="50" value="<?php echo $campos['direccion'];?>" class="formularios">
            <br>
            Poblaci&oacute;n:<input name="Poblacion" type="text" size="15" maxlength="50" value="<?php echo $campos['poblacion'];?>" class="formularios">
	        Provincia:<?php Desplegable('IDProvincia',$_SESSION['DBEMP'].'.provincias','CODIGO','NOMBRE','NOMBRE',$campos['idprovincia'],'','','');?>
                C&oacute;d.Postal:<input name="CodigoPostal" type="text" size="5" maxlength="5" value="<?php echo $campos['codigopostal'];?>" class="formularios">
            <br>
            Inquilino Actual:<input name="inquilino" type="text" size="50" value="<?php echo $inquilino;?>" class="formularios" readonly>
            <?php if ($campos['idinquilino']=='') {?>
                    <img src="<?php echo "images/libre.png";?>" border="0" alt="Est� disponible">
            <?php } else {?>
                    <a href="javascript:;" onClick="window.open('datosinquilino.php?id=<?php echo $campos['idinquilino'];?>','Inquilino<?php echo $campos['idinquilino'];?>','menubar=no,resizable=yes,scrollbars=yes, width=450, height=190')">
                    <img src="images/ocupado.png" border="0" alt="Ver datos Inquilino">
                    </a>
            <?php }?>
			<br>
            Tipo Inmueble:<?php Desplegable('IDTipoInmueble',$_SESSION['DBEMP'].'.inmuebles_tipos','IDTipo','TipoInmueble','TipoInmueble',$campos['idtipoinmueble'],'','','');?>
			<a href="javascript:;" onClick="window.open('inmueblestipos.php','InmueblesTipos','width=250,height=525')"><img src="images/lupa.png" border="0" alt="Tipos de Inmuebles"></a>
			<br>
			Cta. de Cobro:<?php CuentaCorriente('formulario','IDBanco','IDOficina','Digito','Cuenta');?><br>
                            Iban:<input name="Iban" type="text" size="34" maxlength="34" value="<?php echo $campos['iban']; ?>" class="formularios">
                            Bic:<input name="Bic" type="text" size="11" maxlength="11" value="<?php echo $campos['bic']; ?>" class="formularios"> 
                            <br/>
                            Mandato:<input name="Mandato" type="text" size="35" maxlength="35" value="<?php echo $campos['mandato']; ?>" class="formularios">
                            Fecha Mandato:<input name="FechaMandato" type="text" size="10" maxlength="10" value="<?php echo $campos['fechaMandato']; ?>" class="formularios">                        
                            <br/>                        
			Plantilla Contrato:<?php DesplegablePlantillas("PlantillaContrato","plantillas",$campos['plantillacontrato'],"","formularios");?>
			<a href="javascript:;" onClick="window.open('plantillas/'+PlantillaContrato.value,'Plantilla')"><img src="images/lupa.png" border="0" alt="Ver Plantilla"></a>
            <br>
			Mobiliario:<br><textarea name="Mobiliario" cols="70" rows="9" textarea="textarea" class="formularios"><?php echo $campos['mobiliario'];?></textarea>
		</td>
	</tr>

	<?php Subrrayado(2);?>
    <table id="PIE" width="100%" class="formularios">
	<tr>
            <td align="left">
                <?php if ($campos['idinmueble']!='') {?>
                <a href="javascript:;" onClick="window.open('inmuebleconceptos.php?id=<?echo $campos['idinmueble'];?>','<?echo $campos['idinmueble'];?>','Conceptos','width=980,height=520,resizable=yes,scrollbars=yes')">Conceptos</a>
                <a href="javascript:;" onClick="window.open('contenido.php?c=alquilar&t=Alquilar&columna=inmuebles.IDInmueble&valor=<?echo $campos['idinmueble'];?>','Alquiler','width=900,height=520,resizable=yes,scrollbars=yes')">Alquiler</a>
                <a href="javascript:;" onclick="window.open('contenido.php?c=consultarecibos&t=Recibos&campo=recibos.IDInmueble&desde=<?echo $campos['idinmueble'];?>&hasta=<? echo $campos['idinmueble'];?>&resumido=N&Accion=Consulta','Recibos','width=890,height=620,resizable=yes,scrollbars=yes')">Recibos</a>
                <?php }?>		
            </td>
            <td width="50%" align="center" class="boxtitlewhite">
                <?php if ($campos['idinmueble']!='') {?>
                <input name="Accion" type="submit" value="Guardar" class="formularios">
                <?php if ($inquilino==""){?>
                <input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar el inmueble (",$campos['idinmueble'],") ",$campos['direccion'];?>');"><?php }?>
                <?php } else {?>
                <input name="Accion" type="submit" value="Crear" class="formularios">
                <?php }?>
                <input name="Accion" type="submit" value="Limpiar" class="formularios">
            </td>
	</tr>
	<?php Subrrayado(2);?>
	</form>
    <table>  
</table>

<?php }?>

<table width="100%">
    <tr valign="top">
        <td width="400" bgcolor="#CCCCCC"><?php if ($accion!='') Listado();?></td>
        <td bgcolor="#CCCCCC"><?php Formulario();?></td>
    </tr>
</table>
