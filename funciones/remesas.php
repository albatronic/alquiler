<?
//FUNCIONES DE REMESAS

function InformeRemesa($f){
//Muestra un resumen de totales de lo contenido en el fichero de remesa que se manda al banco
//Devuel un string en HTML con el informe

    $informe="<TABLE class='formularios' align='center'>
                <tr><th colspan='3'>DETALLE DE LA REMESA '$f'</th></tr>";
                
    $fp=fopen($f,'r');
    while (!feof($fp)) {
        $linea=fgets($fp,4096);
        $clave=substr($linea,0,4);
        switch ($clave){
            case '5180':    //Datos del presentador
                $informe.="<tr><td colspan='4' align='center'>Presentador: ".substr($linea,4,9)." ".substr($linea,28,50)."</td></tr>";
                $informe.="<tr><td colspan='4' align='center'>Entidad/Oficina Receptora: ".substr($linea,88,4)."/ ".substr($linea,92,4)."</td></tr>";
                $informe.="<tr><th>EMPRESA</th><th>CUENTA ABONO</th><th>N.RECIBOS</th><TH>IMPORTE</TH></tr>";
                break;
            
            case '5380':    //Cabecera Empresa
                $informe.="<tr><td>".substr($linea,4,9)." ".substr($linea,28,40)."</td><td>".substr($linea,68,20)."</td>";
                break;
                
            case '5880':    //Total Empresa
                $importe=substr($linea,88,8).".".substr($linea,96,2);
                $informe.="<td align='right'>".number_format(substr($linea,104,10))."</td><td align='right'>".number_format($importe,2)."</td></tr>";
                break;
                
            case '5980':    //Total Remesa
                $importe=substr($linea,88,8).".".substr($linea,96,2);
                $informe.="<tr><td colspan='2' align='right'><b>TOTAL REMESA</b></td><td align='right'><b>".number_format(substr($linea,104,10))."</b></td><td align='right'><b>".number_format($importe,2)."</b></td></tr>";
                break;
                
        }
    }
    fclose($fp);

    $informe.="</table>";
    return($informe);
}
?>
