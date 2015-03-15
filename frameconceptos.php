<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "conecta.php";
include "modulos.php";
include "funciones/textos.php";

$id=$_GET['id'];
?>

<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <title>Conceptos</title>
</head>
<body>

<table width="100%" align="center" class="formularios" border="1"><tr><th class="blancoazul">CONCEPTOS FACTURABLES DISPONIBLES</th></tr></table>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" class="formularios">
	<tr class="Formularios">
        <th width="16"></th>
        <th>Cód</th>
        <th>Concepto</th>
        <th>Consumo?</th>
        <th>Iva?</th>
        <th>Precio Std</th>
        <th>Sube Auto?</th>
  	</tr>
<?php	
	Subrrayado(7);

	$sql="select * from conceptos order by Consumo DESC,IDConcepto ASC";
    $res=mysql_query($sql);
	$claro="#C0C0C0";
	$oscuro="#808080";
	while ($row=mysql_fetch_array($res)) {
		$i=$i+1;
		if ($row['Consumo']=="S") $color=$claro; else $color=$oscuro;
	?>
		<tr class="Formularios" id="li<?php echo $i;?>" bgcolor="<?php echo $color;?>"
       		onmouseover="<?php echo "cambiacolor('li",$i,"','#FFFF00');";?>"
	      	onmouseout="<?php echo "cambiacolor('li",$i,"','",$color,"');";?>"
        >
		    <td width="16"><a href="frameinmuconce.php?Accion=Crear&id=<?php echo $id;?>&IDConcepto=<?php echo $row['IDConcepto'];?>&Importe=<?php echo $row['Precio'];?>" target="izquierda"><img src="images/insertar.png" border="0" alt="Asignar al Inmueble"></a></td>
            <td><?php echo $row['IDConcepto'];?></td>
            <td><?php echo DecodificaTexto($row['Concepto']);?></td>
            <td align="center"><?php echo $row['Consumo'];?></td>
            <td align="center"><?php echo $row['Iva'];?></td>
            <td align="right"><?php echo $row['Precio'];?></td>
            <td align="center"><?php echo $row['SubeAutomatico'];?></td>
        </tr>
<?php
    }
?>
</table>

</body>
</html>
