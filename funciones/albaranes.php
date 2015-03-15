<?php
// Funciones de Albaranes

function DameIDAlbaran($clave){
//Devuelve el nº de albarán en función a la clave md5

	$res=mysql_query("select IDAlbaran from albaranes_cab where Clave='$clave'");
	$numero=mysql_fetch_array($res);
	return $numero[0];	
}

function DamePrecios($idart){
// BUSCA EL ARTICULO POR EL CODIGO O POR EL EAN Y DEVUELVE EL CODIGO, PRECIOS Y DESCRIPCION.
	$ok=0; $pvp=0; $pvd=0;
	$res=mysql_query("select IDArticulo,Descripcion,Pvp,Pvd from articulos where IDArticulo='$idart' or CodigoEAN='$idart'");
	$art=mysql_fetch_array($res);
	if ($art['IDArticulo']!=''){
		$ok=1;
		$idart=$art['IDArticulo'];
		$descripcion=$art['Descripcion'];
		$pvp=$art['Pvp'];
		$pvd=$art['Pvd'];
	}
	return array($ok,$idart,$pvp,$pvd,$descripcion);	
}

function RecalculaAlbaran($id) {
	$sql=mysql_query("select Descuento,Iva,Recargo from albaranes_cab where IDAlbaran=$id");
	$row=mysql_fetch_array($sql);

	$sql=mysql_query("select sum(importe) from albaranes_lineas where (IDAlbaran=$id)");
	$bruto=mysql_fetch_array($sql);
	$base=$bruto[0]-$row['Descuento'];
	$cuotai=$base*$row['Iva']/100;
	$cuotar=$base*$row['Recargo']/100;
	$total=$base+$cuotai+$cuotar;
		
	//Calcular el peso, volumen y los bultos
	$sql=mysql_query("select sum(articulos.peso*albaranes_lineas.Unidades) as Peso, sum(articulos.volumen*albaranes_lineas.Unidades) as Volumen, sum(Unidades) as Bultos from articulos,albaranes_lineas where (albaranes_lineas.IDArticulo=articulos.IDArticulo) and (albaranes_lineas.IDAlbaran=$id)");
    $row=mysql_fetch_array($sql);
	$sql="update albaranes_cab set Importe='$bruto[0]',
             BaseImponible='$base',
             CuotaIva='$cuotai',
             CuotaRecargo='$cuotar',
             Total='$total',
             Peso='".$row['Peso']."',
             Volumen='".$row['Volumen']."',
             Bultos='".$row['Bultos']."',
             FActualizacion='".date('Ymd His').
             "' where (IDAlbaran=$id) limit 1;";
	$res=mysql_query($sql);
	if (!$res) Mensaje($sql);
}


function BorrarAlbaran($id) {
	$res=mysql_query("select count(IDAlbaran) from albaranes_cab where ((IDAlbaran=$id) and (Expedido='0') and(IDFactura=0));");
	$ok=mysql_fetch_array($res);
    if ($ok[0]==1) {
		//Si tiene recibos, no se puede borrar
		$res=mysql_query("select count(Numero) from recibos_clientes where (Numero=$id);");
		$ok=mysql_fetch_array($res);
		if ($ok[0]>0){
			$ok=0;
			Mensaje("No se puede eliminar porque tiene recibos.");
		} else {
			//Borrar líneas
			$ok=mysql_query("delete from albaranes_lineas where (IDAlbaran=$id);");
			if ($ok) $ok=mysql_query("delete from albaranes_cab where ((IDAlbaran=$id) and (Expedido='0') and (IDFactura=0)) limit 1;");
			if ($ok)
				Mensaje ("El Albarán nº ".$id." ha sido elimidado."); 
				else {
					Mensaje("No se ha podido eliminar el Albaran nº ".$id);
					RecalculaAlbaran($id);
				}
		}
	} else Mensaje("No se ha borrado. El albarán no existe o está expedido");
	return($ok);
}

function BorrarLinea($idalbaran,$idlinea) {
	$ok=mysql_query("delete from albaranes_lineas where ((IDAlbaran=$idalbaran) and (IDLinea=$idlinea)) limit 1;");
	if ($ok) RecalculaAlbaran($idalbaran);
	else Mensaje("No se ha podido borrar la línea de albarán.");
	return($ok);
}

function ExpideAlbaran($id){
	
	$res=mysql_query("select albaranes_lineas.IDArticulo, albaranes_cab.IDSucursal, albaranes_lineas.Unidades from albaranes_lineas, albaranes_cab where (albaranes_lineas.IDAlbaran=$id) and (albaranes_cab.Expedido='0') and (albaranes_cab.IDAlbaran=albaranes_lineas.IDAlbaran);");
	while ($row=mysql_fetch_array($res)){
		$sql="select * from existencias where (IDSucursal='".$row['IDSucursal']."' and IDArticulo='".$row['IDArticulo']."')";
        $a=mysql_query($sql);
		$exi=mysql_fetch_array($a);
		if ($exi['IDArticulo']!=''){
			$existencias=$exi['Reales']-$row['Unidades'];
			$sql="update existencias set Reales='$existencias' where (IDSucursal='".$row['IDSucursal']."' and IDArticulo='".$row['IDArticulo']."') limit 1;";
		} else {
			$unidades=0-$row['Unidades'];
			$sql="insert into existencias values('".$row['IDSucursal']."','".$row['IDArticulo']."','".$unidades."','0','0','0','0')";
		}
		mysql_query($sql);
	}
	$res=mysql_query("update albaranes_cab set Expedido='1', FechaEntrega='".date('Ymd')."', FActualizacion='".date('Ymd His')."' where (IDAlbaran=$id) and (Expedido='0') limit 1;");
	if ($res) return("1"); else return("0");	
}

