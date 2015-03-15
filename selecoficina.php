<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "conecta.php";
include "modulos.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=1;

$idb=$_GET['idb']; if ($idb=='') exit;
$form=$_GET['form'];
$campooficina=$_GET['campooficina'];
$parametros="idb=$idb&form=$form&campooficina=$campooficina";

$res=mysql_query("select Banco from bancos where IDBanco='$idb';");
$banco=mysql_fetch_array($res);
?>

<script language="JavaScript" type="text/javascript">
function CogeOficina(oficina,form,campooficina){
	window.opener[form][campooficina].value=oficina;
    window.close();
	window.opener[form][compooficina].focus();

}
</script>

<?php

function Listado(){
	global $pagina,$idb,$form,$campooficina,$banco,$parametros;
	
    $gris="#CCCCCC";
	$tampagina=28;
    		
	$sql="select * from bancos_oficinas where IDBanco='$idb' order by IDOficina";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><th colspan="3" class="BlancoAzul">Oficinas de <?echo $idb," ",$banco[0];?></th></tr>
	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"selecoficina.php?$parametros&pagina=","left",$gris,'');?>
	</td></tr>
	<tr class="Formularios">
    <th>Oficina</th>
    <th>Direccion</th>
    <th>Poblacion</th>
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
    				onClick="<?php echo "CogeOficina('",$row['IDOficina'],"','",$form,"','",$campooficina,"')";?>"
	       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
		      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
                <td><? echo $row['IDOficina'];?></td>
                <td><? echo $row['Direccion'];?></td>
                <td><? echo $row['Poblacion'];?></td>
			</tr>
<?php
		}
	}?>

	<tr><td colspan="3">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"selecoficina.php?$parametros&pagina=","right",$gris,'');?>
	</td></tr>
</table>		
<?php
}
?>


<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<link href="estilos.css" rel="stylesheet" type="text/css">
<title>:: Oficinas de Bancarias</title>
</head>
<body>

<?php
	Listado($filtro,$orden);
?>

</body>
</html>
