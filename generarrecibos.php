<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/desplegable.php";
require "funciones/fechas.php";
require "funciones/recibos.php";

$EMP=$_SESSION['DBEMP'];
$DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

$accion=$_POST['accion'];
$desdei=$_POST['desdei'];
$hastai=$_POST['hastai'];
$fecha=$_POST['fecha'];
$contadora=$_POST['contadora'];
$contadorb=$_POST['contadorb'];
$hacercopia=$_POST['hacercopia'];

$parametros="desdei=$desdei&hastai=$hastai&mes=$mes";


switch ($accion) {
    case 'Si':
        if(ValidaFecha($fecha)==''){echo "Fecha incorrecta.";exit;};
        
        if ($hacercopia=='S'){//Hacer copia de seguridad previa
            $unidad=DameParametro('UCSEG','** FALTA DEFINIR EL PARAMETRO "UCSEG"');
            list($ok,$mensaje)=CopiaSeguridad($_SESSION['empresa'],$unidad,'T');
            if (!$ok){echo $mensaje; exit;}
        }
        
        $mesrecibo=substr(ValidaFecha($fecha),4,2);
        $anorecibo=substr(ValidaFecha($fecha),0,4);
        $diarecibo=substr(ValidaFecha($fecha),6,6);
        //SELECCIONAR LOS ALQUILERES QUE ESTAN VIGENTES EN EL AñO Y MES EN CURSO
        $sql="select t1.*,t2.Iva,t2.Recargo,t2.Retencion as ValorRetencion,t3.Direccion,concat(t3.IDBanco,t3.IDOficina,t3.Digito,t3.Cuenta) as CuentaAbono,concat(t4.IDBanco,t4.IDOficina,t4.Digito,t4.Cuenta) as CuentaCargo,
                t3.Iban as IbanAbono,t3.Bic BicAbono,t4.Iban IbanCargo,t4.Bic BicCargo,t4.Mandato MandatoCargo, t4.FechaMandato FechaMandatoCargo
                from $DAT.inmuebles_inquilinos as t1,$EMP.tipos_iva as t2,$DAT.inmuebles as t3,$EMP.inquilinos as t4 WHERE
                t1.IDIva=t2.IDIva and
                t1.IDInmueble=t3.IDInmueble and
                t1.IDInquilino=t4.IDInquilino and
                t1.IDInmueble>='$desdei' and t1.IDInmueble<='$hastai' and
                DATE_FORMAT(t1.FechaInicio,'%Y%m')<='".$anorecibo.$mesrecibo."' AND
                DATE_FORMAT(t1.FechaFin,'%Y%m%d')>='".$anorecibo.$mesrecibo.$diarecibo."'
                ORDER BY t3.IDInmueble;";
        $res=mysql_query($sql);
        $i=0;
        $log=date('Ymd_His');
        $historico="<table class='formularios' border='1' width='100%'>\n
                <caption>GENERACION DE RECIBOS EL $log</caption>\n
                <tr>\n
                <th colspan=2>Inmueble</th>
                <th>Dias0</th>
                <th>Dias1</th>
                <th>Tot.Dias</th>
                <th>Aplicar Subida</th>
                <th>Proxima Subida</th>
                <th>Subido</th>
                <th>Recibo Creado</th>\n
                </tr>\n";
        
        while ($alq=mysql_fetch_array($res)){
        //PARA CADA ALQUILER VIGENTE, SELECCIONAR LOS CONCEPTOS QUE HAY QUE FACTURAR

            //Leer el contador que corresponda según lleve o no iva el alquiler
            //Si el valor del iva es negativo, se entiende que no se declara.
            if ($alq['Iva']>=0) $serie=$contadora; else $serie=$contadorb;
            $idrecibo=DameContador($_SESSION['empresa'],$serie)+1;
            $idrecibo=str_pad($idrecibo,5,"0",STR_PAD_LEFT);
            
            //Calcular los dias a facturar
            $diasmes=UltimoDia($anorecibo,$mesrecibo); //Saber los dias que tiene el mes de facturacion
            $primerodemes=$anorecibo."-".$mesrecibo."-01";    //Construir la fecha completa del primer dia del mes de facturaci�n
            //Si el contrato ha empezado despues de primero de mes, se considerara primero de mes la
            //fecha de inicio del contrato.
            if($alq['FechaInicio']>$primerodemes) $primerodemes=$alq['FechaInicio'];
            $findemes=$anorecibo."-".$mesrecibo."-".$diasmes; //Construir la fecha completa del ultimo dia del mes en curso
            if ($alq['FechaFin']>=$findemes) {
                $totdias=$diasmes-substr($primerodemes,8,2)+1;
            } else {
                $totdias=substr($alq['FechaFin'],8,2)-substr($primerodemes,8,2)+1;
            }
            
            //Crear la cabecera del recibo, sin totalizar. Se totaliza al final
            //-----------------------------------------------------------------

            $periodo=FechaEspaniol($primerodemes)."-".date('d/m/Y',mktime(0,0,0,$mesrecibo,substr($primerodemes,8,2)-1+$totdias,$anorecibo));
            if ($alq['Retencion']=="S") $retencion=$alq['ValorRetencion']; else $retencion=0;
            if ($alq['Iva']>=0){
                $valoriva=$alq['Iva'];
                $valorrecargo=$alq['Recargo'];
            } else {//Si el valor del iva es negativo, se entiende que no lleva iva (no se declara).
                $valoriva=0;
                $valorrecargo=0;
            }
            $valores="'$serie$idrecibo','".$alq['IDInquilino']."','".$alq['IDInmueble']."','".ValidaFecha($fecha)."','$periodo','0','0','$valoriva','$valorrecargo','$retencion','0','".$alq['CuentaCargo']."','".$alq['CuentaAbono']."','','S','0','{$alq['IbanCargo']}','{$alq['BicCargo']}','{$alq['IbanAbono']}','{$alq['BicAbono']}','{$alq['MandatoCargo']}','{$alq['FechaMandatoCargo']}'";

            mysql_query("INSERT INTO $DAT.recibos VALUES ($valores);");
            
            //Ver si hay que aplicar una subida dentro del periodo a facturar
            $aplicarsubida='N'; $nuevafecha=""; $subido='N'; $dias0=''; $dias1='';
            if (($alq['FechaSubida']>=$primerodemes) and ($alq['FechaSubida']<=$findemes)){
                $aplicarsubida='S';
                $dias0=substr($alq['FechaSubida'],8,2)-substr($primerodemes,8,2); //N� de d�as a precio antig�o
                $dias1=$totdias-$dias0;//N. de dias a precio nuevo
            }

            $historico=$historico."<tr><td><a href='contenido.php?c=alquilar&columna=inmuebles.IDInmueble&valor=".$alq['IDInmueble']."' target='_blank'>".$alq['IDInmueble']."</a>";
            $historico=$historico."</td><td>".$alq['Direccion']."<br>FI:".FechaEspaniol($alq['FechaInicio'])." FF:".FechaEspaniol($alq['FechaFin'])." FS:".FechaEspaniol($alq['FechaSubida'])."</td><td>".$dias0."</td><td>".$dias1."</td><td>".$totdias."</td>";
            
            $sql="select t1.*,t2.Consumo,t2.Iva,t2.SubeAutomatico
                    from $DAT.inmuebles_conceptos as t1,$EMP.conceptos as t2 where
                    t1.IDInmueble='".$alq['IDInmueble']."' and
                    t1.IDConcepto=t2.IDConcepto
                    order by t2.Consumo,t1.IDConcepto;";
            $recibocreado='N';
            $res1=mysql_query($sql);
            while ($con=mysql_fetch_array($res1)){
                if ($con['Consumo']=="S"){ //Tratamiento de los Conceptos de Consumo
                    $unidades=$con['ValorActual']-$con['ValorAnterior'];
                    // Si la diferencia de lecturas no es positiva se entiende que
                    // ha dado la vuelta el contador.
                    if ($unidades<0) $unidades += 100000;
                    if ($unidades>0){ //Si la diferencia de lecturas no es positiva, no genero recibo
                        $importe=ROUND($unidades*$con['Importe'],2);
                        $periodo='';
                        $valores="'','$serie$idrecibo','".ValidaFecha($fecha)."','".$con['IDConcepto']."','$periodo','".$con['Importe']."','".$con['ValorAnterior']."','".$con['ValorActual']."','$unidades','$importe'";
                        $crea="INSERT INTO $DAT.recibos_lineas VALUES($valores);";
                        if (mysql_query($crea)){
                            $recibocreado='S';
                            //Actualizar los valores de las lecturas
                            $sql="UPDATE $DAT.inmuebles_conceptos SET ValorAnterior='".$con['ValorActual']."' WHERE IDInmueble='".$alq['IDInmueble']."' and IDConcepto='".$con['IDConcepto']."';";
                            mysql_query($sql);
                        }
                    }
                } else {//Tratamiento de los conceptos de NO Consumo
                    if (($aplicarsubida=='S') and ($con['SubeAutomatico'])=='S'){
                        //Recibo del primer periodo
                        if($dias0>0){
                            $importe=round(($con['Importe']/$diasmes)*$dias0,2);
                            $periodo=FechaEspaniol($primerodemes)."-".date('d/m/Y',mktime(0,0,0,$mesrecibo,substr($primerodemes,8,2)-1+$dias0,$anorecibo));
                            $valores="'','$serie$idrecibo','".ValidaFecha($fecha)."','".$con['IDConcepto']."','$periodo','".$con['Importe']."','$diasmes','$dias0','1','$importe'";
                            $crea="INSERT INTO $DAT.recibos_lineas VALUES($valores);";
                            if (mysql_query($crea)) $recibocreado='S';
                        }

                        //Recibo del segundo periodo aplicando la subida
                        if($dias1>0){
                            $precionuevo=$con['Importe']*(1+$alq['PorcentajeSubida']/100);
                            $importe=round(($precionuevo/$diasmes)*$dias1,2);
                            $periodo=FechaEspaniol($alq['FechaSubida'])."-".date('d/m/Y',mktime(0,0,0,$mesrecibo,substr($alq['FechaSubida'],8,2)-1+$dias1,$anorecibo));
                            $valores="'','$serie$idrecibo','".ValidaFecha($fecha)."','".$con['IDConcepto']."','$periodo','$precionuevo','$diasmes','$dias1','1','$importe'";
                            $crea="INSERT INTO $DAT.recibos_lineas VALUES($valores);";
                            if (mysql_query($crea)) $recibocreado='S';
                        }
                        
                        //Cambiar la Fecha de subida
                        $nuevafecha=date('Y-m-d',mktime(0,0,0,substr($alq['FechaSubida'],5,2),substr($alq['FechaSubida'],8,2),substr($alq['FechaSubida'],0,4)+$alq['AnosSubida']));
                        if(mysql_query("UPDATE $DAT.inmuebles_inquilinos SET FechaSubida='$nuevafecha' WHERE IDAlquiler=".$alq['IDAlquiler'])) $subido="S";

                        //Cambiar el precio del concepto
                        mysql_query("UPDATE $DAT.inmuebles_conceptos set Importe='$precionuevo' WHERE IDInmueble='".$con['IDInmueble']."' and IDConcepto='".$con['IDConcepto']."';");

                    } else {
                        $importe=round(($con['Importe']/$diasmes)*$totdias,2);
                        $periodo=FechaEspaniol($primerodemes)."-".date('d/m/Y',mktime(0,0,0,$mesrecibo,substr($primerodemes,8,2)-1+$totdias,$anorecibo));
                        $valores="'','$serie$idrecibo','".ValidaFecha($fecha)."','".$con['IDConcepto']."','$periodo','".$con['Importe']."','$diasmes','$totdias','1','$importe'";
                        $crea="INSERT INTO $DAT.recibos_lineas VALUES($valores);";
                        if (mysql_query($crea)) $recibocreado='S';
                    }
                }
            }
            if ($recibocreado=="S"){
                $i++;
                GuardaContador($_SESSION['empresa'],$serie,$idrecibo);
            }
            TotalizaRecibo($serie.$idrecibo);
            
            $historico=$historico."<td>$aplicarsubida</td><td>".FechaEspaniol($nuevafecha)."</td><td>$subido</td><td align='center'>$recibocreado";
            if ($recibocreado=="S") $historico=$historico."-<a href='recibopdf.php?d=$serie$idrecibo&h=$serie$idrecibo' target='_blank'>$serie$idrecibo</a>";
            $historico=$historico."</td></tr>\n";
        }
        $historico=$historico."<tr height='20'><td colspan='9'></td></tr>\n</table>\n<input name='imprimir' type='button' value='Imprimir' class='formularios' onclick='window.print();'>";
        $fp=fopen("log/$log.php",'w');
        fwrite($fp,$historico);
        fclose($fp);
        ?>
            <script language="JavaScript" type="text/javascript">
            window.open('contenido.php?c=log/<?echo $log;?>','Historico','');
            </script>
        <?
        
        break;

	case '':
        ?>
        <table align="center"><tr height="100"><td></td></tr></table>
        <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Generar Recibos</th></tr>
        <form name="form" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="generarrecibos">
        <tr><td colspan="2">Desde Inmueble:&nbsp<input name="desdei" type="text" size="6" maxlength="6" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Inmueble:&nbsp<input name="hastai" type="text" size="6" maxlength="6" class="formularios"></td></tr>
        <tr><td colspan="2">Fecha de Emisi&oacute;n:&nbsp<input name="fecha" value="<?echo date('d.m.Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">
            Serie 1:&nbsp;
            <select name="contadora" class="formularios">
            <?$res=mysql_query("select IDSerie,Contador from $EMP.series_recibos where IDEmpresa='".$_SESSION['empresa']."' and ConIva='S';");
            while ($row=mysql_fetch_array($res)){?>
            <option value="<?echo $row['IDSerie'];?>"><?echo $row['IDSerie'],"-",$row['Contador'];?></option>
            <?}?>
            </select>
            <img src="images/lupa.png" onclick="window.open('contenido.php?c=contadores&idempresa=<? echo $_SESSION['empresa'];?>&coniva=S','Contadores','width=300,height=400,resizable=yes,scrollbars=yes');">
        </td></tr>
        <tr><td colspan="2">
            Serie 2:&nbsp;
            <select name="contadorb" class="formularios">
            <?$res=mysql_query("select IDSerie,Contador from $EMP.series_recibos where IDEmpresa='".$_SESSION['empresa']."' and ConIva='N';");
            while ($row=mysql_fetch_array($res)){?>
            <option value="<?echo $row['IDSerie'];?>"><?echo $row['IDSerie'],"-",$row['Contador'];?></option>
            <?}?>
            </select>
            <img src="images/lupa.png" onclick="window.open('contenido.php?c=contadores&idempresa=<? echo $_SESSION['empresa'];?>&coniva=N','Contadores','width=300,height=400,resizable=yes,scrollbars=yes');">
        </td></tr>
        <tr><td colspan="2">
            Hacer copia de Seguridad previa:&nbsp;<?DesplegableSN('hacercopia','S','formularios');?>
        </td></tr>
        <tr><td colspan="2"></td></tr>
        <tr valign="top">
            <td align="right">Desea generar los recibos?&nbsp;<input name="accion" value="Si" type="submit" class="formularios" onclick="return Confirma('Desea generar los recibos?');"></td>
            <script language="JavaScript" type="text/javascript">
            document.form.desdei.focus();
            </script>
        </form>
            <td align="left">
                <form name="form" action="contenido.php" method="POST">
                <input name="c" type="hidden" value="inicial">
                <input name="accion" value="No" type="submit" class="formularios">
                </form>
            </td>
        </tr>
        </table>
        <?php
        break;
}
?>
