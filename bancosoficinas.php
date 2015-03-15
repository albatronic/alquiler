<script language="JavaScript" type="text/javascript">
function CogeOficina(banco,oficina,form,campobanco,campooficina){
	window.opener[form][campobanco].value=banco;
	window.opener[form][campooficina].value=oficina;
    window.close();
	window.opener[form][compooficina].focus();

}
</script>

<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";
require "funciones/desplegable.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;


$form=$_GET['form']; if($form=='') $form=$_POST['form'];
$campobanco=$_GET['campobanco']; if($campobanco=='') $campobanco=$_POST['campobanco'];
$campooficina=$_GET['campooficina']; if($campooficina=='') $campooficina=$_POST['campooficina'];

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$banco=$_POST['Banco'];
if (!isset($banco)) $banco=$_GET['Banco'];
if ($banco=='') $filtro="(1=1)"; else $filtro="(bancos_oficinas.IDBanco='$banco')";
$parametros="Banco=$banco&form=$form&campobanco=$campobanco&campooficina=$campooficina";

$idbanco=$_POST['IDBanco'];
if (!isset($idbanco)) $idbanco=$_GET['IDBanco'];
$idoficina=$_POST['IDOficina'];
if (!isset($idoficina)) $idoficina=$_GET['IDOficina'];

$direccion=$_POST['Direccion'];
$poblacion=$_POST['Poblacion'];
$idprovincia=$_POST['IDProvincia'];
$cp=$_POST['CodigoPostal'];
$telefono=$_POST['Telefono'];
$fax=$_POST['Fax'];
$email=$_POST['EMail'];

switch ($accion) {
    case 'Consulta':
        $pagina=1;
        break;
        
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE bancos_oficinas SET Direccion='$direccion', Poblacion='$poblacion', IDProvincia='$idprovincia', CodigoPostal='$cp', Telefono='$telefono', Fax='$fax', EMail='$email' WHERE IDBanco='$idbanco' and IDOficina='$idoficina'";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
        break;

	case 'Borrar':
        $res=mysql_query("select count(IDOficina) from inquilinos where IDBanco='$idbanco' and IDOficina='$idoficina'");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." inquilinos con ese banco y oficina.";
        if ($mensaje!='') Mensaje($mensaje);
        else {
    		$sql="DELETE FROM bancos_oficinas WHERE IDBanco='$idbanco' and IDOficina='$idoficina' LIMIT 1;";
	       	$res=mysql_query($sql);
    		if ($res) Limpia();
	       	else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
        }
		break;

	case 'Crear':
        $idbanco=str_pad($idbanco, 4, "0", STR_PAD_LEFT);
        $idoficina=str_pad($idoficina, 4, "0", STR_PAD_LEFT);
        //VALIDAR EL BANCO
        $res=mysql_query("select count(IDBanco) from bancos where IDBanco='$idbanco';");
        $row=mysql_fetch_array($res);
		if (($row[0]==1) and ($idoficina!='')) {
			$sql="INSERT INTO bancos_oficinas (`IDBanco`,`IDOficina`, `Direccion`, `Poblacion`,`IDProvincia`,`CodigoPostal`,`Telefono`,`Fax`,`EMail`) VALUES ('$idbanco','$idoficina','$direccion','$poblacion','$idprovincia','$cp','$telefono','$fax','$email');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else {Mensaje("No se ha podido insertar la nueva oficina. Inténtelo de nuevo.");$idoficina='';}
		} else Mensaje("Debe indicar una banco existente y una oficina.");
		break;
		
	case 'Editar':
        $sql="select * from bancos_oficinas where IDBanco='$idbanco' and IDOficina='$idoficina'";
		$res=mysql_query($sql);
		$row=mysql_fetch_array($res);
		$idbanco=$row['IDBanco']; $idoficina=$row['IDOficina']; $direccion=$row['Direccion']; $poblacion=$row['Poblacion'];
		$idprovincia=$row['IDProvincia']; $cp=$row['CodigoPostal']; $telefono=$row['Telefono']; $fax=$row['Fax']; $email=$row['EMail'];
		break;
}

function Limpia(){
	global $idbanco,$idoficina,$direccion,$poblacion,$idprovincia,$cp,$telefono,$fax,$email;
	$idbanco='';$idoficina='';$direccion='';$poblacion='';$idprovincia='';$cp='';$telefono='';$fax='';$email='';
};


