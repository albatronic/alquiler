<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/textos.php";
require "funciones/desplegable.php";
require "conecta.php";


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
$campos['idempresa']=$_POST['IDEmpresa'];
if ($campos['idempresa']=='') $campos['idempresa']=$_GET['IDEmpresa'];
$campos['razonsocial']=$_POST['RazonSocial'];
$campos['cif']=$_POST['Cif'];
$campos['direccion']=$_POST['Direccion'];
$campos['poblacion']=$_POST['Poblacion'];
$campos['idprovincia']=$_POST['IDProvincia'];
$campos['codigopostal']=$_POST['CodigoPostal'];
$campos['telefono']=$_POST['Telefono'];
$campos['fax']=$_POST['Fax'];
$campos['email']=$_POST['EMail'];
$campos['web']=$_POST['Web'];
$campos['idbanco']=$_POST['IDBanco'];
$campos['idoficina']=$_POST['IDOficina'];
$campos['digito']=$_POST['Digito'];
$campos['cuenta']=$_POST['Cuenta'];
$campos['factualizacion']=$_POST['FActualizacion'];
$campos['sufijo']=$_POST['Sufijo'];
$campos['iban']=$_POST['Iban'];
$campos['bic']=$_POST['Bic'];


//CONTROL DE LA ACCION A REALIZAR
//-------------------------------------------------------
switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$error='';
		$error=ValidaCC($campos['idbanco'],$campos['idoficina'],$campos['digito'],$campos['cuenta']);
		if (strlen($error)==2){$campos['digito']=$error;$error="";}
		if ($campos['cif']=='') $error="Debe indicar el CIF/NIF de la empresa.";
		if ($campos['razonsocial']=='') $error="Debe indicar la Razón Social de la empresa.";
		if ($campos['idprovincia']=='') $error="Debe indicar una provincia.";
		if ($error!='') Mensaje($error);
		else {
	        $sql="update empresas set RazonSocial='".CodificaTexto($campos['razonsocial']).
					"', Cif='".$campos['cif'].
					"', Direccion='".CodificaTexto($campos['direccion']).
					"', Poblacion='".$campos['poblacion'].
					"', IDProvincia='".$campos['idprovincia'].
					"', CodigoPostal='".$campos['codigopostal'].
					"', Telefono='".$campos['telefono'].
					"', Fax='".$campos['fax'].
					"', Web='".$campos['web'].
					"', EMail='".$campos['email'].
					"', IDBanco='".$campos['idbanco'].
					"', IDOficina='".$campos['idoficina'].
					"', Digito='".$campos['digito'].
					"', Cuenta='".$campos['cuenta'].                    					
					"', FActualizacion='".date("Y-m-d H:i:s").
					"', Sufijo='".$campos['sufijo'].
					"', Iban='".$campos['iban'].
					"', Bic='".$campos['bic'].
					"' where IDEmpresa='".$campos['idempresa']."'";
	        $res=mysql_query($sql);
			if ($res) Limpia();
            else Mensaje("No se han podido actualizar los datos. Inténtelo de nuevo");
		}
		break;

	case 'Borrar':
		if (BorrarEmpresa($campos['idempresa'])) Limpia();
		break;

	case 'Crear':
		$error='';
		$error=ValidaCC($campos['idbanco'],$campos['idoficina'],$campos['digito'],$campos['cuenta']);
		if (strlen($error)==2){$campos['digito']=$error;$error="";}
		if ($campos['cif']=='') $error="Debe indicar el CIF/NIF de la empresa.";
		if ($campos['razonsocial']=='') $error="Debe indicar la Razón Social.";
		if ($campos['idprovincia']=='') $error="Debe indicar una provincia.";
		if ($error!='') Mensaje($error);
		else {
			$valores="'','"
                        .CodificaTexto($campos['razonsocial'])."','"
                        .$campos['cif']."','"
                        .CodificaTexto($campos['direccion'])."','"
                        .$campos['poblacion']."','"
                        .$campos['idprovincia']."','"
                        .$campos['codigopostal']."','"
                        .$campos['telefono']."','"
                        .$campos['fax']."','"
                        .$campos['email']."','"
                        .$campos['web']."','"
                        .$campos['idbanco']."','"
                        .$campos['idoficina']."','"
                        .$campos['digito']."','"
                        .$campos['cuenta']."','"
                        .date("Y-m-d H:i:s")."','"
                        .$campos['sufijo']."','"
                        .$campos['iban']."','"
                        .$campos['bic']."'";

	    	$sql="INSERT INTO empresas VALUES (".$valores.")";
			$res=mysql_query($sql);
			if (!$res) Mensaje("No se ha podido crear. Inténtelo de nuevo.");
			else {
                Limpia();
                $res=mysql_query("select IDEmpresa,IDProvincia from empresas order by IDEmpresa DESC;");
                $row=mysql_fetch_array($res);
                //Crear una Sucursal
                $sql="INSERT INTO `sucursales` (`IDEmpresa`,`IDSucursal`,`Nombre`,`IDProvincia`) VALUES ('".$row[0]."','','Central','".$row[1]."');";
                $res=mysql_query($sql);
                if (!CrearBaseDatos($row[0])) Mensaje("OJO!!!! NO SE HA PODIDO CREAR LA BASE DE DATOS DE LA EMPRESA.");
            }
		}
		break;
		
	case 'Editar':
		$res=mysql_query("select * from empresas where (IDEmpresa='".$campos['idempresa']."')");
		$row=mysql_fetch_array($res);
		$campos['razonsocial']=DecodificaTexto($row['RazonSocial']);
		$campos['cif']=$row['Cif'];
		$campos['direccion']=DecodificaTexto($row['Direccion']);
		$campos['poblacion']=$row['Poblacion'];
		$campos['idprovincia']=$row['IDProvincia'];
		$campos['codigopostal']=$row['CodigoPostal'];
		$campos['telefono']=$row['Telefono'];
		$campos['fax']=$row['Fax'];
		$campos['web']=$row['Web'];
		$campos['email']=$row['EMail'];
		$campos['idbanco']=$row['IDBanco'];
		$campos['idoficina']=$row['IDOficina'];
		$campos['digito']=$row['Digito'];
		$campos['cuenta']=$row['Cuenta'];
		$campos['factualizacion']=$row['FActualizacion'];
		$campos['sufijo']=$row['Sufijo'];
		$campos['iban']=$row['Iban'];
		$campos['bic']=$row['Bic'];
		break;
}

