<?php
session_start();
if ($_SESSION['iu'] == '')
    exit;
$idagente = $_SESSION['iu'];
$esadm = $_SESSION['esadm'];

require "conecta.php";
require "funciones/desplegable.php";
require "funciones/fechas.php";
require "funciones/recibos.php";
require "funciones/formatos.php";
require "funciones/textos.php";
//require "funciones/Cuaderno19SepaXml.class.php";
require "funciones/SepaXml19.class.php";

$EMP = $_SESSION['DBEMP'];
$DAT = $_SESSION['DBDAT'] . $_SESSION['empresa'];
$conceptocobro = DameParametro('COBRO', '00');

$v = $_POST;

if (($v['entidadreceptora'] == '') or ($v['oficinareceptora'] == '')) {
    $sql = "select IDBanco,IDOficina from $EMP.empresas where IDEmpresa='" . $_SESSION['empresa'] . "';";
    $res = mysql_query($sql);
    $row = mysql_fetch_array($res);
    $v['entidadreceptora'] = $row['IDBanco'];
    $v['oficinareceptora'] = $row['IDOficina'];
}
if ($v['fecharemesa'] == '')
    $v['fecharemesa'] = date('dmy');
if ($v['fechacargo'] == '')
    $v['fechacargo'] = date('dmy');
if ($v['fecharecibos'] == '')
    $v['fecharecibos'] = date('dmY');

function Vacio($n) {
    return(str_repeat(" ", $n));
}

function Ceros($s, $n) { //Rellena a ceros por la izquierda
    return(str_pad($s, $n, "0", STR_PAD_LEFT));
}

function Rellena($s, $n) {//Rellena con espacios por la derecha
    $s = substr($s, 0, $n); //Primero recorto
    return(str_pad($s, $n, " ", STR_PAD_RIGHT)); //y luego le añado espacios
}

function Escribe($f, $r) {
    if (!fwrite($f, $r . "\n"))
        Mensaje("Error al escribir en el fichero de salida {$f}");;
}

function limpia($texto) {
    $texto = utf8_encode($texto);
    $array = array(
        'ª' => '.',
        'º' => '.',
        '&' => ' ',
        'nbsp;' => ' ',
        'Ñ' => 'NI',
        'ñ' => 'ni',
    );
    foreach ($array as $key => $letra) {
        $texto = str_replace($key, $letra, $texto);
    }
    return $texto;
}

function CreaRemesa($orde, $v, $recibos) {
    
    $total = 0;
    foreach ($recibos as $row) {
        $total += $row['importe'];
    }

    $idRemesa = $orde['Cif'] . date('Y-m-d') . "T" . date('H:i:s');
    $arrayRemesa = array(
        'header' => array(
            'id' => $idRemesa,
            'fecha' => date('Y-m-d') . "T" . date('H:i:s'),
            'fechaCargo' => "20" . substr($v['fechacargo'], 4, 2) . "-" . substr($v['fechacargo'], 2, 2) . "-" . substr($v['fechacargo'], 0, 2),
            'nRecibos' => count($recibos),
            'total' => $total,
            'razonSocial' => limpia($orde['RazonSocial']),
            'direccion1' => limpia($orde['Direccion']),
            'direccion2' => limpia($orde['CodigoPostal'] . " " . $orde['Poblacion']),
            'cif' => "    " . $orde['Sufijo'] . $orde['Cif'],
            'iban' => $orde['Iban'],
            'bic' => $orde['Bic'],
        ),
        'recibos' => $recibos,
    );

    return array(
        'ordenante' => $orde['RazonSocial'],
        'nRecibos' => count($recibos),
        'importe' => $total,
        'fichero' => SepaXml19::makeDocument("remesas/{$idRemesa}.xml", $arrayRemesa),
    );
}

