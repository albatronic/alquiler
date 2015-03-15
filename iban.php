<?php
error_reporting(E_ALL);

/**
 * ACTUALIZA EL IBAN Y EL BIC DE LOS INQUILINOS Y DE LOS RECIBOS
 */
$i = -1;
$vconect = array();
if (file_exists("conexion.php")) {
    $f = fopen("conexion.php", "r");
    $kk = fgets($f, 4096); //Me salto la primera lÍnea = '<?php'
    while (!feof($f) and ($i < 5)) {
        // Leo línea a línea y quito los 2 primeros caracteres (//) y los 2 Últimos caracteres de la línea (CR+LF)
        $i++;
        $vconect[$i] = fgets($f, 4096);
        $vconect[$i] = substr($vconect[$i], 2, strlen($vconect[$i]) - 4);
    }
    fclose($f);
} else {
    echo "FALTAN LOS PARAMETROS DE CONEXION.";
    exit;
}

$_SESSION['SERVIDORDB'] = $vconect[0];
$_SESSION['USUARIODB'] = $vconect[1];
$_SESSION['PASSWORDDB'] = $vconect[2];
$_SESSION['DBEMP'] = $vconect[3];
$_SESSION['DBDAT'] = $vconect[4];

function iban($cc, $codigoPais = "ES") {
    $dividendo = $cc . (ord($codigoPais[0]) - 55) . (ord($codigoPais[1]) - 55) . '00';
    $digitoControl = 98 - bcmod($dividendo, '97');
    if (strlen($digitoControl) == 1) {
        $digitoControl = '0' . $digitoControl;
    }
    return $codigoPais . $digitoControl . $cc;
}

/**
 * Actualizar empresas
 */
$dbLink = mysql_connect($vconect[0], $vconect[1], $vconect[2]);
if ($dbLink) {
    // Empresas
    $query = "select IDEmpresa,IDBanco,IDOficina,Digito,Cuenta from alquiler_empresas.empresas";
    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)) {
        $empresas[] = $row['IDEmpresa'];
        $cc = $row['IDBanco'] . $row['IDOficina'] . $row['Digito'] . $row['Cuenta'];
        $iban = iban($cc);
        $query = "update alquiler_empresas.empresas set Iban='{$iban}' where IDEmpresa='{$row['IDEmpresa']}'";
        echo $query, "<br/>";
        mysql_query($query);
    }

    // Inquilinos
    $query = "select IDInquilino,IDBanco,IDOficina,Digito,Cuenta from alquiler_empresas.inquilinos";
    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)) {
        $cc = $row['IDBanco'] . $row['IDOficina'] . $row['Digito'] . $row['Cuenta'];
        $iban = iban($cc);
        $query = "update alquiler_empresas.inquilinos set Iban='{$iban}', Bic='BBBBESPP', Mandato='{$row['IDInquilino']}', FechaMandato='2009-10-31' where IDInquilino='{$row['IDInquilino']}'";
        //echo $query, "<br/>";
        mysql_query($query);
    }

    // Inmuebles y Recibos
    foreach ($empresas as $empresa) {
        $connectID = mysql_select_db("alquiler_gestion{$empresa}", $dbLink);
        if ($connectID) {

            // Inmuebles
            $query = "select IDInmueble,IDBanco,IDOficina,Digito,Cuenta from alquiler_gestion{$empresa}.inmuebles";
            $result = mysql_query($query);
            while ($row = mysql_fetch_assoc($result)) {
                $cc = $row['IDBanco'] . $row['IDOficina'] . $row['Digito'] . $row['Cuenta'];
                $iban = iban($cc);
                $query = "update alquiler_gestion{$empresa}.inmuebles set Iban='{$iban}' where IDInmueble='{$row['IDInmueble']}'";
                echo $query, "<br/>";
                mysql_query($query);
            }

            // Recibos
            $query = "select IDRecibo,CuentaCargo,CuentaAbono from alquiler_gestion{$empresa}.recibos";
            $result = mysql_query($query);
            while ($row = mysql_fetch_assoc($result)) {
                $ibanCargo = iban($row['CuentaCargo']);
                $ibanAbono = iban($row['CuentaAbono']);
                $query = "update alquiler_gestion{$empresa}.recibos set IbanCargo='{$ibanCargo}', IbanAbono='{$ibanAbono}' where IDRecibo='{$row['IDRecibo']}'";
                echo $query, "<br/>";
                mysql_query($query);
            }
        }
    }
} else
    die("Error conexion DB");

