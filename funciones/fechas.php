<?php
// FUNCIONES DE FECHAS..................

function ValidaFecha($fecha){
	$aux=str_replace('/','',$fecha);
	$aux=str_replace('.','',$aux);
	$aux=str_replace(',','',$aux);
    $aux=str_replace('-','',$aux);
    if (strlen($aux)==8){$d=substr($aux,0,2); $m=substr($aux,2,2); $a=substr($aux,4,4);}
    if (strlen($aux)==6) {$d=substr($aux,0,2); $m=substr($aux,2,2); $a=2000+substr($aux,4,2);}
    if (($d>'0') and ($d<'32') and ($m>'0') and ($m<'13') and ($a>0)) return($a.$m.$d); else return('');
}

function AlmacenaFecha($fecha){
	return(ValidaFecha($fecha));
}

function Fecha() {
	$f=date("Ymd His");
	$meses=array(Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre);
	$dias=array(Domingo,Lunes,Martes,Miercoles,Jueves,Viernes,Sabado);
	
	$diasemana=$dias[date("w")];
	$ano=substr($f,0,4);
	$mes=$meses[substr($f,4,2)-1];
	$dia=ltrim(substr($f,6,2),"0");
	$hora=substr($f,9,2).":".substr($f,11,2).":".substr($f,13,2);
	echo $diasemana.", ".$dia." de ".$mes." de ".$ano;
}

function FechaEspaniol($fecha) {
	$dia=substr($fecha,8,2);
	$mes=substr($fecha,5,2);
	$ano=substr($fecha,0,4);
	return($dia."/".$mes."/".$ano);		
}

function UltimoDia($anho,$mes){
//Devuelve el n� de d�as del a�o-mes indicado
   if (((Resto($anho,4)==0) and (Resto($anho,100)!=0)) or (Resto($anho,400)==0)) {
       $dias_febrero = 29;
   } else {
       $dias_febrero = 28;
   }
   switch($mes) {
       case 1: return 31; break;
       case 2: return $dias_febrero; break;
       case 3: return 31; break;
       case 4: return 30; break;
       case 5: return 31; break;
       case 6: return 30; break;
       case 7: return 31; break;
       case 8: return 31; break;
       case 9: return 30; break;
       case 10: return 31; break;
       case 11: return 30; break;
       case 12: return 31; break;
   }
}


function Pinta_Fecha(){
$fecha=date(dmY);
$dia=substr($fecha,0,2);
$m=substr($fecha,2,2);
$a=substr($fecha,4,4);

switch($m){
case "01": $mletra="Enero";
break;
case "02": $mletra="Febrero";
break;
case "03": $mletra="Marzo";
break;
case "04": $mletra="Abril";
break;
case "05": $mletra="Mayo";
break;
case "06": $mletra="Junio";
break;
case "07": $mletra="Julio";
break;
case "08": $mletra="Agosto";
break;
case "09": $mletra="Septiembre";
break;
case "10": $mletra="Octubre";
break;
case "11": $mletra="Noviembre";
break;
case "12": $mletra="Diciembre";
break;
}
echo $dia," de ",$mletra," de ",$a;
}

?>
