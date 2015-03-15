<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/textos.php";
require "funciones/desplegable.php";

//RECOGIDA DE PARAMETROS
//-----------------------------------------------------------------------------
$pagina=$_GET['pagina'];
if (!isset($pagina)){
	$pagina=$_POST['pagina'];
	if (!isset($pagina)) $pagina=1;
}

$accion=$_POST['Accion'];
if ($accion=='') $accion=$_GET['Accion'];
	
//Parámetros de formulario de Mantenimiento
$campos['idproveedor']=$_POST['IDProveedor'];
if ($campos['idproveedor']=='') $campos['idproveedor']=$_GET['IDProveedor'];
$campos['razonsocial']=$_POST['RazonSocial'];
$campos['nombrecomercial']=$_POST['NombreComercial'];
$campos['cif']=$_POST['Cif'];
$campos['direccion']=$_POST['Direccion'];
$campos['poblacion']=$_POST['Poblacion'];
$campos['idprovincia']=$_POST['IDProvincia'];
$campos['codigopostal']=$_POST['CodigoPostal'];
$campos['telefono']=$_POST['Telefono'];
$campos['fax']=$_POST['Fax'];
$campos['movil']=$_POST['Movil'];
$campos['email']=$_POST['EMail'];
$campos['web']=$_POST['Web'];
$campos['ccontable']=$_POST['CContable'];
$campos['banco']=$_POST['Banco'];
$campos['cbanco']=$_POST['CBanco'];
$campos['observaciones']=$_POST['Observaciones'];
$campos['factualizacion']=$_POST['FActualizacion'];
?>

<table id="FORMULARIO_SELECCION" width="100%" align="center" valign="top" bgcolor="#CCCCCC">
<tr><td align="center" class="boxtitlewhite">CONSULTA DE PROVEEDORES</td></tr>
<tr><td>
	<table align="center" class="formularios" BORDER="0">
	<tr>
		<form name="Consulta" action="contenido.php" method="post">
		<input name="c" value="proveedores" type="hidden">
		<input name="Accion" value="Consulta" type="hidden">
		<td align="center">Buscar por:
			<select name="columna" class="ComboFamilias">
				<option value="RazonSocial">Razón Social</option>
				<option value="NombreComercial">Nombre Comercial</option>
                <option value="Direccion">Domicilio</option>
				<option value="Poblacion">Población</option>
				<option value="Telefono">Telefono</option>
				<option value="Movil">Móvil</option>
				<option value="Cif">DNI/CIF</option>				
			</select>
		</td>
		<td>
			Valor(?):<input name="valor" type="text" size="40" maxlength="50" class="formularios">
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
		if ($campos['razonsocial']=='') $error="Debe indicar la Razón Social del proveedor.";
		if ($campos['idprovincia']=='') $error="Debe indicar una provincia.";
		if ($error!='') Mensaje($error);
		else {
	        $sql="update proveedores set RazonSocial='".CodificaTexto($campos['razonsocial']).
					"', NombreComercial='".CodificaTexto($campos['nombrecomercial']).
					"', Cif='".$campos['cif'].
					"', Direccion='".CodificaTexto($campos['direccion']).
					"', Poblacion='".$campos['poblacion'].
					"', IDProvincia='".$campos['idprovincia'].
					"', CodigoPostal='".$campos['codigopostal'].
					"', Telefono='".$campos['telefono'].
					"', Fax='".$campos['fax'].
					"', Banco='".$campos['banco'].
					"',	CBanco='".$campos['cbanco'].
					"', CContable='".$campos['ccontable'].
					"', Observaciones='".CodificaTexto($campos['observaciones']).
					"', Web='".$campos['web'].
					"', EMail='".$campos['email'].
					"', Movil='".$campos['movil'].
					"', FActualizacion='".date("Ymd His").
					"' where IDProveedor='".$campos['idproveedor']."'";
	        $res=mysql_query($sql);
			if (!$res) Mensaje("No se han podido actualizar los datos. Inténtelo de nuevo");
		}
		break;

	case 'Borrar':
		if (BorrarProveedor($campos['idproveedor'])) Limpia();
		break;

	case 'Crear':
		$error='';
		if ($campos['razonsocial']=='') $error="Debe indicar la Razón Social del proveedor.";
		if ($campos['idprovincia']=='') $error="Debe indicar una provincia.";
		if ($error!='') Mensaje($error);
		else {
			if ($campos['idproveedor']=='') {
				$res=mysql_query("select MAX(IDProveedor) from proveedores");
				if ($res) $cod=mysql_fetch_array($res); else $cod[0]=0;
				$campos['idproveedor']=$cod[0]+1;
			}
			$campos['idproveedor']=str_pad($campos['idproveedor'], 10, "0", STR_PAD_RIGHT);
	        $campos['ccontable']=$campos['idproveedor'];	
			if ($campos['nombrecomercial']=='') $campos['nombrecomercial']=$campos['razonsocial'];
			$valores="'".$campos['idproveedor']."','"
                        .CodificaTexto($campos['razonsocial'])."','"
                        .CodificaTexto($campos['nombrecomercial'])."','"
                        .$campos['cif']."','"
                        .CodificaTexto($campos['direccion'])."','"
                        .$campos['poblacion']."','"
                        .$campos['idprovincia']."','"
                        .$campos['codigopostal']."','"
                        .$campos['telefono']."','"
                        .$campos['fax']."','"
                        .$campos['movil']."','"
                        .$campos['email']."','"
                        .$campos['web']."','"
                        .$campos['ccontable']."','"
                        .$campos['banco']."','"
                        .$campos['cbanco']."','"
                        .CodificaTexto($campos['observaciones'])."','"
                        .date("Ymd His")."'";

	    	$sql="INSERT INTO proveedores VALUES (".$valores.")";
			$res=mysql_query($sql);
			if (!$res) Mensaje("No se ha podido crear. Inténtelo de nuevo.");
		}
		break;
		
	case 'Editar':
		$res=mysql_query("select * from proveedores where (IDProveedor='".$campos['idproveedor']."')");
		$row=mysql_fetch_array($res);
		$campos['razonsocial']=DecodificaTexto($row['RazonSocial']);
		$campos['nombrecomercial']=DecodificaTexto($row['NombreComercial']);
		$campos['cif']=$row['Cif'];
		$campos['direccion']=DecodificaTexto($row['Direccion']);
		$campos['poblacion']=$row['Poblacion'];
		$campos['idprovincia']=$row['IDProvincia'];
		$campos['codigopostal']=$row['CodigoPostal'];
		$campos['telefono']=$row['Telefono'];
		$campos['fax']=$row['Fax'];
		$campos['banco']=$row['Banco'];
		$campos['cbanco']=$row['CBanco'];
		$campos['ccontable']=$row['CContable'];
		$campos['observaciones']=DecodificaTexto($row['Observaciones']);
		$campos['web']=$row['Web'];
		$campos['email']=$row['EMail'];
		$campos['movil']=$row['Movil'];
		$campos['factualizacion']=$row['FActualizacion'];
		break;

	case 'Consulta':
		$columna=$_POST['columna'];
		if($columna=='') $columna=$_GET['columna'];
		
		$valor=$_POST['valor'];
		if($valor=='') $valor=$_GET['valor'];
		$c=str_replace("?","%",$valor);
		if ($c=='') $c=$columna." like '%'"; else $c=$columna." like '$c'";

		$filtro="where (".$c.") and (proveedores.IDProvincia=".$_SESSION['DBEMP'].".provincias.CODIGO)";
		$parametros="columna=$columna&valor=$valor";
		Listado();
		break;
}

