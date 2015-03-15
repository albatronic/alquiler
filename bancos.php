<script language="JavaScript" type="text/javascript">
function CogeBanco(banco,form,campobanco){
	window.opener[form][campobanco].value=banco;
    window.close();
	window.opener[form][compobanco].focus();
}
</script>

<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;

$form=$_GET['form']; if($form=='') $form=$_POST['form'];
$campobanco=$_GET['campobanco']; if($campobanco=='') $campobanco=$_POST['campobanco'];
$parametros="form=$form&campobanco=$campobanco";

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
$banco=$_POST['Banco'];


switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE bancos SET Banco='$banco' WHERE IDBanco='$id'";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Inténtelo de nuevo.");
        break;

	case 'Borrar':
        $mensaje="";
        $res=mysql_query("select count(IDBanco) from bancos_oficinas where IDBanco='$id';");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." oficinas ese banco.";

        $res=mysql_query("select count(IDBanco) from inquilinos where IDBanco=$id");
        $n=mysql_fetch_array($res);
        if ($n[0]>0) $mensaje="No se puede borrar porque hay ".$n[0]." inquilinos con ese banco.";
        if ($mensaje!='') Mensaje($mensaje);
        else {
    		$sql="DELETE FROM bancos WHERE IDBanco='$id' LIMIT 1;";
	       	$res=mysql_query($sql);
    		if ($res) Limpia();
	       	else Mensaje("No se ha podido eliminar. Inténtelo de nuevo");
        }
		break;

	case 'Crear':
		if (($id!='') and ($banco!='')) {
			$sql="INSERT INTO bancos (`IDBanco`,`Banco`) VALUES ('$id','$banco');";
			$res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar el nuevo banco. Inténtelo de nuevo.");
		} else Mensaje("Debe indicar una código y una descripción del banco.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from bancos where IDBanco='$id'");
		$row=mysql_fetch_array($res);
		$id=$row['IDBanco']; $banco=$row['Banco'];
		break;
}

function Limpia(){
	global $id,$banco;
	$id='';$banco='';
};


function Listado(){
	global $pagina,$form,$campobanco,$parametros;

	$gris="#CCCCCC";
	$tampagina=18;
    		
	$sql="select * from bancos order by IDBanco";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios"><tr><td align="center" class="blancoazul">ENTIDADES BANCARIAS</td></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=bancos&".$parametros."&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
        <th width="16"></th>
        <th>Codigo</th>
        <th>Banco</th>
  	</tr>
<?php	
	Subrrayado(3);

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
		    	<td width="16"><a href="contenido.php?c=bancos&Accion=Editar&id=<?echo $row['IDBanco'];?>&pagina=<?echo $pagina;?>&<?echo $parametros;?>"><img src="images/botoneditar.png" border="0" alt="<?echo $row['IDBanco'];?>"></a></td>
                <td onClick="<?echo "CogeBanco('",$row['IDBanco'],"','",$form,"','",$campobanco,"')";?>"><?echo $row['IDBanco'];?></td>
                <td><?echo $row['Banco'];?></td>
			</tr>
<?php
		}
	}?>

	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=bancos&".$parametros."&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?php
}

function Formulario(){
global $id,$banco,$pagina,$form,$campobanco;

?>
<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Mantenimiento de Entidades Bancarias</td></tr>
<tr><td>
<table width="100%" align="center" class="ComboFamilias">
  <form name="formulario" action="contenido.php" method="post">
  	<input name="c" type="hidden" value="bancos">
  	<input name="pagina" type="hidden" value="<?echo $pagina;?>">
  	<input name="form" type="hidden" value="<?echo $form;?>">
  	<input name="campobanco" type="hidden" value="<?echo $campobanco;?>">
	<tr>
        <TD>Codigo:<input name="id" type="text" size="4" maxlength="4" value="<?echo $id;?>" class="formularios" <? if ($id!='') echo "READONLY";?>></td>
        <TD>Banco:<input name="Banco" type="text" size="40" maxlength="50" value="<?echo $banco;?>" class="formularios"></td>
    </tr>

	<tr><td colspan="2" align="center">
		<?php if ($id!='') {?>
		<input name="Accion" type="submit" value="Guardar" class="formularios">
		<input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?echo "Desea eliminar el banco ",$banco;?>');">				
		<?php } else {?>
		<input name="Accion" type="submit" value="Crear" class="formularios">
		<?php }?>
		<input name="Accion" type="submit" value="Limpiar" class="formularios">
	</td></tr>
</form>
<script language="JavaScript" type="text/javascript">
document.formulario.id.focus();
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
