<?php
if ($_SESSION['iu']=='') exit;

require "funciones/desplegable.php";
require "funciones/textos.php";
require "funciones/fechas.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;

$id=$_GET['id'];
$accion=$_GET['accion'];
if (!isset($id)) $id=$_POST['id'];
if ($accion=='') $accion=$_POST['accion'];

switch ($accion) {

    case 'Nuevo':
        $id=''; $sucursal=''; $descripcion=''; $vigencia=''; $emergente='0';
    	break;

    case 'E':
        $res=mysql_query("select * from noticias where IDNoticia=$id");
        if ($row=mysql_fetch_array($res)) {
            $sucursal=$row['IDSucursal']; $descripcion=DecodificaTexto($row['Noticia']); $vigencia=$row['Vigencia']; $emergente=$row['Emergente'];
        }
	    break;

    case 'B':
        $res=mysql_query("delete from `noticias` where `IDNoticia`='$id' limit 1");
    	break;

    case 'Guardar':
		$sucursal=$_POST['sucursal'];
        $descripcion=CodificaTexto($_POST['descripcion']);
        $vigencia=$_POST['vigencia'];
		$emergente=$_POST['emergente'];
		if ($emergente=='on') $emergente='1'; else $emergente='0';
		
		//Validaciones
        $mensaje="";
        if ($descripcion=='') $mensaje="La descripci�n no puede estar vacia.";
        if ($vigencia=='') $mensaje=$mensaje." Debe indicar la fecha de fin de la noticia.";

        if ($mensaje=='') {
            if ($id!='') { //Modificaci�n
                $sql="UPDATE `noticias` SET `IDSucursal`='$sucursal', `Noticia`='$descripcion', `Vigencia`='$vigencia', `Emergente`='$emergente' where (IDNoticia=$id) limit 1";
            } else { //Alta
                $sql="INSERT INTO `noticias` (`IDNoticia`,`IDSucursal`,`Noticia`,`Vigencia`, `Emergente`)
                       VALUES ('','$sucursal','$descripcion','$vigencia','$emergente')";
            }
            $res=mysql_query($sql);
            $id='';$sucursal='';$descripcion='';$vigencia='';
        } else Mensaje($mensaje);
    	break;
		
	case 'Borrar':
		//Borrar todas las noticias caducadas
		$sql="delete from noticias where Vigencia<'".date('Y-m-d')."'";
		$res=mysql_query($sql);
		break;
}//ACCION


function Listado(){
	global $pagina,$parametros;
	$gris="#CCCCCC";
	$tampagina=DameParametro('LOPAP',10);
    		
	$sql="select * from noticias order by IDSucursal,Vigencia";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
	?>
	<table width="100%" class="formularios" align="center" bgcolor="<?php echo $gris;?>">
	<tr><td colspan="6" align="center" class="BlancoAzul">Listado de Noticias</td></tr>
    <tr><td colspan="6">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=adm_noticias&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="formularios">
		<td></td>
		<td></td>
		<td><strong>Sucursal</strong></td>
		<td><strong>Noticia</strong></td>
		<td><strong>Vigencia</strong></td>
		<td align="center"><strong>Emergente</strong></td>
	</tr>
	<?php
		Subrrayado(6);
		$res=mysql_query($sql);

		$ok=@mysql_data_seek($res,$desderegistro);
		if ($ok) {
			$i=1;
			while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
				$i=$i+1;
				$res1=mysql_query("select Nombre from ".$_SESSION['DBEMP'].".sucursales where (IDEmpresa=".$_SESSION['empresa'].") and (IDSucursal=".$row['IDSucursal'].")");
				$row1=mysql_fetch_array($res1);
		?>
				<tr valign="top" class="Formularios" id="linea<?php echo $i;?>"
       				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      			onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
				>
					<td><a href="contenido.php?c=adm_noticias&id=<?php echo $row['IDNoticia'];?>&accion=E&pagina=<?php echo $pagina;?>" title="Modificar"><img src="images/botoneditar.png" border=0></a></td>
					<td><a href="contenido.php?c=adm_noticias&id=<?php echo $row['IDNoticia'];?>&accion=B" title="Eliminar"><img src="images/botonborrar.png" border=0></a></td>			
					<td><?php if ($row1['Nombre']=='') echo "<b>TODAS</b>"; else echo $row1['Nombre'];?></td>
					<td><?php echo DecodificaTexto($row['Noticia']);?></td>
					<td><?php echo FechaEspaniol($row['Vigencia']);?></td>
					<td align="center"><input name="emergente" type="checkbox" <?php if ($row['Emergente']=='1') echo "CHECKED";?> readonly></td>
				</tr>
	<?php
				Subrrayado(6);
			}
		}?>
	<tr><td colspan="6">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=adm_noticias&pagina=","right",$gris,"");?>
	</td></tr>
</table>	
<?php }

Listado();
?>
<table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
<tr><td align="center" class="BlancoAzul">Alta/Edici�n de Noticia <?php echo $id;?></td></tr>
<tr><td>
<table width="100%" align="center" class="formularios">
	<form name="altanoticia" action="contenido.php" method="post">
	<tr valign="top">
		<TD valign="top">
			Para la Sucursal (nada para todas):<?php DesplegableSucursal('sucursal',$_SESSION['empresa'],$sucursal,'');?>
            Vigencia:<input name="vigencia" type="text" size="10" maxlength="10" value="<?php echo $vigencia;?>" class="formularios"> (aaaa-mm-dd)
			 Mostrar en Ventana Emergente:<input name="emergente" type="checkbox" <?php if ($emergente=='1') echo "checked";?> class="formularios">
			<br><br>
            Descripci�n de la noticia:<br><textarea name="descripcion" cols="100" rows="5" class="formularios"><?php echo $descripcion;?></textarea>

        </td>
	</tr>
	<tr>
        <td align="center">
		  	<input name="c" type="hidden" value="adm_noticias">
		  	<input name="id" type="hidden" value="<?php echo $id;?>">
		  	<input name="pagina" type="hidden" value="<?php echo $pagina;?>">
            <input name="accion" type="submit" value="Guardar" class="formularios">
            <input name="accion" type="submit" value="Nuevo" class="formularios">
        </td>
    </tr>
	</form>
	<tr>
		<td align="center">
		<form name="borrarcaducadas"  action="contenido.php" method="post">
		  	<input name="c" type="hidden" value="adm_noticias">
			<input name="accion" value="Borrar" type="hidden">
			<input name="" type="button" value="Borrar Noticias Caducadas" class="formularios" onclick="if (confirm('�Desea eliminar todas los noticias caducadas?')) document.borrarcaducadas.submit();">
		</form>
		</td>			
	</tr>
<script language="JavaScript" type="text/javascript">
document.altanoticia.sucursal.focus();
</script>
</table>
</td></tr>
</table>
