<?php

/**
 * GENERAR EL CUADERNO 19 EN FORMATO XML SEGUN SEPA
 *
 * @author Sergio Pérez <sergio.perez@albatronic.com>
 * @version 1.0 20-02-2014
 * 
 * $array = array(
    'header' => array(
        'id' => 'S1914/11/20140215',
        'fecha' => date('Y-m-d')."T".date('H:i:s'),
        'fechaCargo' => '2014-02-28',
        'nRecibos' => 1,
        'total' => '234.58',
        'razonSocial' => 'Informatica Albatronic, SL',
        'direccion1' => 'Avd. Blas Otero, 10 Local 1',
        'direccion2' => '18200 Maracena Granada',
        'cif' => 'ES00B18426684',
        'iban' => 'ESxx21002497190210004796',
        'bic' => 'CAIXESBB',
    ),
    'recibos' => array(
        0 => array(
            'numeroFactura' => 'FA001',
            'importe' => '1500.23',
            'idMandato' => 'mandato1',
            'fechaMandato' => '2013-02-01',
            'bic' => 'BIC001',
            'iban' => 'ESXXBBBBOOOODDCCCCCCCCCC',
            'razonSocial' => 'PRIMER CLIENTE, SL',
            'direccion1' => 'calle',
            'direccion2' => 'poblacion',
            'pais' => 'ES',
            'texto' => 'Factura N. FA001 10-01-2014 1500.23€',         
        ),
        1 => array(
            'numeroFactura' => 'FA002',
            'importe' => '500.99',
            'idMandato' => 'mandato2',
            'fechaMandato' => '2013-02-02',
            'bic' => 'BIC002',
            'iban' => 'ESXXBBBBOOOODDCCCCCCCCCC',
            'razonSocial' => 'SEGUNDO CLIENTE, SL',
            'direccion1' => 'calle',
            'direccion2' => 'poblacion',
            'pais' => 'EN',
            'texto' => 'Factura N. FA002 11-01-2014 500.99€',              
        ),
    ),
);
 */
class SepaXml19 {

    var $xmlStr = "";

    function makeDocument($ficheroXml, array $info) {
        
        $xml = $this->getDocument($info);
        $fp = @fopen($ficheroXml, "w");
        if ($fp) {
            fwrite($fp, $xml);
            fclose($fp);
            $ok = true;
        } else {
            $ficheroXml = "";
        }
        
        return $ficheroXml;
    }

     function getDocument(array $info) {
        $this->put("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>");
        $this->put("<Document xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02\">");
        $this->put("<CstmrDrctDbtInitn>", 1);
        $this->getHeader($info['header']);
        $this->getRecibos($info);
        $this->put("</CstmrDrctDbtInitn>", 1);
        $this->put("</Document>");

        return $this->xmlStr;
    }

    /**
     * Genera la cabecera
     * @param array $header La información de la cabecera
     */
     function getHeader(array $header) {
        $this->put("<GrpHdr>", 2);
        $this->put("<MsgId>{$header['id']}</MsgId>", 3);
        $this->put("<CreDtTm>{$header['fecha']}</CreDtTm>", 3);
        $this->put("<NbOfTxs>{$header['nRecibos']}</NbOfTxs>", 3);
        $this->put("<CtrlSum>{$header['total']}</CtrlSum>", 3);
        $this->put("<InitgPty>", 3);
        $this->put("<Nm>{$header['razonSocial']}</Nm>", 4);
        $this->put("<Id>", 4);
        $this->put("<OrgId>", 5);
        $this->put("<Othr>", 6);
        $this->put("<Id>{$header['cif']}</Id>", 7);
        $this->put("</Othr>", 6);
        $this->put("</OrgId>", 5);
        $this->put("</Id>", 4);
        $this->put("</InitgPty>", 3);
        $this->put("</GrpHdr>", 2);
    }

