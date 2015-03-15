<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";
require "funciones/desplegable.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];

//Parámetros del formulario de consulta
$idempresa=$_POST['IDEmpresa'];
if($idempresa=='') $idempresa=$_GET['IDEmpresa'];
$parametros="IDEmpresa=$idempresa";

//Parámetros de formulario de Mantenimiento
$idsucursal=$_POST['idsucursal'];
if (!isset($idsucursal)) $idsucursal=$_GET['idsucursal'];
$nombre=$_POST['Nombre'];
$direccion=$_POST['Direccion'];
$poblacion=$_POST['Poblacion'];
$idprovincia=$_POST['IDProvincia'];
$codpostal=$_POST['CodPostal'];
$telefono=$_POST['Telefono'];
$fax=$_POST['Fax'];
$email=$_POST['EMail'];
$responsable=$_POST['Responsable'];
$contador=$_POST['Contador'];

?>
<script language="JavaScript" type="text/javascript">
function Recarga(){
	document.Consulta.submit();
}
</script>

<table id="FORMULARIO_SELECCION" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
<tr><td align="center" class="boxtitlewhite">INDIQUE UNA EMPRESA</td></tr>
<tr><td>
	<table align="center" class="formularios">
	<form name="Consulta" action="contenido.php" method="post">
		<input type="hidden" name="c" value="sucursales">
		<input name="Accion" Value="Consulta" type="hidden">
	<tr>
		<td ALign="CENTER">
			Empresa: <?php Desplegable('IDEmpresa','empresas','IDEmpresa','RazonSocial','RazonSocial',$idempresa,'onchange="Recarga();"','','');?>
		</td>
	</tr>
	</form>
	<script language="JavaScript" type="text/javascript">
	document.Consulta.idempresa.focus();
	</script>
	</table>
</td></tr>
<?php Subrrayado(1);?>
</table>

<?php
if ($idempresa=='') exit;

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$error="";
		if ($nombre=='') $error="Debe indicar un nombre";
		if ($idprovincia=='') $error="Debe indicar una provincia";
		if ($error!="") Mensaje($error);
		else {
			$sql="UPDATE `sucursales` SET `Nombre`='$nombre',`Direccion`='$direccion',`Poblacion`='$poblacion',`IDProvincia`='$idprovincia',`CodigoPostal`='$codpostal',
						 `Telefono`='$telefono',`Fax`='$fax',`EMail`='$email',`Responsable`='$responsable', `ContadorFacturas`='$contador'
					WHERE (".$parametros.") and (`IDSucursal`='$idsucursal') LIMIT 1";
			$res=mysql_query($sql);
			if ($res) Limpia();
        	else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
		}
        break;

	case 'Borrar':
        if (BorrarSucursal($parametros,$idsucursal)) Limpia();
       	else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
		break;

	case 'Crear':
		$error="";
        if ($idempresa=='') $error="Debe indicar una empresa";
		if ($nombre=='') $error="Debe indicar un nombre";
		if ($idprovincia=='') $error="Debe indicar una provincia";
		if ($error!="") Mensaje($error);
		else {
			$sql="INSERT INTO `sucursales` (`IDEmpresa`,`IDSucursal`,`Nombre`,`Direccion`,`Poblacion`,`IDProvincia`,`CodigoPostal`,`Telefono`,`Fax`,`EMail`,`Responsable`,`ContadorFacturas`)
					VALUES ('$idempresa','','$nombre','$direccion','$poblacion','$idprovincia','$codpostal','$telefono','$fax','$email','$responsable','$contador');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar la nueva sucursal. Inténtelo de nuevo. ");
		}
		break;
		
	case 'Editar':
		$res=mysql_query("select * from sucursales where (".$parametros.") and (IDSucursal='$idsucursal')");
		$row=mysql_fetch_array($res);
		$idempresa=$row['IDEmpresa'];
		$nombre=$row['Nombre']; $direccion=$row['Direccion']; $poblacion=$row['Poblacion']; $idprovincia=$row['IDProvincia'];
        $codpostal=$row['CodigoPostal']; $telefono=$row['Telefono']; $fax=$row['Fax']; $email=$row['EMail'];
        $responsable=$row['Responsable']; $contador=$row['ContadorFacturas'];
		break;
}

function BorrarSucursal($empresa,$id){
	$ok=0;
	$m="";
	
	//buscar relaciones con otras tablas: presupuestos, albaranes...
	$sql="select count(IDAlbaran) from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".albaranes_cab where IDSucursal='$id'";
	$res=mysql_query($sql);
	$n=mysql_fetch_array($res);
	if ($n[0]>0) $m="No se puede borrar. Hay Albaranes de esa sucursal.";
	

	$sql="select count(IDPsto) from ".$_SESSION['DBDAT'].$_SESSION['empresa'].".psto_cab where IDSucursal='$id'";
    echo $sql;
	$res=mysql_query($sql);
    $n=mysql_fetch_array($res);
	if ($n[0]>0) $m="No se puede borrar: TIENE PRESUPUESTOS.";

	if($m==""){
	    $sql="DELETE FROM sucursales WHERE (".$empresa.") and (IDSucursal='$id') LIMIT 1;"; echo $sql;
       	$ok=mysql_query($sql);
	} else
	{
		Mensaje($m);
	}
	return($ok);
};

