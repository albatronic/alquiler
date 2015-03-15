<?php
if ($_SESSION['iu']=='') exit;
require "conecta.php";
require "funciones/comprobar_email.php";
require "funciones/desplegable.php";

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
$login=$_POST['Login'];
$nombre=$_POST['Nombre'];
$password=$_POST['Password'];
$password1=$_POST['Password1'];
$email=$_POST['EMail'];
$activo=$_POST['Activo'];
$administrador=$_POST['Administrador'];
$idempresa=$_POST['IDEmpresa'];
$idsucursal=$_POST['IDSucursal'];

switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
        $m='';
		if ($password!=$password1) $m="Los dos password introducidos no son iguales.";
        if (($email=='') or (!comprobar_email($email))) {$m="El Email indicado es incorrecto"; $email='';}
        if ($idempresa=='') $m="Debe indicar en que empresa operará el agente.";
        if ($idsucursal=='') $m="Debe indicar en que sucursal operará el agente.";
        if ($m==''){
			$quien=md5($login.$password."Pablo"); $password=md5($password);
			if ($activo=='on') $activo='1'; else $activo='0';
			if ($administrador=='on') $administrador='1'; else $administrador='0';
			$sql="UPDATE `agentes` SET 
					`Login` = '$login',
					`Nombre` ='$nombre',
					`Password` = '$password',
					`Quien` = '$quien',
					`EMail` = '$email',
					`Activo` = '$activo',
					`Administrador` = '$administrador',
					`IDEmpresa` = '$idempresa',
					`IDSucursal` = '$idsucursal'
					WHERE `IDAgente` = '$id' LIMIT 1";
			$res=mysql_query($sql);
			if ($res) Limpia();
            else Mensaje("No se han guardado lo datos correctamente. Inténtelo otra vez.");
		}
		else Mensaje($m);
		break;

	case 'Borrar':
		$sql="DELETE FROM agentes WHERE IDAgente='$id' LIMIT 1;";
		$res=mysql_query($sql);
		if ($res) Limpia();
		else Mensaje("No se han podido eliminar. Inténtelo de nuevo");
		break;

	case 'Crear':
		if (($login!='') and ($password!='') and ($password1!='') and ($idempresa!='') and ($idsucursal!='')) {
			if ($password!=$password1){
				Mensaje("Los dos password introducidos no son iguales.");
			} else
			{	$res=mysql_query("select * from agentes where Login='$login'");
				$existe=mysql_num_rows($res);
				if ($existe) {
					Mensaje("El login indicado ya existe. Intente con otro");
				} else
				{	if ( ($email=='') or (!comprobar_email($email)) ) {Mensaje("El Email indicado es incorrecto"); $email='';}	
					$quien=md5($login.$password."Pablo"); $password=md5($password);	
					if ($activo=='on') $activo='1'; else $activo='0';
                    if ($administrador=='on') $administrador='1'; else $administrador='0';
					$sql="INSERT INTO `agentes` 
					VALUES ('', '$login', '$nombre', '$password', '$quien', '0', '', '$email', '$activo', '$administrador', '$idempresa', '$idsucursal');";
					$res=mysql_query($sql);
				}
			}			
		} else Mensaje("Debe indicar un login, el password, la empresa y la sucursal");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from agentes where IDAgente='$id'");
		$row=mysql_fetch_array($res);
		$id=$row['IDAgente']; $login=$row['Login']; $nombre=$row['Nombre']; $password=$row['Password']; $password1=$row['Password'];
		$email=$row['EMail']; $activo=$row['Activo']; $administrador=$row['Administrador']; $idempresa=$row['IDEmpresa']; $idsucursal=$row['IDSucursal'];
		break;
}

function Limpia(){
	global $id,$login,$password,$password1,$email,$activo,$administrador;
	$id='';$login='';$nombre='';$password='';$password1='';$email='';$activo='';$administrador=''; $idempresa=''; $idsucursal='';
};