switch ($v['accion']) {
    case 'Si':
        $error = "";
        if ($v['hacercopia'] == 'S') {//Hacer copia de seguridad previa de los datos comunes
            $basesdedatos = array($_SESSION['DBEMP'],);
            require "funciones/backup.php";
            //list($ok,$mensaje)=CopiaSeguridad($_SESSION['empresa'],'','E');
            //if (!$ok) $error="!!! NO SE HA REALIZADO LA COPIA DE SEGURIDAD DE LOS DATOS COMUNES CORRECTAMENTE !!!";
        }

        //VALIDACIONES
        $aux = str_pad($v['presentadornif'], 9, " ", STR_PAD_RIGHT);
        if ($aux == Vacio(9))
            $error = "Debe indicar el NIF del presentador";
        $aux = str_pad($v['presentadornombre'], 40, " ", STR_PAD_RIGHT);
        if ($aux == Vacio(40))
            $error = "Debe indicar el NOMBRE del presentador";
        if (ValidaFecha($v['fecharemesa']) > ValidaFecha($v['fechacargo']))
            $error = "La FECHA DE CARGO debe ser igual o superior a la FECHA REMESA";
        if (($v['desdeempresa'] == '') or ($v['hastaempresa'] == ''))
            $error = "Debe seleccionar las Empresas";
        if ($v['desdeempresa'] > $v['hastaempresa'])
            $error = "La empresa DESDE debe ser inferior a la HASTA";
        if (ValidaFecha($v['fecharecibos']) == '')
            $error = "La FECHA DE EMISION de los recibos no es correcta";

        $sql = "select IDBanco from $EMP.bancos_oficinas where IDBanco='" . $v['entidadreceptora'] . "' and IDOficina='" . $v['oficinareceptora'] . "';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        if ($row[0] == '')
            $error = "El Banco y/o Oficina Receptora no existen";
        if ($error == "") {

            //BUCLE PARA GESTIONAR CADA UNO DE LOS ORDENANTES (EMPRESAS)
            $sql = "select * from $EMP.empresas where IDEmpresa>='" . $v['desdeempresa'] . "' and IDEmpresa<='" . $v['hastaempresa'] . "' order by IDEmpresa;";
            $res = mysql_query($sql);
            while ($orde = mysql_fetch_array($res)) {
                if ($v['hacercopia'] == 'S') {//Hacer copia de seguridad previa de la empresa en curso
                    $basesdedatos = array($_SESSION['DBDAT'] . $orde['IDEmpresa'],);
                    require "funciones/backup.php";
                    //list($ok,$mensaje)=CopiaSeguridad($orde['IDEmpresa'],'','D');
                    //if (!$ok) Mensaje("!!! NO SE HA REALIZADO LA COPIA DE SEGURIDAD DE ".$orden['IDEmpresa']." ".$orde['RazonSocial']." CORRECTAMENTE !!!");
                }

                //RECORRO LOS RECIBOS DEL ORDENANTE EN CURSO.
                $tablas = $_SESSION['DBDAT'] . $orde['IDEmpresa'] . ".recibos as t1, $EMP.inquilinos as t2, " . $_SESSION['DBDAT'] . $orde['IDEmpresa'] . ".inmuebles as t3, $EMP.provincias as t4";
                $filtro = "Fecha='" . AlmacenaFecha($v['fecharecibos']) . "' and SUBSTRING(CuentaAbono,1,4)='" . $v['entidadreceptora'] . "' and SUBSTRING(CuentaCargo,1,4)<>'0000' and t1.IDRemesa=''";
                $filtro.="and t1.IDInquilino=t2.IDInquilino and t1.IDInmueble=t3.IDInmueble and t3.IDProvincia=t4.CODIGO";
                $sql = "select t1.*,t2.RazonSocial,t3.Direccion,t3.Poblacion,t3.CodigoPostal,t4.NOMBRE from $tablas where $filtro order by IDRecibo;";
                $res1 = mysql_query($sql);

                $recibos = array();
                while ($recibo = mysql_fetch_array($res1)) {
                    $recibos[] = array(
                        'numeroFactura' => $recibo['IDRecibo'],
                        'importe' => $recibo['Total'],
                        'idMandato' => $recibo['Mandato'],
                        'fechaMandato' => $recibo['FechaMandato'],
                        'bic' => $recibo['BicCargo'],
                        'iban' => $recibo['IbanCargo'],
                        'razonSocial' => limpia($recibo['RazonSocial']),
                        'direccion1' => limpia($recibo['Direccion']),
                        'direccion2' => limpia($recibo['Poblacion']) . " " . $recibo['CodigoPostal'] . " " . $recibo['NOMBRE'],
                        'pais' => 'ES',
                        'texto' => "Recibo " . $recibo['IDRecibo'] . " de fecha " . FechaEspaniol($recibo['Fecha']),
                    );

                    //RECORRER LOS LINEAS DE CADA RECIBO
                    $sql = "select t1.*,t2.Concepto,t2.Consumo,t2.CobroMediacion from " . $_SESSION['DBDAT'] . $orde['IDEmpresa'] . ".recibos_lineas as t1,$EMP.conceptos as t2
                        where t1.IDRecibo='" . $recibo['IDRecibo'] . "' and t1.IDConcepto=t2.IDConcepto and t1.IDConcepto<>'$conceptocobro' order by IDLinea;";
                    $res2 = mysql_query($sql);
                    $campo = "";
                    $i = 1;
                    $campo[$i] = substr(Rellena(DecodificaTexto($recibo['Direccion']), 40), 0, 40);
                    while ($lineas = mysql_fetch_array($res2)) {
                        $i++;
                        if ($lineas['CobroMediacion'] == 'S')
                            $tipo = "# ";
                        else
                            $tipo = "* ";
                        if ($lineas['Consumo'] == 'S') {
                            $campo[$i] = substr(Rellena(Vacio(4) . $tipo . $lineas['Concepto'] . " " . $lineas['Precio'] . " x", 40), 0, 40);
                            $i++;
                            $campo[$i] = substr(Rellena(Vacio(3) . $lineas['Unidades'] . Vacio(4) . "=" . str_pad($lineas['Importe'], 10, " ", STR_PAD_LEFT) . " Lect(" . $lineas['ValorAnterior'] . " " . $lineas['ValorActual'] . ")", 40), 0, 40);
                        } else {
                            $campo[$i] = substr(Rellena(Vacio(4) . $tipo . $lineas['Concepto'], 40), 0, 40);
                            $i++;
                            $campo[$i] = substr(Rellena(Vacio(4) . Vacio(4) . "=" . str_pad($lineas['Importe'], 10, " ", STR_PAD_LEFT), 40), 0, 40);
                        }
                    }

                    if ($recibo['Iva'] != 0) {
                        $i++;
                        $campo[$i] = substr(Rellena(Vacio(7) . $recibo['Iva'] . "% IVA s/" . $recibo['Base'] . "=*", 40), 0, 40);
                        $i++;
                        $campo[$i] = substr(Rellena(Vacio(8) . "=" . str_pad(round($recibo['Base'] * $recibo['Iva'] / 100, 2), 10, " ", STR_PAD_LEFT), 40), 0, 40);
                    }
                    if ($recibo['Retencion'] != 0) {
                        $i++;
                        $campo[$i] = substr(Rellena(Vacio(7) . $recibo['Iva'] . "% RET s/" . $recibo['Base'] . "=*", 40), 0, 40);
                        $i++;
                        $campo[$i] = substr(Rellena(Vacio(8) . "=" . str_pad(-1 * round($recibo['Base'] * $recibo['Retencion'] / 100, 2), 10, " ", STR_PAD_LEFT), 40), 0, 40);
                    }
                    if ($recibo['Recargo'] != 0) {
                        $i++;
                        $campo[$i] = substr(Rellena(Vacio(7) . $recibo['Iva'] . "% REC s/" . $recibo['Base'] . "=*", 40), 0, 40);
                        $i++;
                        $campo[$i] = substr(Rellena(Vacio(8) . "=" . str_pad(round($recibo['Base'] * $recibo['Recargo'] / 100, 2), 10, " ", STR_PAD_LEFT), 40), 0, 40);
                    }

                    $nregopc = ceil($i / 3); //Numero de registros opcionales que hay que crear
                    for ($j = 1; $j <= $nregopc; $j++) {
                        $n = ($j - 1) * 3 + 1;
                        $reg = "568" . $j . $orde['Cif'] . $orde['Sufijo'] . $inqui . $inmue . $campo[$n] . substr(Rellena($campo[$n + 1], 40), 0, 40) . substr(Rellena($campo[$n + 2], 40), 0, 40) . Vacio(14);
                        //Escribe($fp, $reg);
                    }

                    //Marcar el recibo con el ID de la remesa
                    //$actu = mysql_query("update " . $_SESSION['DBDAT'] . $orde['IDEmpresa'] . ".recibos set IDRemesa='$idremesa' where IDRecibo='" . $recibo['IDRecibo'] . "' limit 1;");
                    //if (!$actu)
                    //    Mensaje("NO SE HA ACTUALIZADO EL RECIBO " . $recibos['IDRecibo'] . " de la empresa " . $orde['IDEmpresa']);
                }

                if (count($recibos)) {
                    $resultado[] = CreaRemesa($orde, $v, $recibos);
                }
            }
            //FIN BUCLE ORDENANTE

            //Crear pagina de resumen
            $totalRecibos = $totalImporte = 0;
            $resumen = 
                    "<table width='60%' class='formularios' align='center' border=0>" .
                    "<tr height=60><td colspan=4>&nbsp</td></tr>" .
                    "<tr height=40 class='blancoazul'><th colspan=4 valign='middle'>RESUMEN GENERACION DE REMESA</th></tr>" .
                    "<tr><td>Empresa</td><td>N. Recibos</td><td>Importe</td><td>Fichero SEPA</td></tr>";
            foreach ($resultado as $row) {
                $resumen .= "<tr><td>{$row['ordenante']}</td><td>{$row['nRecibos']}</td><td>{$row['importe']}</td><td><a href='{$row['fichero']}' target='_blank'>{$row['fichero']}</a></td></tr>";
                $totalRecibos += $row['nRecibos'];
                $totalImporte += $row['importe'];
            }
            $resumen .= "<tr><td>TOTAL</td><td>{$totalRecibos}</td><td>{$totalImporte}</td><td></td></tr></table>";

            echo $resumen;

            break;
        } else
            Mensaje($error);

    case '':
        ?>
        <table align="center"><tr height="40"><td></td></tr></table>

        <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="generarremesa">

            <table class="combofamilias" align="center" border="0">
                <tr><th class="blancoazul" colspan="2">Generar Remesa</th></tr>

                <tr><th colspan="2">Datos del Presentador</th></tr>
                <tr><td colspan="2">NIF/CIF:&nbsp;<input name="presentadornif" type="text" value="<?echo $v['presentadornif'];?>" size="9" maxlength="9" class="formularios"></td></tr>
                <tr><td colspan="2">Nombre:&nbsp;<input name="presentadornombre" type="text" value="<?echo $v['presentadornombre'];?>" size="40" maxlength="40" class="formularios"></td></tr>
                <tr><td colspan="2">
                        Entidad/Oficina Receptora:&nbsp;
                        <input name="entidadreceptora" type="text" value="<?echo $v['entidadreceptora'];?>" size="4" maxlength="4" class="formularios">
                        <input name="oficinareceptora" type="text" value="<?echo $v['oficinareceptora'];?>" size="4" maxlength="4" class="formularios">
                    </td>
                </tr>
                <tr><td colspan="2">
                        Fecha Remesa (ddmmaa):&nbsp;<input name="fecharemesa" value="<?echo $v['fecharemesa'];?>" type="text" size="6" maxlength="6" class="formularios">&nbsp
                        Fecha Cargo:&nbsp;<input name="fechacargo" value="<?echo $v['fechacargo'];?>" type="text" size="6" maxlength="6" class="formularios">
                    </td></tr>

                <tr height="20"><td colspan="2"></td></tr>
                <tr><td colspan="2">Desde Empresa:&nbsp;<?php Desplegable('desdeempresa', $EMP . '.empresas', 'IDEmpresa', 'CONCAT(IDEmpresa,"-",RazonSocial)', 'IDEmpresa ASC', '', '', 'formularios', ''); ?></td></tr>
                <tr><td colspan="2">Hasta Empresa:&nbsp;<?php Desplegable('hastaempresa', $EMP . '.empresas', 'IDEmpresa', 'CONCAT(IDEmpresa,"-",RazonSocial)', 'IDEmpresa DESC', '', '', 'formularios', ''); ?></td></tr>
                <tr><td colspan="2">Fecha de Emisi&oacute;n Recibos (ddmmaaaa):&nbsp;<input name="fecharecibos" value="<?echo $v['fecharecibos'];?>" type="text" size="8" maxlength="8" class="formularios"></td></tr>

                <tr height="30"><td colspan="2"></td></tr>
                <tr><td colspan="2">
                        Hacer copia de Seguridad previa:&nbsp;<?php DesplegableSN('hacercopia', 'S', 'formularios'); ?>
                    </td></tr>
                <tr height="30"><td colspan="2"></td></tr>
                <tr valign="top">
                    <td align="right">Desea generar la remesa?&nbsp;<input name="accion" value="Si" type="submit" class="formularios"></td>
                <script language="JavaScript" type="text/javascript">
                    document.form.presentadornif.focus();
                </script>
        </form>
        <form name="form" action="contenido.php" method="POST">
            <td align="left">
                <input name="c" type="hidden" value="inicial">
                <input name="accion" value="No" type="submit" class="formularios">
            </td>
        </tr>
        </table>
        </form>
        <?php
        break;
}
?>
