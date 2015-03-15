<?
//FUNCIONES PARA OPERAR CON LOS RECIBOS
//-------------------------------------


function DameContador($emp,$serie){
//DEVUELVE EL VALOR ACTUAL DEL CONTADOR DE LA EMPRESA Y SERIE INDICADA
    global $EMP;

    $sql="select Contador from $EMP.series_recibos where IDEmpresa='$emp' and IDSerie='$serie';";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    return $row[0];
}

function GuardaContador($emp,$serie,$contador){
//GRABA EL VALOR CONTADOR DE LA EMPRESA Y SERIE INDICADA
    global $EMP;

    $sql="update $EMP.series_recibos set Contador=$contador where IDEmpresa='$emp' and IDSerie='$serie';";
    return(mysql_query($sql));
}

function TotalizaRecibo($id){
    global $EMP,$DAT;
    
//NO SE TIENEN EN CUENTA LOS POSIBLES CONCEPTOS DE COBRO QUE TENGA EL RECIBO
    $conceptocobro=DameParametro('COBRO','00');
    
    $res=mysql_query("select * from $DAT.recibos where IDRecibo='$id'");
    $rec=mysql_fetch_array($res);
    
    $sql="SELECT t1.IDRecibo,sum(t1.Importe) as Importe , t2.Iva
            FROM $DAT.recibos_lineas AS t1, $EMP.conceptos AS t2
            WHERE t1.IDRecibo='$id' AND t1.IDConcepto=t2.IDConcepto and t1.IDConcepto<>'$conceptocobro'
            GROUP BY t1.IDRecibo,t2.Iva";
    $res=mysql_query($sql);
    $bci=0;$bsi=0;
    while ($lin=mysql_fetch_array($res))
        if ($lin['Iva']=='S') $bci=$bci+$lin['Importe']; else $bsi=$bsi+$lin['Importe'];
        
    $total=ROUND($bsi+$bci*(1+$rec['Iva']/100+$rec['Recargo']/100-$rec['Retencion']/100),2);
    
    //Calcular lo cobrado. Lo apuntes de cobro van con signo negativo
    $cobrado=0;
    $sql="select sum(Importe) from $DAT.recibos_lineas where IDRecibo='$id' and IDConcepto='$conceptocobro';";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    $cobrado=$row[0];
    $saldo=$total+$cobrado;
    
    //Actualizar el recibo
    $sql="update $DAT.recibos set Base='$bci',BaseMediacion='$bsi',Total='$total',Saldo='$saldo' where IDRecibo='$id'";
    mysql_query($sql);
}

function BorrarLinea($idrecibo,$idlinea){
    $sql="delete from recibos_lineas where IDRecibo='$idrecibo' and IDLinea='$idlinea';";
    $ok=mysql_query($sql);
    if ($ok) TotalizaRecibo($idrecibo);
    return($ok);
}

function CobrarRecibo($id,$concepto,$fecha){
    $ok=0;

    //Buscar el importe total del recibo.
    $res=mysql_query("select Saldo from recibos where IDRecibo='$id';");
    $row=mysql_fetch_array($res);
    $ptecobro=$row['Saldo'];
    
    if($ptecobro>0){
        if($fecha=='') $fecha=date('d/m/Y');
        $fecha=AlmacenaFecha($fecha);
        $valores="'$id','$fecha','$concepto','','1','0','-1','$ptecobro','".-1*$ptecobro."'";
        $sql="INSERT INTO recibos_lineas (IDRecibo,Fecha,IDConcepto,Periodo,ValorAnterior,ValorActual,Unidades,Precio,Importe) VALUES ($valores)";
        if(mysql_query($sql)){
            $ok=1;
            TotalizaRecibo($id);
        }
    }
    return($ok);
}

function SaldoInquilino($id){
    $DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

	$res=mysql_query("select sum(Saldo) from $DAT.recibos where IDInquilino='$id';");
	$saldo=mysql_fetch_array($res);
	return($saldo[0]);
}

function SaldoInmueble($id){
    $DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

	$res=mysql_query("select sum(Saldo) from $DAT.recibos where IDInmueble='$id';");
	$saldo=mysql_fetch_array($res);
	return($saldo[0]);
}

function SaldoAlquiler($idinq,$idinm){
    $DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

	$res=mysql_query("select sum(Saldo) from $DAT.recibos where (IDInmueble='$idinm' and IDInquilino='$idinq');");
	$saldo=mysql_fetch_array($res);
	return($saldo[0]);
}
?>