function ListaAgentes(){
	$gris="#CCCCCC";
	
	$res=mysql_query("select * from agentes order by IDAgente");?>
	<table width="100%" align="center" bgcolor="<?php echo $gris;?>" class="formularios">
	<tr><td colspan="10" align="center" class="BlancoAzul">Listado de Agentes Registrados</td></tr>
	<tr>
		<td></td>
		<th>Login</th>
		<th>Nombre</th>
		<th>N.Log</th>
		<th width="125" nowrap>Ultimo Log</th>
		<th E-mail</strong></th>
		<th>Activo</strong></th>
		<th>Admtdor</strong></th>
		<th width="150" nowrap>Empresa</th>
		<th width="150" nowrap>Sucursal</th>
	</tr>
	<?php
		Subrrayado(10);
		$i=0;
		while ($row=mysql_fetch_array($res)){
		$i=$i+1;
        $res1=mysql_query("select RazonSocial from empresas where IDEmpresa=".$row['IDEmpresa']);
        $row1=mysql_fetch_array($res1);
        $res2=mysql_query("select Nombre from sucursales where (IDEmpresa=".$row['IDEmpresa'].") and (IDSucursal=".$row['IDSucursal'].")");
        $row2=mysql_fetch_array($res2);
        ?>
		<tr valign="top" class="Formularios" id="linea<?php echo $i;?>"
       				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      			onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
		>
			<td><a href="contenido.php?c=adm_agentes&id=<?php echo $row['IDAgente'];?>&Accion=Editar"><img src="images/botoneditar.png" border="0"></a></td>
			<td><?php echo $row['Login'];?></td>
			<td><?php echo $row['Nombre'];?></td>
			<td align="right" width="40"><?php echo $row['NLogin'];?></td>
			<td width="125" nowrap><?php echo $row['UltimoLogin'];?></td>
			<td><a href="mailto:<?php echo $row['EMail'];?>"><?php echo $row['EMail'];?></a></td>
			<td align="center"><input name="activo" type="checkbox" <?php if ($row['Activo']) echo "CHECKED";?> readonly></td>
			<td align="center"><input name="adm" type="checkbox" <?php if ($row['Administrador']) echo "CHECKED";?> readonly></td>			
			<td width="150" nowrap><?php echo $row1[0];?></td>
			<td width="150" nowrap><?php echo $row2[0];?></td>
		</tr>
	<?php
        Subrrayado(10);
         }?>
	</table>	

<?php }

ListaAgentes();?>

<script language="JavaScript" type="text/javascript">
function Recarga(){
	document.altaagente.Accion.value='Recarga';
	document.altaagente.submit();
}
</script>

<table width="90%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Alta de Agente</td></tr>
<tr><td>
<table width="100%" align="center" class="formularios">
  <form name="altaagente" action="contenido.php" method="post">
  	<input name="c" type="hidden" value="adm_agentes">
  	<input name="id" type="hidden" value="<?php echo $id;?>">
	<tr><TD align="right">Login:</TD><td><input name="Login" type="text" size="6" maxlength="6" value="<?php echo $login;?>" class="formularios">
			Nombre:<input name="Nombre" type="text" size="50" maxlength="50" value="<?php echo $nombre;?>" class="formularios"></td></tr>
	<tr><td align="right">Password:</td><td><input name="Password" type="password" size="6" maxlength="6" value="<?php echo $password;?>" class="formularios">
			Repite Password:<input name="Password1" type="password" size="6" maxlength="6" class="formularios"></td></tr>	
	<tr><td align="right">E-Mail:</td><td><input name="EMail" type="text" size="50" maxlength="50" value="<?php echo $email;?>" class="formularios"></td></tr>
	<tr><td align="right">Activo:</td><td><input name="Activo" type="checkbox" <?php if ($activo) echo "CHECKED";?> class="formularios">
			Administrador:<input name="Administrador" type="checkbox" <?php if ($administrador) echo "CHECKED";?> class="formularios"></td></tr>
	<tr><td align="right">Empresa:</td><td><?php Desplegable('IDEmpresa',$_SESSION['DBEMP'].'.empresas','IDEmpresa','RazonSocial','RazonSocial',$idempresa,'onchange="Recarga();"','','');?>
			Sucursal: <?php DesplegableSucursal('IDSucursal',$idempresa,$idsucursal,'');?>
		</td>
	</tr>

	<tr><td colspan="2" align="center">
		<?php if ($id!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar el agente ",$nombre;?>');">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.altaagente.Login.focus();
</script>
</table>
</td></tr>
</table>