     function getRecibos(array $info) {
        
        $header = $info['header'];
        
        $this->put("<PmtInf>", 2);
        $this->put("<PmtInfId>{$header['id']}</PmtInfId>", 3);
        $this->put("<PmtMtd>DD</PmtMtd>", 3);
        $this->put("<NbOfTxs>{$header['nRecibos']}</NbOfTxs>", 3);
        $this->put("<CtrlSum>{$header['total']}</CtrlSum>", 3);
        $this->put("<PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrm><Cd>CORE</Cd></LclInstrm><SeqTp>RCUR</SeqTp></PmtTpInf>", 3);
        $this->put("<ReqdColltnDt>{$header['fechaCargo']}</ReqdColltnDt>", 3);

        $this->put("<Cdtr>", 3);
        $this->put("<Nm>{$header['razonSocial']}</Nm>", 4);
        $this->put("<PstlAdr>", 4);
        $this->put("<Ctry>ES</Ctry>", 5);
        $this->put("<AdrLine>{$header['direccion1']}</AdrLine>", 5);
        $this->put("<AdrLine>{$header['direccion2']}</AdrLine>", 5);
        $this->put("</PstlAdr>", 4);
        $this->put("</Cdtr>", 3);

        $this->put("<CdtrAcct>", 3);
        $this->put("<Id><IBAN>{$header['iban']}</IBAN></Id>", 4);
        $this->put("<Ccy>EUR</Ccy>", 4);
        $this->put("</CdtrAcct>", 3);

        $this->put("<CdtrAgt>", 3);
        $this->put("<FinInstnId><BIC>{$header['bic']}</BIC></FinInstnId>", 4);
        $this->put("</CdtrAgt>", 3);

        $this->put("<ChrgBr>SLEV</ChrgBr>", 3);

        $this->put("<CdtrSchmeId>", 3);
        $this->put("<Id>", 4);
        $this->put("<PrvtId>", 5);
        $this->put("<Othr>", 6);
        $this->put("<Id>{$header['cif']}</Id>", 7);
        $this->put("<SchmeNm>", 7);
        $this->put("<Prtry>SEPA</Prtry>", 8);
        $this->put("</SchmeNm>", 7);
        $this->put("</Othr>", 6);
        $this->put("</PrvtId>", 5);
        $this->put("</Id>", 4);
        $this->put("</CdtrSchmeId>", 3);

        foreach($info['recibos'] as $recibo) {
            $this->getRecibo($recibo);
        }
            
        $this->put("</PmtInf>", 2);
    }

     function getRecibo(array $recibo) {
        
        $this->put("<DrctDbtTxInf>",3);
        $this->put("<PmtId>",4);
        $this->put("<EndToEndId>{$recibo['numeroFactura']}</EndToEndId>",5);      
        $this->put("</PmtId>",4);
        $this->put("<InstdAmt Ccy=\"EUR\">{$recibo['importe']}</InstdAmt>",4);
        
        $this->put("<DrctDbtTx>",4);
        $this->put("<MndtRltdInf>",5);
        $this->put("<MndtId>{$recibo['idMandato']}</MndtId>",6);
        $this->put("<DtOfSgntr>{$recibo['fechaMandato']}</DtOfSgntr>",6);
        $this->put("<AmdmntInd>false</AmdmntInd>",6);
        $this->put("</MndtRltdInf>",5);
        $this->put("</DrctDbtTx>",4);
        
        $this->put("<DbtrAgt>",4);
        $this->put("<FinInstnId>",5);
        $this->put("<BIC>{$recibo['bic']}</BIC>",6);
        $this->put("</FinInstnId>",5);
        $this->put("</DbtrAgt>",4);
        
        $this->put("<Dbtr>",4);
        $this->put("<Nm>{$recibo['razonSocial']}</Nm>",4);
        $this->put("<PstlAdr>",4);
        $this->put("<Ctry>{$recibo['pais']}</Ctry>",5);
        $this->put("<AdrLine>{$recibo['direccion1']}</AdrLine>",5);
        $this->put("<AdrLine>{$recibo['direccion2']}</AdrLine>",5);
        $this->put("</PstlAdr>",4);
        $this->put("</Dbtr>",4);
        
        $this->put("<DbtrAcct>",4);
        $this->put("<Id>",5);
        $this->put("<IBAN>{$recibo['iban']}</IBAN>",6);
        $this->put("</Id>",5);
        $this->put("</DbtrAcct>",4);
        
        $this->put("<Purp>",4);
        $this->put("<Cd>TRAD</Cd>",5);
        $this->put("</Purp>",4);
        
        $this->put("<RmtInf>",4);
        $this->put("<Ustrd>{$recibo['texto']}</Ustrd>",4);
        $this->put("</RmtInf>",4);
        
        $this->put("</DrctDbtTxInf>",3);
    }
    
     function put($texto, $jerarquia = 0) {
        $linea = str_repeat(" ", $jerarquia * 2) . $texto . "\n";
        $this->xmlStr .= $linea;
    }

}