function Listado(){
	global $pagina,$parametros,$filtro,$banco,$form,$campobanco,$campooficina;
	$gris="#CCCCCC";
	$tampagina=15;
    		
	$sql="select bancos_oficinas.*, bancos.banco as Banco from bancos_oficinas,bancos where bancos.IDBanco=bancos_oficinas.IDBanco and $filtro order by IDBanco,IDOficina";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios">
    <tr><td align="center" class="blancoazul">OFICINAS BANCARIAS</td></tr>

    <tr><td align="center" class="blancoazul">
            <form name="formulario" action="contenido.php" method="post">
            <input name="c" type="hidden" value="bancosoficinas">
            <input name="form" type="hidden" value="<?echo $form;?>">
            <input name="campobanco" type="hidden" value="<?echo $campobanco;?>">
            <input name="campooficina" type="hidden" value="<?echo $campooficina;?>">
            Banco:<?DesplegableBanco('Banco',$banco,'formularios',"onchange='submit()'");?>
            <input name="Accion" value="Consulta" type="submit" class="formularios">
    </td></tr>
</table>

<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="5">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=bancosoficinas&".$parametros."&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
        <th width="16"></th>
        <th>Banco</th>
        <th>Oficina</th>
        <th>Direccion</th>
        <th>Poblacion</th>
  	</tr>
<?php	
	Subrrayado(5);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>"
       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
		    	<td width="16"><a href="contenido.php?c=bancosoficinas&Accion=Editar&IDBanco=<?echo $row['IDBanco'];?>&IDOficina=<?echo $row['IDOficina'];?>&pagina=<?php echo $pagina;?>&<?echo $parametros;?>"><img src="images/botoneditar.png" border="0" alt="<?echo $row['Banco'];?>"></a></td>
                <td align="center"><?echo $row['IDBanco'];?></td>
                <td align="center" onClick="<?echo "CogeOficina('",$row['IDBanco'],"','",$row['IDOficina'],"','",$form,"','",$campobanco,"','",$campooficina,"')";?>">
                    <?echo $row['IDOficina'];?>
                </td>
                <td><?php echo $row['Direccion'];?></td>
                <td><?php echo $row['Poblacion'];?></td>
			</tr>
<?php
		}
	}?>

	<tr><td colspan="5">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=bancosoficinas&".$parametros."&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?php
}

function Formulario(){
global $idbanco,$idoficina,$direccion,$poblacion,$idprovincia,$telefono,$cp,$fax,$email,$pagina,$banco,$form,$campobanco,$campooficina;
?>
<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Mantenimiento de OFICINAS BANCARIAS</td></tr>
<tr><td>
<table width="100%" align="center" class="ComboFamilias">
  <form name="formulario" action="contenido.php" method="post">
  	<input name="c" type="hidden" value="bancosoficinas">
  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
  	<input name="Banco" type="hidden" value="<?echo $banco;?>">
  	<input name="form" type="hidden" value="<?echo $form;?>">
    <input name="campobanco" type="hidden" value="<?echo $campobanco;?>">
  	<input name="campooficina" type="hidden" value="<?echo $campooficina;?>">
	<tr><td>
        Banco:<input name="IDBanco" type="text" size="4" maxlength="4" value="<? echo $idbanco;?>" class="formularios">
        <img src="images/lupa.png" border="0" onclick="MuestraBancos('formulario','IDBanco');">
        Oficina:<input name="IDOficina" type="text" size="4" maxlength="4" value="<?php echo $idoficina;?>" class="formularios">
        Direccion:<input name="Direccion" type="text" size="25" maxlength="50" value="<?php echo $direccion;?>" class="formularios">
        Poblacion:<input name="Poblacion" type="text" size="25" maxlength="50" value="<?php echo $poblacion;?>" class="formularios">
        Provincia:<?php Desplegable('IDProvincia',$_SESSION['DBEMP'].'.provincias','CODIGO','NOMBRE','NOMBRE',$idprovincia,'','','');?>
        Cod.Postal:<input name="CodigoPostal" type="text" size="5" maxlength="5" value="<?php echo $cp;?>" class="formularios">
        <br>
        Telefono:<input name="Telefono" type="text" size="25" maxlength="30" value="<?php echo $telefono;?>" class="formularios">
        Fax:<input name="Fax" type="text" size="25" maxlength="30" value="<?php echo $fax;?>" class="formularios">
        EMail:<input name="EMail" type="text" size="25" maxlength="50" value="<?php echo $email;?>" class="formularios">
    </td></tr>

	<tr><td align="center">
		<?php if ($idoficina!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar la oficina ",$direccion;?>');">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.IDBanco.focus();
</script>
</table>
</td></tr>
</table>
<?php
}
?>


<table width="100%">
    <tr>
        <td width="100%"><?Listado();?></td>
    </tr>
    <tr>
        <td width="100%"><?Formulario();?></td>
    </tr>
</table>
