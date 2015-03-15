<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "engancha.php";
include "modulos.php";
include "funciones/formatos.php";
include "funciones/desplegable.php";
include "funciones/textos.php";


$pagina=$_POST['pagina'];
if ($pagina=='') $pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=1;

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];
$id=$_POST['id'];
if (!isset($id)) $id=$_GET['id'];
if ($id=="") {Mensaje("Se ha perdido la vinculacion con el inmueble."); exit;}

$idconcepto=$_POST['IDConcepto'];
if (!isset($idconcepto)) $idconcepto=$_GET['IDConcepto'];

$importe=$_POST['Importe'];
if ($importe=='') $importe=$_GET['Importe'];


switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'Guardar':
		$sql="UPDATE inmuebles_conceptos SET IDConcepto='$idconcepto', Importe='$importe' WHERE ((IDInmueble='$id') and (IDConcepto='$idconcepto'))";
		$res=mysql_query($sql);
		if ($res) Limpia();
        else Mensaje ("No se han guardado los datos correctamente. Intentelo de nuevo.");
        break;

	case 'Borrar':
 		$sql="DELETE FROM inmuebles_conceptos WHERE ((IDInmueble='$id') and (IDConcepto='$idconcepto')) LIMIT 1;";
       	$res=mysql_query($sql);
   		if ($res) Limpia();
       	else Mensaje("No se ha podido eliminar. Intentelo de nuevo");
		break;

	case 'Crear':
		if ($idconcepto!='') {
			$sql="INSERT INTO inmuebles_conceptos (IDInmueble,IDConcepto,Importe) VALUES ('$id','$idconcepto','$importe');";
            $res=mysql_query($sql);
			if ($res) Limpia();
			else Mensaje("No se ha podido insertar el concepto. Intentelo de nuevo.");
		} else Mensaje("Debe indicar un Concepto.");
		break;
		
	case 'Editar':
		$res=mysql_query("select * from inmuebles_conceptos where ((IDInmueble='$id') and (IDConcepto='$idconcepto'));");
		$row=mysql_fetch_array($res);
		$importe=$row['Importe'];
		break;

    case 'Quitar Todos':
 		$sql="DELETE FROM inmuebles_conceptos WHERE (IDInmueble='$id');";
       	$res=mysql_query($sql);
   		if ($res) Limpia();
       	else Mensaje("No se han podido eliminar. Intentelo de nuevo");
        break;
}

$parametros="id=$id";
$filtro="(inmuebles_conceptos.IDInmueble='$id') AND (".$_SESSION['DBEMP'].".conceptos.IDConcepto=inmuebles_conceptos.IDConcepto)";

function Limpia(){
	global $idconcepto,$importe;
	$idconcepto='';$importe='';
};

function Listado(){
    global $pagina,$parametros,$id,$filtro;
	
    $gris="#CCCCCC";
    $tampagina=20;

    $sql="SELECT inmuebles_conceptos.*,".
        $_SESSION['DBEMP'].".conceptos.Concepto,".
        $_SESSION['DBEMP'].".conceptos.Precio,".
        $_SESSION['DBEMP'].".conceptos.Consumo
        FROM inmuebles_conceptos,".$_SESSION['DBEMP'].".conceptos
        WHERE ".$filtro."
        order by inmuebles_conceptos.IDConcepto;";
    list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table width="100%" align="center" class="formularios" border="1"><tr><th class="blancoazul">CONCEPTOS FACTURABLES ASIGNADOS</th></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"frameinmuconce.php?".$parametros."&pagina=","left",$gris,'');?>
	</td></tr>
	<tr class="Formularios">
        <th>Cod</th>
        <th>Concepto</th>
        <th>Importe</th>
  	</tr>
<?php	
	Subrrayado(3);
	$claro="#C0C0C0";
	$oscuro="#808080";

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
            $i=1;
            while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
                $i=$i+1;
                if ($row['Consumo']=="S") $color=$claro; else $color=$oscuro;?>
                <tr class="Formularios" id="linea<?php echo $i;?>" bgcolor="<?php echo $color;?>"
                onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
                onmouseout="<?php echo "cambiacolor('linea",$i,"','",$color,"');";?>"
                >
                    <td><a href="frameinmuconce.php?Accion=Editar&id=<?php echo $id;?>&IDConcepto=<?php echo $row['IDConcepto'];?>&pagina=<?php echo $pagina;?>"><?php echo $row['IDConcepto'];?></a></td>
                    <td><?php echo $row['Concepto'];?></td>
                    <td ALIGN="right"><?php echo $row['Importe'];?></td>
                </tr>
                </form>
<?
		}
	}?>

	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"frameinmuconce.php?".$parametros."&pagina=","right",$gris,'');?>
	</td></tr>
</table>		
<?php
}
?>


<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<link href="estilos.css" rel="stylesheet" type="text/css">
<title>:: Conceptos del Inmueble</title>
</head>
<body>

<table widht="100%">
<tr>
    <td width="100%">
    <?Listado($filtro,$orden);?>
    </td>
</tr>

<tr>
    <td width="100%">

    <table width="100%" border="1" align="center" bordercolor="#000099" class="formularios">
    <tr><td align="center" class="BlancoAzul">Mantenimiento de Conceptos</td></tr>
    <tr><td>
    <table width="100%" align="center" class="ComboFamilias">
    <form name="formulario" action="frameinmuconce.php" method="post">
        <input name="id" type="hidden" value="<?php echo $id;?>">
        <input name="pagina" type="hidden" value="<?php echo $pagina;?>">
        <tr><td>Concepto:</td><td><?php Desplegable("IDConcepto",$_SESSION['DBEMP'].".conceptos","IDConcepto","Concepto","IDConcepto",$idconcepto,"","formularios","");?></td></tr>
        <tr><TD>Importe:</TD><td><input name="Importe" type="text" size="10" maxlength="9" value="<?php echo $importe;?>" class="formularios"></td></tr>

        <tr><td colspan="2" align="center">
            <?php if ($idconcepto!='') {?>
            <input name="Accion" type="submit" value="Guardar" class="formularios">
            <input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea ELIMINAR el concepto ",$idconcepto;?>');">
            <?php } else {?>
            <input name="Accion" type="submit" value="Crear" class="formularios">
            <?php }?>
            <input name="Accion" type="submit" value="Limpiar" class="formularios">
            <input name="Accion" type="submit" value="Quitar Todos" class="formularios"  onclick="return Confirma('<?php echo "REALMENTE DESEA ELIMINAR TODOS LOS CONCEPTOS DE ESTE INMUEBLE??";?>');">
        </td></tr>
    </form>
    <script language="JavaScript" type="text/javascript">
    document.formulario.IDConcepto.focus();
    </script>
    </table>
    </td></tr>
    </table>

    </td>
</tr>
</table>

</body>
</html>