function Limpia(){
	global $campos;
	$campos="";
};

function BorrarProveedor($id){
	$ok=0;
	$m="";
	
	//buscar relaciones con otras tablas: pedidos, facturas, ...

	$res=mysql_query("select IDProveedor from frecibidas_cab where IDProveedor='$id'");
	$n=mysql_num_rows($res);
	if ($n) $m="No se puede borrar. Tiene facturas.";
	
	if($m==""){
		$ok=mysql_query("delete from proveedores where IDProveedor='$id' limit 1;");
	} else
	{
		Mensaje($m);
	}
	return($ok);
};

function Listado(){
	global $pagina,$filtro,$parametros;
	$gris="#CCCCCC";
	$tampagina=DameParametro('LOPAP',10);
	
	$sql="select proveedores.*,".$_SESSION['DBEMP'].".provincias.NOMBRE as Provincia from proveedores,".$_SESSION['DBEMP'].".provincias ".$filtro." order by RazonSocial";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" class="ComboFamilias">
	<tr><td colspan="7">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=proveedores&".$parametros."&Accion=Consulta&pagina=","left",$gris,"");?>
	</td></tr>

	<tr class="Formularios">
    <td><strong>Código</strong></td>
    <td><strong>Razon Social</strong></td>
    <td><strong>Dirección</strong></td>
    <td><strong>Población</strong></td>
    <td><strong>Provincia</strong></td>
    <td><strong>Teléfono</strong></td>
    <td><strong>Móvil</strong></td>
  	</tr>
<?php	
	Subrrayado(7);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class='Formularios' id="linea<?php echo $i;?>" title="<?php echo "Observaciones: ",DecodificaTexto($row['Observaciones']);?>"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
    			<td><a href="contenido.php?c=proveedores&Accion=Editar&IDProveedor=<?php echo $row['IDProveedor'];?>&pagina=<?php echo $pagina,"&",$parametros;?>" title="Modificar/Borrar"><?php echo $row['IDProveedor'];?></a></td>
		    	<td><?php echo DecodificaTexto($row['RazonSocial']);?></td>
		    	<td><?php echo DecodificaTexto($row['Direccion']);?></td>
	    		<td><?php echo $row['Poblacion'];?></td>
			    <td><?php echo $row['Provincia'];?></td>
		    	<td><?php echo $row['Telefono'];?></td>
			    <td><?php echo $row['Movil'];?></td>
			</tr>
<?php
			Subrrayado(7);
		}
	}?>

	<tr><td colspan="7">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=proveedores&".$parametros."&Accion=Consulta&pagina=","right",$gris,"");?>
	</td></tr>