function PonRecibos($idalbaran,$importe){
	$res=mysql_query("select IDAlbaran,Fecha,IDCliente from albaranes_cab where IDAlbaran=$idalbaran");
	$row=mysql_fetch_array($res);
	
	$valores="('A','".$row['IDAlbaran']."','".$row['Fecha']."','".$row['Fecha']."','".$importe."','".$row['IDCliente']."','P','".date("Ymd His")."')";
	$sql="insert into recibos_clientes (Tipo,Numero,FExpedicion,FVencimiento,Importe,IDCliente,Estado,FActualizacion) values ".$valores;
	$res=mysql_query($sql);
	if (!$res) Mensaje("OJO!!!!: No se ha generado el recibo del albarán $idalbaran.");
}

function PonEnCaja($idalbaran,$importe,$fcobro,$caja){
	$m='';
    if ($caja=='') $m="No está definida la variable 'caja'. No se ha generado el apunte.";
    if ($_SESSION['sucursal']=='') $m="No está definida la variable 'sucursal'. No se ha generado el apunte.";
    if ($m=='') {
		$valores="('".$_SESSION['sucursal']."','".
                    $caja."','','".
                    date('Ymd His')."','Entrada por Albaran $idalbaran','".
                    $fcobro."','V','".
                    $idalbaran."','".
                    $importe."','0')";
		$sql="insert into cajas (IDSucursal,IDTpv,IDApunte,Fecha,Concepto,FormaDePago,Origen,Documento,Importe,Asiento) values ".$valores;
		$res=mysql_query($sql);
		if (!$res) Mensaje("OJO!!!!: No se ha podido generar el apunte en caja del albarán $idalbaran.");
	} else Mensaje($m);
}

function Facturar($idalbaran,$fecha){
	$sql="select * from albaranes_cab where (IDAlbaran=".$idalbaran.") and (Expedido='1') and (IDFactura=0)";
	$res=mysql_query($sql);
    $alb=mysql_fetch_array($res);
	if ($alb['IDAlbaran']=='') return(0);
	
    if ($fecha=='') $fecha=date("Ymd");
    
	$factura=0;
	//Leo contadores
	$res=mysql_query("select ContadorFacturas from ".$_SESSION['DBEMP'].".sucursales where IDSucursal=".$alb['IDSucursal']);
	$contador=mysql_fetch_array($res);
	$nfact=$contador[0]+1;
	
	//Creo la cabecera de factura
	$valores="'','".
            $alb['IDSucursal']."','".
            $nfact."','".
            $fecha."','".
            $alb['IDCliente']."','".
            $alb['Importe']."','".
            $alb['Descuento']."','".
            $alb['BaseImponible']."','".
            $alb['Iva']."','".
            $alb['CuotaIva']."','".
            $alb['Recargo']."','".
            $alb['CuotaRecargo']."','".
            $alb['Total']."','7000000001','0','".
            $alb['Observaciones']."','".
            $alb['Peso']."','".
            $alb['Volumen']."','".
            $alb['Bultos']."','".
            $alb['Expedicion']."','".
            $alb['IDAgencia']."','".
            md5($_SESSION['sucursal'].$nfact)."','".
            date("Ymd His")."'";
	$sql="insert into femitidas_cab values(".$valores.")";
	$res=mysql_query($sql);
	if ($res){
		//Actualizo los contadores
		$res=mysql_query("update ".$_SESSION['DBEMP'].".sucursales set ContadorFacturas=$nfact where IDSucursal=".$alb['IDSucursal']);
        //Busco el id asignado a la factura
        $res=mysql_query("select IDFactura from femitidas_cab where (NumeroFactura=$nfact and IDSucursal=".$alb['IDSucursal'].")");
        $fac=mysql_fetch_array($res);
		//Creo las líneas de factura
		$ok=1;
		$res=mysql_query("select * from albaranes_lineas where IDAlbaran=$idalbaran");
		while ($row=mysql_fetch_array($res)){
			$valores="'".$fac['IDFactura']."','','".$row['IDArticulo']."','".$row['Descripcion']."','".$row['Unidades']."','".$row['Precio']."','".$row['Descuento']."','".$row['Importe']."','".$row['ImporteCosto']."','".$row['NumeroSerie']."','".$idalbaran."','".date("Ymd His")."','".$row['Iva']."','0','0'";
			$sql="insert into femitidas_lineas (IDFactura,IDLinea,IDArticulo,Descripcion,Unidades,Precio,Descuento,Importe,ImporteCosto,NumeroSerie,IDAlbaran,FActualizacion,Iva,ComisionAgente,ComisionMontador) values (".$valores.")";
			if (!mysql_query($sql)) {Mensaje("No se ha podido crear la línea de factura: $sql"); $ok=0;}			
		}
		if ($ok==1){
			//Marcar el albaran como facturado
			mysql_query("update albaranes_cab set IDFactura=$nfact where IDAlbaran=$idalbaran");
		}
		$factura=$nfact;
	} else Mensaje("No se ha podido generar la factura: ".$sql);
	return($factura);
}
?>
