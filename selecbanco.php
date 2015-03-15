<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "conecta.php";
include "modulos.php";

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=1;


$form=$_GET['form'];
$campobanco=$_GET['campobanco'];
$parametros="form=$form&campobanco=$campobanco";

$res=mysql_query("select Banco from bancos where IDBanco='$idb';");
$banco=mysql_fetch_array($res);
?>

<script language="JavaScript" type="text/javascript">
function CogeBanco(banco,form,campobanco){
	window.opener[form][campobanco].value=banco;
    window.close();
	window.opener[form][compobanco].focus();

}
</script>

<?php

function Listado(){
	global $pagina,$form,$campobanco,$parametros;
	
    $gris="#CCCCCC";
	$tampagina=24;
    		
	$sql="select * from bancos order by IDBanco";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><th colspan="2" class="BlancoAzul">Entidades Bancarias</th></tr>
	<tr><td colspan="2">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"selecbanco.php?$parametros&pagina=","left",$gris,'');?>
	</td></tr>
	<tr class="Formularios">
    <th>Codigo</th>
    <th>Nombre Entidad</th>
  	</tr>
<?php	
	Subrrayado(2);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>"
    				onClick="<?php echo "CogeBanco('",$row['IDBanco'],"','",$form,"','",$campobanco,"')";?>"
	       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
		      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
                <td><? echo $row['IDBanco'];?></td>
                <td><? echo $row['Banco'];?></td>
			</tr>
<?php
			Subrrayado(2);
		}
	}?>

	<tr><td colspan="2">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"selecbanco.php?$parametros&pagina=","right",$gris,'');?>
	</td></tr>
</table>		
<?php
}
?>


<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<link href="estilos.css" rel="stylesheet" type="text/css">
<title>:: Entidades Bancarias</title>
</head>
<body>

<?php
	Listado($filtro,$orden);
?>

</body>
</html>
