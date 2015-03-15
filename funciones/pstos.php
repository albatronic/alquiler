<?php
// Funciones de Pedido


function DameIDPsto($clave){
//Devuelve el nº de presupuesto en función a la clave md5

	$res=mysql_query("select IDPsto from psto_cab where ClavePsto='$clave'");
	$numero=mysql_fetch_array($res);
	return $numero[0];	
}

function ValidaPedido($idpedido){

    $validado=1;
    return $validado;
}

function DamePrecios($idfab,$idart,$color){
	$ok=0;
	$res=mysql_query("select * from articulos where IDFabricante='$idfab' and IDArticulo='$idart'");
	$art=mysql_fetch_array($res);
	if ($art['IDArticulo']!=''){
		$ok=1;
		$res=mysql_query("select * from incrementos where IDFabricante='$idfab' and IDColor='$color'");
		$inc=mysql_fetch_array($res);
	
		if ($art['Grupo']=='1') $incremento=$inc['Grupo1']; else $incremento=$inc['Grupo0'];
		$pvp=$art['Pvp']*(1+$incremento/100);
		$pvd=$art['Pvd']*(1+$incremento/100);
	}
	return array($ok,$pvp,$pvd,$incremento);	
}

function TramitaPedido($idpsto,$estado){

        $idagente=$_SESSION['iu'];
        $fecha=date("Ymd His");
        $cliente=$_POST['Cliente'];
        $direccion=$_POST['Direccion'];
        $poblacion=$_POST['Poblacion'];
        $provincia=$_POST['Provincia'];
		$telefono=$_POST['Telefono'];
		$movil=$_POST['Movil'];
		$email=$_POST['EMail'];
        $importe=$_POST['Importe'];
        $descuento=$_POST['Descuento'];
        $baseimponible=$importe-$descuento;
        $iva=$_POST['Iva'];
        $cuotaiva=$baseimponible*$iva/100;
		$total=$baseimponible+$cuotaiva;
        $observaciones=$_POST['Observaciones'];
		$clavepsto=md5($idagente.$fecha);

		
    if ($idpsto==0) { //Pedido Nuevo
        $valores="('', '$idagente', '$fecha', '$cliente', '$direccion', '$poblacion', '$provincia', '$telefono',
                   '$movil', '$email', '0', '0', '0', '16', '0','0', '0', '$observaciones', '$clavepsto')";
        $sql="INSERT INTO `psto_cab` VALUES ".$valores;
        $res=mysql_query($sql);
    } else
    { //Pedido antiguo
        $sql="update psto_cab set Fecha='$fecha',Cliente='$cliente', Direccion='$direccion', Poblacion='$poblacion', `Provincia`='$provincia', Telefono='$telefono',
				Movil='$movil', EMail='$email', Observaciones='$observaciones', Estado='$estado',
				Importe='$importe', Descuento='$descuento', Baseimponible='$baseimponible', Iva='$iva', CuotaIva='$cuotaiva', Total='$total'
				where IDPsto=$idpsto limit 1";
        $res=mysql_query($sql);
    }
 
 	echo "<table width='100%' border='0' align='center' class='formularios' bgcolor='#CCCCCC'>
			<tr><td align='center'>";   
    if ($res) echo "Operación realizada con éxito."; else echo "No se ha podido realizar la operación.<br>Inténtelo más tarde.";
	echo "<br><br><a href='servlet.php'><img src='images/volver.gif' border='0'></a></td></tr></table>";
}

function RecalculaPresupuesto($idpresupuesto) {

	$sql=mysql_query("select * from psto_cab where IDPsto=$idpresupuesto");
	$row=mysql_fetch_array($sql);
	
	$sql=mysql_query("select sum(importe) from psto_lineas where (IDPsto=$idpresupuesto)");
	$bruto=mysql_fetch_array($sql);
	$base=$bruto[0]-$row['Descuento'];
	$cuota=$base*$row['Iva']/100;
	$total=$base+$cuota;
	$sql="update psto_cab set Importe=$bruto[0], BaseImponible=$base, CuotaIva=$cuota, Total=$total where (IDPsto=$idpresupuesto)";
	$res=mysql_query($sql);
	if (!$res) Atras("No se ha podido recalcular el presupuesto");
}


function BorrarPresupuesto($idpresupuesto) {
	$ok=mysql_query("delete from psto_lineas where IDPsto=$idpresupuesto");
	if ($ok) $ok=mysql_query("delete from psto_cab where IDPsto=$idpresupuesto");
		
	if ($ok)
		Mensaje ("El presupuesto nº ".$idpresupuesto." ha sido elimidado."); 
		else Mensaje("No se ha podido eliminar el presupuesto nº ".$idpresupuesto);
	return($ok);
}

function BorrarLineaPresupuesto($idpsto,$idlinea) {
	$ok=mysql_query("delete from psto_lineas where (IDPsto=$idpsto) and (IDLinea=$idlinea);");
	if ($ok) RecalculaPresupuesto($idpsto);
	return($ok);
}
?>
