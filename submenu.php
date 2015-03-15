<?php
session_start();
if ($_SESSION['iu']=='') exit;
$esadm=$_SESSION['esadm'];
//
//RECIBE COMO PARÁMETRO EL ID DE OPCION DE MENU PARA QUE MUESTRE LAS OPCIONES DEL SUBMENU.
//
$agente=$_SESSION['iu'];
$id=$_GET['id'];
$t=$_GET['t'];
require "conecta.php";
require "modulos.php";
?>

<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
    <title>Submenu</title>
    <link href="estilos.css" rel="stylesheet" type="text/css">

<SCRIPT LANGUAGE="JavaScript">
<!--
function Lanza(script){
	top.contenido.location='contenido.php?c='+script;
}
// -->
</SCRIPT>
</head>
<body class="degradadoazul" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" align="left" class="menu">
  <tr><td class="boxtitlewhite" colspan="2" align="center"><?php echo $t;?></td></tr>
		<?php
		if ($id!=''){
		    if($esadm) $filtro="1=1"; else $filtro="Administrador='0'";
    		$res=mysql_query("select * from submenu where ((IDOpcion=$id) and ($filtro)) order by Orden");
    		$i=0;
	       	while ($row=mysql_fetch_array($res)){
                $i=$i+1;?>
    			<tr class='combofamilias' id="linea<?php echo $i;?>"
    				onmouseover="<?php echo "cambiacolor('linea",$i,"','",$AMARILLO,"');";?>"
	       			onmouseout= "<?php echo "cambiacolor('linea",$i,"','",$FONDOMENU,"');";?>"
    			>
                    <td width="14">
                        <img src="images/bola.gif" onclick="window.open('contenido.php?c=<?echo $row['Script'],"&t=",$row['Titulo'];?>','<?echo $row['Script'];?>','scrollbars=yes,resizable=yes,menubar=yes')">
                    </td>
                    <td align="left">
                        <?php if ($row['Emergente']=="1") {?><a href="javascript:;" onclick="window.open('contenido.php?c=<?php echo $row['Script'],"&t=",$row['Titulo'];?>','<?echo $row['Script'];?>','scrollbars=yes,resizable=yes,menubar=yes')"><?php echo $row['Titulo'];?></a><?php }?>
                        <?php if ($row['Emergente']=="0") {?><a href="javascript:;" onclick="Lanza('<?php echo $row['Script'];?>');"><?php echo $row['Titulo'];?></a><?php }?>
                    </td>
                </tr>
		<?php
            }
        }?>
</table>
</body>
</html>