function Limpia(){
	global $idsucursal,$direccion,$nombre,$poblacion,$idprovincia,$codpostal,$telefono,$fax,$email,$responsable,$contador;
	$idsucursal=''; $direccion=''; $nombre=''; $poblacion=''; $idprovincia=''; $codpostal=''; $telefono=''; $fax=''; $email='';
	$responsable=''; $contador='';
};

function Listado(){
	global $pagina,$parametros;
	$gris="#CCCCCC";
	$tampagina=10;
    		
	$sql="select sucursales.*,provincias.NOMBRE as Provincia from sucursales,provincias where (".$parametros.") and (sucursales.IDProvincia=provincias.CODIGO) order by IDSucursal";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios"><tr><td align="center" class="blancoazul">SUCURSALES</td></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
	<tr><td colspan="9"><?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=sucursales&".$parametros."&pagina=","left",$gris,"");?></td></tr>
	<tr class="Formularios">
		<td></td>
	    <td><strong>Nombre</strong></td>
    	<td><strong>Direccion</strong></td>
    	<td><strong>Poblacion</strong></td>
	    <td><strong>Provincia</strong></td>
	    <td><strong>CP</strong></td>
	    <td><strong>Telefono</strong></td>
	    <td><strong>Fax</strong></td>
	    <td><strong>Responsable</strong></td>
  	</tr>
<?php	
	Subrrayado(9);

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
		    	<td width="16">
					<a href="contenido.php?c=sucursales&Accion=Editar&idsucursal=<?php echo $row['IDSucursal'];?>&<?php echo $parametros;?>&pagina=<?php echo $pagina;?>">
						<img src="images/botoneditar.png" border="0">
					</a>
				</td>
			    <td><?php echo $row['Nombre'];?></td>
			    <td><?php echo $row['Direccion'];?></td>				
			    <td><?php echo $row['Poblacion'];?></td>				
			    <td><?php echo $row['Provincia'];?></td>				
			    <td><?php echo $row['CodigoPostal'];?></td>				
			    <td><?php echo $row['Telefono'];?></td>
			    <td><?php echo $row['Fax'];?></td>
			    <td><?php echo $row['Responsable'];?></td>				
			</tr>
<?php
			Subrrayado(9);
		}
	}?>

	<tr><td colspan="9"><?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=sucursales&".$parametros."&pagina=","right",$gris,"");?></td></tr>
</table>		
<?php
}
?>



<?php
	Listado();
?>

<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Mantenimiento de Sucursales</td></tr>
<tr><td>
<table width="100%" align="center" class="ComboFamilias">
  <form name="formulario" action="contenido.php" method="post">
  	<input name="IDEmpresa" type="hidden" value="<?php echo $idempresa;?>">
  	<input name="idsucursal" type="hidden" value="<?php echo $idsucursal;?>">
  	<input name="c" type="hidden" value="sucursales">
  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
	<tr><TD>Nombre:<input name="Nombre" type="text" size="40" maxlength="50" value="<?php echo $nombre;?>" class="formularios">
            Direccion:<input name="Direccion" type="text" size="40" maxlength="50" value="<?php echo $direccion;?>" class="formularios">
	</td></tr>
	<tr><TD>Poblacion:<input name="Poblacion" type="text" size="15" maxlength="20" value="<?php echo $poblacion;?>" class="formularios">
			Provincia:<?php Desplegable('IDProvincia','provincias','CODIGO','NOMBRE','NOMBRE',$idprovincia,'','','');?>
			Cod.Postal:<input name="CodPostal" type="text" size="5" maxlength="5" value="<?php echo $codpostal;?>" class="formularios">
	</td></tr>
	<tr><TD>Teléfono:<input name="Telefono" type="text" size="15" maxlength="20" value="<?php echo $telefono;?>" class="formularios">
			Fax:<input name="Fax" type="text" size="15" maxlength="20" value="<?php echo $fax;?>" class="formularios">
			Email:<input name="EMail" type="text" size="35" maxlength="50" value="<?php echo $email;?>" class="formularios">
	</td></tr>
	<tr><TD>Responsable:<input name="Responsable" type="text" size="50" maxlength="50" value="<?php echo $responsable;?>" class="formularios">
            Contador Facturas:<input name="Contador" type="text" size="8" maxlength="8" value="<?php echo $contador;?>" class="formularios">	
	</td></tr>

	<tr><td colspan="2" align="center">
		<?php if ($idsucursal!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar la sucursal ",$nombre;?>');">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.Nombre.focus();
</script>
</table>
</td></tr>
</table>