function Limpia(){
	global $campos;
	$campos="";
};

function CrearBaseDatos($id){
    $ok=0;
    
    //creo la base de datos
    $nombredb=$_SESSION['DBDAT'].$id;
    $ok=mysql_query("CREATE DATABASE `".$nombredb."`;");
    if ($ok) $ok=mysql_query("USE `".$nombredb."`;");
    if ($ok){
        //creo las tablas
        $res=mysql_query("select * from ".$_SESSION['DBEMP'].".tablas;");
        while ($row=mysql_fetch_array($res) and $ok) {
            $ok=mysql_query($row['Sql']);
            if (!$ok) $mensaje="ERROR AL CREAR LA TABLA: ".$row['Tabla'];
        }
    }
    mysql_query("USE `".$_SESSION['DBEMP']."`;");
    if (!$ok) Mensaje($mensaje);
    return($ok);
};

function BorrarEmpresa($id){
	$ok=0;
	
	$ok=mysql_query("DROP DATABASE ".$_SESSION['DBDAT'].$id);
	if ($ok) $ok=mysql_query("delete from empresas where IDEmpresa=$id limit 1;");
	if ($ok) $ok=mysql_query("delete from sucursales where IDEmpresa=$id limit 1;");
	return($ok);
};

function Listado(){
	global $pagina,$filtro,$parametros;
	$gris="#CCCCCC";
	$tampagina=10;
	
	$sql="select empresas.*, provincias.NOMBRE as Provincia from empresas, provincias ".$filtro." order by RazonSocial";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" class="formularios">
	<tr><td colspan="7">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=empresas&".$parametros."&Accion=Consulta&pagina=","left",$gris,"");?>
	</td></tr>

	<tr class="Formularios">
    <td><strong>C&oacute;digo</strong></td>
    <td><strong>Raz&oacute;n Social</strong></td>
    <td><strong>Direcci&oacute;n</strong></td>
    <td><strong>Poblaci&oacute;n</strong></td>
    <td><strong>Provincia</strong></td>
    <td><strong>Tel&eacute;fono</strong></td>
    <td><strong>Fax</strong></td>
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
			<tr class='Formularios' id="linea<?php echo $i;?>"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
    			<td><a href="contenido.php?c=empresas&Accion=Editar&IDEmpresa=<?php echo $row['IDEmpresa'];?>&pagina=<?php echo $pagina,"&",$parametros;?>" title="Modificar/Borrar"><?php echo $row['IDEmpresa'];?></a></td>
		    	<td><?php echo DecodificaTexto($row['RazonSocial']);?></td>
		    	<td><?php echo DecodificaTexto($row['Direccion']);?></td>
	    		<td><?php echo $row['Poblacion'];?></td>
			    <td><?php echo $row['Provincia'];?></td>
		    	<td><?php echo $row['Telefono'];?></td>
			    <td><?php echo $row['Fax'];?></td>
			</tr>
<?php
			Subrrayado(7);
		}
	}?>

	<tr><td colspan="7">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=empresas&".$parametros."&Accion=Consulta&pagina=","right",$gris,"");?>
	</td></tr>
