<?php 
//NOMBRE DEL FORMULARIO Y DEL CAMPO DONDE HAY QUE DEVOLVER LA FECHA SELECCIONADA.
$formulario=$_GET['f'];
if(!isset($formulario)) $formulario=$_POST['f'];
$campo=$_GET['c'];
if(!isset($campo)) $campo=$_POST['c'];

$hoy=$_POST['hoy'];
if(isset($hoy)) {
	$mes=date("n"); $anio=date("Y");
} else {
	$mes=$_POST['mes'];
	if(!isset($mes)) $mes=$_GET['mes'];
	if(!isset($mes)) $mes=date("n");

	$anio=$_POST['anio'];
	if(!isset($anio)) $anio=$_GET['anio'];
	if(!isset($anio)) $anio=date("Y");
}
?>

<script language="JavaScript" type="text/javascript">
function CogeFecha(fecha,formulario,campo){
	window.opener.document[formulario][campo].value=fecha;
	window.close();	
}

function RecargaVentana(mes,anio,formulario,campo) {
	var url;
	if(mes<1){mes=12;anio=eval(anio)-1;}
	if(mes>12){mes=1;anio=eval(anio)+1;}
	url='calendario.php?mes=' + mes + '&anio=' + anio + '&f=' + formulario + '&c=' + campo;
	document.location.href=url;
}
</script>

<?php

// LISTADO MESES CORTOS EN INGLES
function listameses(){
	$a=gregoriantojd(1,10,2005);

	for($c=1;$c<13;$c++){
		$mes[$c]=jdmonthname($a,0);
		$a+=32;
		}
	return $mes;
}

//SE A�ADEN HUECOS POR LA IZQUIERDA A LA MATRIZ
function aniadirhuecos($matriz,$numhuecos){
	for($c=0;$c<$numhuecos;$c++)
		array_unshift($matriz,"");
	return $matriz;
}

//TABLA CALENDARIO DEL MES CON ENCABEZAMIENTO DIAS SEMANA
function Calendario($mes,$anio){
	
global $formulario,$campo;

$diasmes=range(1,cal_days_in_month(1,$mes,$anio));//matriz de 31,30,29 � 28 elementos
$diasmesreal=sizeof($diasmes);

$primerdiames=date("w",mktime(0,0,0,$mes,1,$anio));;//dia de la semana del 1� de mes 
switch ($primerdiames){//aniado huecos por la izquierda a la matriz $diasmes para ajustar a los encabezamientos del calendario
	case 1://lunes
		break;
	case 2:// martes
		//array_unshift($diasmes,"");
		$diasmes=aniadirhuecos($diasmes,1);
		 break;
	case 3:// miercoles
		$diasmes=aniadirhuecos($diasmes,2);
		 break;
	case 4://jueves
		$diasmes=aniadirhuecos($diasmes,3);
		 break;
	case 5:// viernes
		$diasmes=aniadirhuecos($diasmes,4);
		 break;
	case 6://s�bado
		$diasmes=aniadirhuecos($diasmes,5);
		 break;
	case 0://domingo
		$diasmes=aniadirhuecos($diasmes,6);
		 break;
}
?>

	<table width="100%"  border="0" class="formularios">
        <tr> 
          <th>L</th><th>M</th><th>X</th><th>J</th><th>V</th><th>S</th><th><basefont color="#FF0000">D</th>
        </tr>

		<?php
		$b=6;
		if(($primerdiames<5 && $primerdiames>0)||($primerdiames==5)||($primerdiames==6 && $diasmesreal<31)||($primerdiames==2 && $diasmesreal==28)||($primerdiames==0 && $diasmesreal<30))$b=5;
		if($primerdiames==1 && $diasmesreal==28)$b=4;

		for($a=0;$a<$b;$a++){//$b es el num de semanas a mostrar
			echo "<tr>";
			for($c=0;$c<7;$c++){
				list(,$dia)=each($diasmes);
				if($c==6) {$color="#FF0000";} else {$color="";}?>
				<td onClick="<?php echo "CogeFecha('",$dia,"/",$mes,"/",$anio,"','",$formulario,"','",$campo,"');";?>">
					<basefont color="<?php echo $color;?>"><?php echo $dia;?>
				</td>
			 <?php
			}?>
			</tr>
		<?php
		}
		reset($diasmes);
		?>
	</table>
<?php
}
?>

<html>
<head>
<title>Calendario</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
	th,td{text-align:center}
.formularios {
	FONT: 8pt Verdana, Arial, Helvetica, sans-serif
}
</style>
</head>
<body background="../images/fondo_cuadritos.gif" leftmargin="2" rightmargin="2" topmargin="2">
<noscript>Tu navegador no soporta JavaScript!</noscript>
<table width="280" border="0"  align="center" class="formularios">
	<tr>
		<td colspan="5">
  			<table class="formularios">
			<tr>
  				<td><img src="../images/anterior1.gif" onClick="RecargaVentana(eval(document.mesanio.mes.value)-1,document.mesanio.anio.value,'<?php echo $formulario;?>','<?php echo $campo;?>');" alt="Retroceder un mes"></td>
				<form name="mesanio" method="post" action="calendario.php">
				<input type="hidden" name="f" value="<?php echo $formulario;?>">
    			<input type="hidden" name="c" value="<?php echo $campo;?>">
				<td align="center">
					Mes:
					<select name="mes" size="1" id="mes" class="formularios" onchange="RecargaVentana(document.mesanio.mes.value,document.mesanio.anio.value,'<?php echo $formulario;?>','<?php echo $campo;?>');" >
					<?php
					$meses=listameses();
					for($c=1;$c<13;$c++){?>
					<option value='<?php echo $c;?>' <?php if ($c==$mes) echo "SELECTED";?>><?php echo $meses[$c];?></option>
					<?php }?>
					</select>
				</td>
    			<td align="center">
					A�o:
					<select name="anio" size="1" id="anio" class="formularios" onchange="RecargaVentana(document.mesanio.mes.value,document.mesanio.anio.value,'<?php echo $formulario;?>','<?php echo $campo;?>');">
					<?php 
					for($c=1971;$c<2100;$c++){?>
					<option value='<?php echo $c;?>' <?php if ($c==$anio) echo "SELECTED";?>><?php echo $c;?></option>
					<?php }?>
					</select>
				</td>
				<td>
					<input type="submit" value="Hoy" name="hoy" class="formularios">
				</td>
				</form>
				<td><img src="../images/siguiente1.gif" onClick="RecargaVentana(eval(document.mesanio.mes.value)+1,document.mesanio.anio.value,'<?php echo $formulario;?>','<?php echo $campo;?>');" alt="Avanzar un mes"></td>
  			</tr>
  			</table>
		</td>
	</tr>
	<tr> 
    	<td  colspan="5" bgcolor="#00CCFF" >
		<?php Calendario($mes,$anio);?>
		</td>
	</tr>
</table>
</body>
</html>
