<?php
// Funciones de Facturas Recibidas

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

function RecalculaFactura($id) {
	$sql=mysql_query("select Descuento,Iva,Recargo from frecibidas_cab where IDFactura=$id");
	$row=mysql_fetch_array($sql);

	$sql=mysql_query("select sum(importe) from frecibidas_lineas where (IDFactura=$id)");
	$bruto=mysql_fetch_array($sql);
	$base=$bruto[0]-$row['Descuento'];
	$cuotai=$base*$row['Iva']/100;
	$cuotar=$base*$row['Recargo']/100;
	$total=$base+$cuotai+$cuotar;
		
	$sql="update frecibidas_cab set Importe='$bruto[0]',
             BaseImponible='$base',
             CuotaIva='$cuotai',
             CuotaRecargo='$cuotar',
             Total='$total',
             FActualizacion='".date('Ymd His').
             "' where (IDFactura=$id) limit 1;";
	$res=mysql_query($sql);
	if (!$res) Mensaje($sql);
}


function BorrarFactura($id) {
	$res=mysql_query("select count(IDFactura) from frecibidas_cab where (IDFactura=$id);");
	$ok=mysql_fetch_array($res);
    if ($ok[0]==1) {
		//Borrar líneas
		$ok=mysql_query("delete from frecibidas_lineas where (IDFactura=$id);");
		if ($ok) $ok=mysql_query("delete from frecibidas_cab where (IDFactura=$id) limit 1;");
		if ($ok)
			Mensaje ("La Factura nº ".$id." ha sido elimidada.");
			else {
				Mensaje("No se ha podido eliminar la Factura nº ".$id);
				RecalculaFactura($id);
			}

	} else Mensaje("No se ha borrado. La factura no existe");
	return($ok);
}

function BorrarLinea($idfactura,$idlinea) {
	$ok=mysql_query("delete from frecibidas_lineas where ((IDFactura=$idfactura) and (IDLinea=$idlinea)) limit 1;");
	if ($ok) RecalculaFactura($idfactura);
	else Mensaje("No se ha podido borrar la línea de factura.");
	return($ok);
}
?>