</table>
<?php }?>


<?php

$filtro="where (empresas.IDProvincia=provincias.CODIGO)";
Listado();
		
// PONER VALORES POR DEFECTO------------------------------------------

if ($campos['idprovincia']=="") $campos['idprovincia']="18";
//--------------------------------------------------------------------
?>
<table ID="MANTENIMIENTO" width="100%" border="0" class="formularios"  bgcolor="">
	<tr ID="TITULO">
		<td colspan="2" align="center" class="boxtitlewhite">Mantenimiento de Empresas</td>
	</tr>
	
	<form action="contenido.php" method="post" name="formulario">
		<input name="c" value="empresas" type="hidden">
	<TR ID="CUERPO">
		<TD WIDTH="100%" colspan="2">
			C&oacute;digo:<input name="IDEmpresa" type="text" size="3" value="<?php echo $campos['idempresa'];?>" class="formularios" readonly>
			Raz&oacute;n Social:<input name="RazonSocial" type="text" size="60" maxlength="50" value="<?php echo $campos['razonsocial'];?>" class="formularios">
			CIF:<input name="Cif" type="text" size="10" maxlength="10" value="<?php echo $campos['cif'];?>" class="formularios">
			<br>
			Direcci&oacute;n:<input name="Direccion" type="text" size="40" maxlength="50" value="<?php echo $campos['direccion'];?>" class="formularios">
			Poblaci&oacute;n:<input name="Poblacion" type="text" size="20" maxlength="30" value="<?php echo $campos['poblacion'];?>" class="formularios">
                        Provincia:<?php Desplegable('IDProvincia','provincias','CODIGO','NOMBRE','NOMBRE',$campos['idprovincia'],'','','');?>
        <br>
                        C&oacute;d.Postal:<input name="CodigoPostal" type="text" size="5" maxlength="5" value="<?php echo $campos['codigopostal'];?>" class="formularios">
                        Tel&eacute;fono:<input name="Telefono" type="text" size="10" maxlength="30" value="<?php echo $campos['telefono'];?>" class="formularios">
			Fax:<input name="Fax" type="text" size="10" maxlength="30" value="<?php echo $campos['fax'];?>" class="formularios">
			E-mail:<input name="EMail" type="text" size="50" maxlength="50" value="<?php echo $campos['email'];?>" class="formularios">
 			Web:<input name="Web" type="text" size="50" maxlength="50" value="<?php echo $campos['web'];?>" class="formularios"><br>
			Cuenta Remesas:<?php CuentaCorriente('formulario','IDBanco','IDOficina','Digito','Cuenta');?>&nbsp;
                        Sufijo Remesas:<input name="Sufijo" type="text" size="3" maxlength="3" value="<?php echo $campos['sufijo'];?>" class="formularios">
                        Iban:<input name="Iban" type="text" size="34" maxlength="34" value="<?php echo $campos['iban'];?>" class="formularios">
                        Bic:<input name="Bic" type="text" size="11" maxlength="11" value="<?php echo $campos['bic'];?>" class="formularios">
            (<?php echo $campos['factualizacion'];?>)
		</td>
	</tr>

	<?php
    Subrrayado(2);
    ?>
	<tr id="PIE">
		<td COLSPAN="2" align="right">
			<?php if ($campos['idempresa']!='') {?>
            <input type="button" class="formularios" value="Contadores" ACCESSKEY="C" onclick="window.open('contenido.php?c=contadores&idempresa=<? echo $campos['idempresa'];?>','Contadores','width=300,height=400,resizable=yes,scrollbars=yes');">&nbsp;&nbsp;
			<input name="Accion" type="submit" value="Guardar" ACCESSKEY="G" class="formularios">&nbsp;&nbsp;
        	<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('Realmente desea eliminar la empresa <?php echo $campos['razonsocial'];?> y todos sus datos?')">&nbsp;&nbsp;
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
</table>