</table>
<?php }?>


<?php
// PONER VALORES POR DEFECTO------------------------------------------

if ($campos['idprovincia']=="") $campos['idprovincia']="18";
//--------------------------------------------------------------------
?>
<table ID="MANTENIMIENTO" width="100%" border="0" class="formularios"  bgcolor="">
	<tr ID="TITULO">
		<td colspan="2" align="center" class="boxtitlewhite">Mantenimiento de Proveedores</td>
	</tr>
	
	<form action="contenido.php" method="post" name="formulario">
		<input name="c" value="proveedores" type="hidden">
	<TR ID="CUERPO">
		<TD WIDTH="100%" colspan="2">
			Código:<input name="IDProveedor" type="text" size="10" maxlength="10" value="<?php echo $campos['idproveedor'];?>" class="formularios" <?php if ($campos['idproveedor']!='') echo "readonly";?>>
			Razón Social:<input name="RazonSocial" type="text" size="60" maxlength="50" value="<?php echo $campos['razonsocial'];?>" class="formularios">
			CIF:<input name="Cif" type="text" size="10" maxlength="10" value="<?php echo $campos['cif'];?>" class="formularios">
			<br>
			Nombre Comercial:<input name="NombreComercial" type="text" size="60" maxlength="50" value="<?php echo $campos['nombrecomercial'];?>" class="formularios">
			<br>
			Direccion:<input name="Direccion" type="text" size="40" maxlength="50" value="<?php echo $campos['direccion'];?>" class="formularios">
			Población:<input name="Poblacion" type="text" size="20" maxlength="30" value="<?php echo $campos['poblacion'];?>" class="formularios">
	        Provincia:<?php Desplegable('IDProvincia',$_SESSION['DBEMP'].'.provincias','CODIGO','NOMBRE','NOMBRE',$campos['idprovincia'],'','','');?>
        <br>
            Cód.Postal:<input name="CodigoPostal" type="text" size="5" maxlength="5" value="<?php echo $campos['codigopostal'];?>" class="formularios">
			Teléfono:<input name="Telefono" type="text" size="10" maxlength="30" value="<?php echo $campos['telefono'];?>" class="formularios">
			Fax:<input name="Fax" type="text" size="10" maxlength="30" value="<?php echo $campos['fax'];?>" class="formularios">
			Móvil:<input name="Movil" type="text" size="10" maxlength="30" value="<?php echo $campos['movil'];?>" class="formularios">
            Cuenta Contable:<input name="CContable" type="text" size="10" maxlength="10" value="<?php echo $campos['ccontable'];?>" class="formularios">
			<br>
			E-mail:<input name="EMail" type="text" size="30" maxlength="50" value="<?php echo $campos['email'];?>" class="formularios">
 			Web:<input name="Web" type="text" size="30" maxlength="50" value="<?php echo $campos['web'];?>" class="formularios">
			<br>
			Banco:<input name="Banco" type="text" size="18" maxlength="50" value="<?php echo $campos['banco'];?>" class="formularios">
			Cta.Corriente:<input name="CBanco" type="text" size="22" maxlength="20" value="<?php echo $campos['cbanco'];?>" class="formularios">
			<br>
			Observaciones:<br><textarea name="Observaciones" cols="100" rows="3" textarea="textarea" class="formularios"><?php echo $campos['observaciones'];?></textarea>
			<?php echo $campos['factualizacion'];?>
		</td>
	</tr>

	<?php
    if ($esadm){
         Subrrayado(2);?>
	<tr id="PIE">
		<td align="left">
			<?php if ($campos['idproveedor']!='') {?>
				<a href="contenido.php?c=articulosxproveedor&idproveedor=<?php echo $campos['idproveedor'];?>">Compras</a>
			<?php }?>		
		</td>
		<td align="right">
			<?php if ($campos['idproveedor']!='') {?>
			<input name="Accion" type="submit" value="Guardar" class="formularios">&nbsp;&nbsp;
			<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar el proveedor ",$campos['razonsocial'];?>');">&nbsp;&nbsp;
			<?php } else {?>
			<input name="Accion" type="submit" value="Crear" class="formularios">&nbsp;&nbsp;
			<?php }?>
			<input name="Accion" type="submit" value="Limpiar" class="formularios">
		</td>
	</tr>
	<?php Subrrayado(2);?>
	</form>
	<script language="JavaScript" type="text/javascript">
	document.formulario.RazonSocial.focus();
	</script>
    <?php } ?>
</table>
