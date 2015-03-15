<?php

/**
 * Clase para generar el CUADERNO 19 SEPA EN FORMATO XML
 *
 * @author Sergio Pérez <sergio.perez@albatronic.com>
 * @copyright Informática ALBATRONIC, SL
 * @since 01-04-2014
 *
 */
class Cuaderno19SepaXml {

    static $parametros;
    static $idRemesa;

    public function __construct($parametros) {
        self::$parametros = $parametros;
    }

    static function makeRemesa($parametros, $filtro) {

        self::$parametros = $parametros;

        $ordenante = new PcaeEmpresas($_SESSION['emp']);

        if (self::$parametros['agrupar']) {
            $recibos = self::RecibosAgrupados($filtro);
        } else {
            $recibos = self::RecibosIndividuales($filtro);
        }

        $fecha = new Fecha($parametros['fechaCobro']);
        $fechaCargo = $fecha->getaaaammdd();
        self::$idRemesa = $ordenante->getCif() . date('Y-m-d') . "T" . date('H:i:s');

        $arrayRemesa = array(
            'header' => array(
                'id' => self::$idRemesa,
                'fecha' => date('Y-m-d') . "T" . date('H:i:s'),
                'fechaCargo' => $fechaCargo,
                'nRecibos' => $recibos['nRecibos'],
                'total' => $recibos['importeTotal'],
                'razonSocial' => $ordenante->getRazonSocial(),
                'direccion1' => $ordenante->getDireccion(),
                'direccion2' => $ordenante->getCodigoPostal() . " " . $ordenante->getIdMunicipio()->getMunicipio() . " " . $ordenante->getIdProvincia()->getProvincia(),
                'cif' => $ordenante->getCif(),
                'iban' => $ordenante->getIban(),
                'bic' => $ordenante->getBic(),
            ),
            'recibos' => $recibos['recibos'],
        );

        self::escribeLog($arrayRemesa);

        return SepaXml19::makeDocument("docs/docs{$_SESSION['emp']}/remesas/" . self::$idRemesa . ".xml", $arrayRemesa);
    }

    static function escribeLog($arrayRemesa) {

        $log[self::$idRemesa] = $arrayRemesa;

        $archivo = "docs/docs{$_SESSION['emp']}/remesas/log.yml";
        if (!file_exists($archivo))
            $fp = fopen($archivo, "w");
        else
            $fp = fopen($archivo, "a");

        $yml = sfYaml::dump($log,4);

        fwrite($fp, $yml);
        fclose($fp);
    }

    /**
     * GENERA UN RECIBO BANCARIO POR CADA RECIBO DE CLIENTE    
     */
    static function RecibosIndividuales($filtro) {

        $recibos = new RecibosClientes();
        $clientes = new Clientes();
        $tablaRecibos = $recibos->getDataBaseName() . "." . $recibos->getTableName();
        $tablaClientes = $clientes->getDataBaseName() . "." . $clientes->getTableName();

        $em = new EntityManager($recibos->getConectionName());
        if ($em->getDbLink()) {
            $filtro .= " and (Remesar='1') and (CHAR_LENGTH(r.Iban)>23) and (r.Iban<>'ES8200000000000000000000') and (r.Importe>0)";
            $query = "select r.IDRecibo "
                    . "from {$tablaRecibos} as r "
                    . "left join {$tablaClientes} as c on r.IDCliente=c.IDCliente "
                    . "where {$filtro} "
                    . "order by c.RazonSocial,r.Vencimiento ASC";
            $em->query($query);
            $rows = $em->fetchResult();
        }
        unset($recibos);

        $total = 0;

        foreach ($rows as $row) {
            $recibo = new RecibosClientes($row['IDRecibo']);
            $cliente = $recibo->getIDCliente();
            $total += $recibo->getImporte();
            $recibos[] = array(
                'numeroFactura' => $recibo->getIDFactura()->getNumeroFactura(),
                'importe' => $recibo->getImporte(),
                'idMandato' => $recibo->getMandato(),
                'fechaMandato' => $recibo->getFechaMandato('aaaammdd'),
                'bic' => ($recibo->getBic() == '') ? "BBBBESPP" : $recibo->getBic(),
                'iban' => $recibo->getIban(),
                'razonSocial' => $cliente->getRazonSocial(),
                'direccion1' => $cliente->getDireccion(),
                'direccion2' => $cliente->getIDPoblacion()->getMunicipio() . " " . $cliente->getCodigoPostal() . " " . $cliente->getIDProvincia()->getProvincia(),
                'pais' => $cliente->getIDPais()->getCodigo(),
                'texto' => "Factura N. {$recibo->getIDFactura()->getNumeroFactura()} {$recibo->getIDFactura()->getFecha()} {$recibo->getImporte()}",
            );
        }

        unset($cliente);
        unset($recibo);

        return array(
            'nRecibos' => count($rows),
            'importeTotal' => $total,
            'recibos' => $recibos,
        );
    }

    static function RecibosAgrupados($filtro) {

        $recibos = new RecibosClientes();
        $clientes = new Clientes();
        $tablaRecibos = $recibos->getDataBaseName() . "." . $recibos->getTableName();
        $tablaClientes = $clientes->getDataBaseName() . "." . $clientes->getTableName();

        $em = new EntityManager($recibos->getConectionName());
        if ($em->getDbLink()) {
            $filtro .= " and (Remesar='1') and (CHAR_LENGTH(r.Iban)>23) and (r.Iban<>'ES8200000000000000000000')";
            $query = "select r.IDCliente,"
                    . "sum(r.Importe) importe,"
                    . "r.Iban iban, "
                    . "r.Bic bic, "
                    . "r.Mandato idMandato, "
                    . "r.FechaMandato fechaMandato "
                    . "from {$tablaRecibos} as r "
                    . "left join {$tablaClientes} as c on r.IDCliente=c.IDCliente "
                    . "where {$filtro} "
                    . "group by r.IDCliente,r.Iban "
                    . "having sum(r.Importe)>0 "
                    . "order by c.RazonSocial,r.Vencimiento ASC";
            $em->query($query);
            $rows = $em->fetchResult();
        }

        unset($em);
        unset($recibos);
        unset($clientes);

        $total = 0;

        foreach ($rows as $row) {
            $cliente = new Clientes($row['IDCliente']);
            $total += $row['importe'];
            $recibos[] = array(
                'numeroFactura' => 'Varias',
                'importe' => $row['importe'],
                'idMandato' => $row['idMandato'],
                'fechaMandato' => $row['fechaMandato'],
                'bic' => ($row['bic'] == '') ? "BBBBESPP" : $row['bic'],
                'iban' => $row['iban'],
                'razonSocial' => $cliente->getRazonSocial(),
                'direccion1' => $cliente->getDireccion(),
                'direccion2' => $cliente->getIDPoblacion()->getMunicipio() . " " . $cliente->getCodigoPostal() . " " . $cliente->getIDProvincia()->getProvincia(),
                'pais' => $cliente->getIDPais()->getCodigo(),
                'texto' => "Varias Facturas",
            );
        }

        unset($cliente);

        return array(
            'nRecibos' => count($rows),
            'importeTotal' => $total,
            'recibos' => $recibos,
        );
    }

}

?>
