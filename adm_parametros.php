<?php
if ($_SESSION['iu']=='') exit;
require "conecta.php";
require "funciones/textos.php";

$accion=$_POST['Accion'];
$id=$_POST['Id'];
if ($id=='') $id=$_GET['Id'];
$descripcion=$_POST['Descripcion'];
$valor=$_POST['Valor'];

switch ($accion) {
	case 'Limpiar':
		$id='';$descripcion='';$valor='';
		break;
		
	case 'Guardar':
		$sql = "UPDATE `parametros` SET `Descripcion` = '$descripcion', `Valor` = '".CodificaTexto($valor)."' WHERE `IDParametro` = '$id' LIMIT 1;";
		$res=mysql_query($sql);
		if ($res) Limpia();
		else Mensaje("No se han podido actualizar los datos. Inténtelo de nuevo");
		break;

	case 'Borrar':
		$sql="DELETE FROM parametros WHERE IDParametro='$id' LIMIT 1;";
		$res=mysql_query($sql);
		if ($res) Limpia();
		else Mensaje("No se han podido eliminar. Inténtelo de nuevo");
		break;

	case 'Crear':
		if (($id!='') and ($descripcion!='')) {
			$res=mysql_query("select * from parametros where IdParametro='$id'");
			$existe=mysql_num_rows($res);
			if ($existe) {
				Mensaje("El Id indicado ya existe. Intente con otro");
				$id="";
			} else
			{	$sql="INSERT INTO `parametros` ( `IDParametro` , `Descripcion` , `Valor`) VALUES ('$id', '$descripcion', '".CodificaTexto($valor)."');";
				$res=mysql_query($sql);
				if ($res) Limpia();
				else Mensaje("No se ha podido crear. Inténtelo de nuevo.");
			}
		} else Mensaje("Debe indicar un Código y una Descripción");
		break;
		
	default:
		$res=mysql_query("select * from parametros where IDParametro='$id'");
		$row=mysql_fetch_array($res);
		$id=$row[0]; $descripcion=$row[1]; $valor=DecodificaTexto($row[2]);
		break;
}

function Limpia(){
	global $id,$descripcion,$valor;
	
	$id='';$descripcion='';$valor='';
}

function Listado(){
	$res=mysql_query("select * from parametros order by IDParametro");?>
	<table width="100%" class="formularios" align="center" bgcolor="#99CCF0">
	<tr><td colspan="3" align="center" class="BlancoAzul">Listado de Parametros</td></tr>
	<tr><td>ID</td><td>Descripción</td><td>Valor</td></tr>
	<?php
		Subrrayado(3);
		while ($row=mysql_fetch_array($res)){?>
		<tr>
			<td><a href="contenido.php?c=adm_parametros&Id=<?php echo $row['IDParametro'];?>" title="Modificar/Borrar"><?php echo $row['IDParametro'];?></a></td>
			<td><?php echo $row[1];?></td>
			<td><textarea cols="40" rows="3" readonly class="formularios"><?php echo $row[2];?></textarea></td>
		</tr>
	<?php }?>
	</table>	

<?php }
?>

<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td colspan="2" align="center" class="BlancoAzul">Mantenimiento de Parámetros</td></tr>
<tr>
    <td><?Listado();?></td>
    <td valign="top">
    <table width="100%" align="center" class="formularios">
	<form name="formularioalta" action="contenido.php" method="post">
		<input name="c" value="adm_parametros" type="hidden">
		<tr><TD>Id:</TD><td><input name="Id" type="text" size="5" maxlength="5" value="<?php echo $id;?>">
              (No se puede modificar el c&oacute;digo una vez creado)</td>
          </tr>
		<tr><TD>Descripción:</TD><td><input name="Descripcion" type="text" size="45" maxlength="50" value="<?php echo $descripcion;?>"></td></tr>
		<tr><td>Valor:</td><td><textarea name="Valor" cols="45" rows="10" class="formularios"><?php echo $valor;?></textarea></td></tr>
		<tr><td colspan="2" align="center">
			<?php if ($id!='') {?>
			<input name="Accion" type="submit" value="Guardar" class="formularios">
			<input name="Accion" type="submit" value="Borrar" class="formularios">				
			<?php } else {?>
			<input name="Accion" type="submit" value="Crear" class="formularios">
			<?php }?>
			<input name="Accion" type="submit" value="Limpiar" class="formularios">
		</td></tr>
	</form>
	<script language="JavaScript" type="text/javascript">
	document.formularioalta.Id.focus();
	</script>
    </table>
    </td>
</tr>
</table>
