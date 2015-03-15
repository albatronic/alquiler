<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

include "engancha.php";
include "modulos.php";
?>

<script language="JavaScript" type="text/javascript">
function CogeInmueble(id,nombre,formulario,campoclave,campotexto){
	window.opener[formulario][campoclave].value=id;
	window.opener[formulario][campotexto].value=nombre;
	window.close();
}
</script>

<?php

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=1;

$orden=$_GET['orden'];
if ($orden=='') $orden="IDInmueble";

$abc=$_GET['abc'];
$texto=$_GET['texto'];
$texto=$texto."%";
if ($texto=='') $cadena=$abc."%"; else $cadena=str_replace('?','%',$texto); 

$form=$_GET['form'];
$campoclave=$_GET['campoclave'];
$campotexto=$_GET['campotexto'];

$filtro="where (((Direccion like '$cadena') or (Poblacion like '$cadena') or (IDInmueble like '$cadena')))";
$parametros="orden=$orden&abc=$abc&texto=$texto&form=$form&campoclave=$campoclave&campotexto=$campotexto";

function Listado($filtro,$orden){
	$tampagina=25;
	global $pagina,$parametros,$form,$campoclave,$campotexto;
	$gris="#CCCCCC";
		
	$sql="select * from inmuebles ".$filtro." order by ".$orden;
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>
<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
	<tr><td colspan="4">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"selecinmueble.php?".$parametros."&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
    <th>Codigo</th>
    <th>Direccion</th>
    <th>Poblacion</th>
  	</tr>
<?php	
	Subrrayado(4);

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>"
				onClick="<?php echo "CogeInmueble('",$row['IDInmueble'],"','",$row['Direccion'],"','",$form,"','",$campoclave,"','",$campotexto,"');";?>"
				onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
				onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
			>
		    	<td><?php echo $row['IDInmueble'];?></td>
			    <td><?php echo $row['Direccion'];?></td>
			    <td><?php echo $row['Poblacion'];?></td>
                <td>
                    <? if ($row['IDInquilino']=='') {?>
                            <img src="images/libre.png" height="13" border="0" alt="Está disponible">
                    <? } else {?>
                            <img src="images/ocupado.png" height="13" border="0" alt="Ocupado por <?echo $row['IDInquilino'];?>">
                    <? }?>
                </td>
			</tr>
<?php
			//Subrrayado(4);
		}
	}?>

	<tr><td colspan="4">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"selecinmueble.php?".$parametros."&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?php
}
?>


<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<link href="estilos.css" rel="stylesheet" type="text/css">
<title>:: Selección de Inmueble</title>
</head>
<body onload="Centrar();">

<table id="ABECEDARIO" width="100%" class="ComboFamilias" border="1">
	<tr>
		<td width="100%" align="center">
		<form name="texto" action="selecinmueble.php" method="GET">
            <input name="form" type="hidden" value="<?php echo $form;?>">
            <input name="campoclave" type="hidden" value="<?php echo $campoclave;?>">
            <input name="campotexto" type="hidden" value="<?php echo $campotexto;?>">
			Texto(?):&nbsp&nbsp <input name="texto" type="text" size="30" maxlength="80" class="formularios">
            Orden:&nbsp&nbsp
            <select name="orden" class="formularios">
                <option value="IDInmueble" <?if ($orden=='IDInmueble') echo " SELECTED";?>>Codigo</option>
                <option value="Direccion" <?if ($orden=='Direccion') echo " SELECTED";?>>Direccion</option>
            </select>
			<input type='image' img src='images\lupa.png'>
            <input name="accion" type="button" value="Nuevo" class="formularios" onclick="window.open('contenido.php?c=inmuebles','Inmuebles','menubar=no,resizable=yes,width=850,height=550');">
		</form>
		<script language="JavaScript" type="text/javascript">
		document.forms[0].texto.focus();
		</script>
		</td>
	</tr>
	<tr>
		<td width="100%" align="center">
			[<a href="selecinmueble.php?abc=<?php echo "&form=",$form,"&campoclave=",$campoclave,"&campotexto=",$campotexto,"&orden=",$orden;?>">Todos</a>
		<?php 
		for ($i=65; $i <= 90; $i++){
			$abc=chr($i);
		?>
			&nbsp&nbsp<a href="selecinmueble.php?abc=<?php echo $abc,"&form=",$form,"&campoclave=",$campoclave,"&campotexto=",$campotexto,"&orden=",$orden;?>"><?php echo $abc;?></a>	
		<?php }?>
			]
		</td>
	</tr>
</table>
<?php
	Listado($filtro,$orden);
?>
</body>
</html>

