<?php
if ($_SESSION['iu'] == '')
    exit;
$idagente = $_SESSION['iu'];
$esadm = $_SESSION['esadm'];

require "conecta.php";
require "funciones/textos.php";
require "funciones/desplegable.php";
require "funciones/fechas.php";
require "funciones/recibos.php";

//RECOGIDA DE PARAMETROS
//-----------------------------------------------------------------------------
$pagina = $_GET['pagina'];
if (!isset($pagina)) {
    $pagina = $_POST['pagina'];
    if (!isset($pagina))
        $pagina = 1;
}

$accion = $_POST['Accion'];
if ($accion == '')
    $accion = $_GET['Accion'];
if ($accion == '')
    $accion = "Consulta";

$orden = $_POST['orden'];
if ($orden == '')
    $orden = $_GET['orden'];
if ($orden == '')
    $orden = "IDInquilino";

$columna = $_POST['columna'];
if ($columna == '')
    $columna = $_GET['columna'];
if ($columna == '')
    $columna = 'IDInquilino';

$valor = $_POST['valor'];
if ($valor == '')
    $valor = $_GET['valor'];
if ($valor == '')
    $valor = 'I?';

$c = str_replace("?", "%", $valor);
if ($c == '')
    $c = "1";
else
    $c = $columna . " like '$c%'";

$filtro = "(" . $c . ")";
$parametros = "columna=$columna&valor=$valor&orden=$orden";


//Parametros de formulario de Mantenimiento
//-----------------------------------------
$campos['idinquilino'] = $_POST['IDInquilino'];
if ($campos['idinquilino'] == '')
    $campos['idinquilino'] = $_GET['IDInquilino'];
$campos['razonsocial'] = $_POST['RazonSocial'];
$campos['nombrecomercial'] = $_POST['NombreComercial'];
$campos['cif'] = $_POST['Cif'];
$campos['direccion'] = $_POST['Direccion'];
$campos['poblacion'] = $_POST['Poblacion'];
$campos['idprovincia'] = $_POST['IDProvincia'];
$campos['codigopostal'] = $_POST['CodigoPostal'];
$campos['telefono'] = $_POST['Telefono'];
$campos['fax'] = $_POST['Fax'];
$campos['movil'] = $_POST['Movil'];
$campos['email'] = $_POST['EMail'];
$campos['web'] = $_POST['Web'];
$campos['ccontable'] = $_POST['CContable'];
$campos['idbanco'] = $_POST['IDBanco'];
$campos['idoficina'] = $_POST['IDOficina'];
$campos['digito'] = $_POST['Digito'];
$campos['cuenta'] = $_POST['Cuenta'];
$campos['recargo'] = $_POST['Recargo'];
$campos['observaciones'] = $_POST['Observaciones'];
$campos['avisos'] = $_POST['Avisos'];
$campos['factualizacion'] = $_POST['FActualizacion'];
$campos['vigente'] = $_POST['Vigente'];
$campos['fechanacimiento'] = $_POST['FechaNacimiento'];
$campos['iban'] = $_POST['Iban'];
$campos['bic'] = $_POST['Bic'];
$campos['mandato'] = $_POST['Mandato'];
$campos['fechaMandato'] = $_POST['FechaMandato'];
?>

<table id="FORMULARIO_SELECCION" width="100%" align="center" valign="top" bgcolor="#CCCCCC">
    <tr><td align="center" class="boxtitlewhite">CONSULTA DE INQUILINOS</td></tr>
    <tr><td>
            <table align="center" class="formularios" BORDER="0">
                <tr>
                <form name="Consulta" action="contenido.php" method="post">
                    <input name="c" value="inquilinos" type="hidden">
                    <input name="Accion" value="Consulta" type="hidden">
                    <td align="center">Orden:
                        <select name="orden" class="ComboFamilias" onchange="submit();">
                            <option value="IDInquilino" <?if($orden=="IDInquilino") echo "selected";?>>Codigo</option>
                            <option value="RazonSocial" <?if($orden=="RazonSocial") echo "selected";?>>Nombre</option>
                        </select>
                    </td>
                    <td align="center">Buscar por:
                        <select name="columna" class="ComboFamilias">
                            <option value="RazonSocial" <?php if ($columna == 'RazonSocial') echo "SELECTED"; ?>>Raz&oacute;n Social</option>
                            <option value="IDInquilino" <?php if ($columna == 'IDInquilino') echo "SELECTED"; ?>>C&oacute;digo</option>
                            <option value="NombreComercial" <?php if ($columna == 'NombreComercial') echo "SELECTED"; ?>>Nombre Comercial</option>
                            <option value="Direccion" <?php if ($columna == 'Direccion') echo "SELECTED"; ?>>Domicilio</option>
                            <option value="Poblacion" <?php if ($columna == 'Poblacion') echo "SELECTED"; ?>>Poblaci&oacute;n</option>
                            <option value="Telefono" <?php if ($columna == 'Telefono') echo "SELECTED"; ?>>Telefono</option>
                            <option value="Movil" <?php if ($columna == 'Movil') echo "SELECTED"; ?>>M&oacute;vil</option>
                            <option value="Cif" <?php if ($columna == 'Cif') echo "SELECTED"; ?>>DNI/CIF</option>				
                        </select>
                    </td>
                    <td>
                        Valor(?):<input name="valor" value="<?php echo $valor; ?>" type="text" size="40" maxlength="50" class="formularios">
                    </td>
                    <td align="center">
                        <input type="image" img src="images\lupa.png">
                    </td>
                </form>
    </tr>
</table>
</td></tr>
<?php Subrrayado(1); ?>
</table>

<?PHP
//CONTROL DE LA ACCION A REALIZAR
//-------------------------------------------------------
switch ($accion) {
    case 'Limpiar':
        Limpia();
        break;

    case 'Guardar':
        $error = '';
        $error = ValidaCC($campos['idbanco'], $campos['idoficina'], $campos['digito'], $campos['cuenta']);
        if (strlen($error) == 2) {
            $campos['digito'] = $error;
            $error = "";
        }
        if ($campos['razonsocial'] == '')
            $error = "Debe indicar la Raz&oacute;n Social del inquilino.";
        if ($campos['idprovincia'] == '')
            $error = "Debe indicar una provincia.";
        if ($campos['recargo'] == '')
            $campos['recargo'] = 0;
        if ($error != '')
            Mensaje($error);
        else {
            if ($campos['vigente'] != '')
                $campos['vigente'] = '1';
            else
                $campos['vigente'] = '0';
            $sql = "update inquilinos set RazonSocial='" . CodificaTexto($campos['razonsocial']) .
                    "', NombreComercial='" . CodificaTexto($campos['nombrecomercial']) .
                    "', Cif='" . $campos['cif'] .
                    "', Direccion='" . CodificaTexto($campos['direccion']) .
                    "', Poblacion='" . $campos['poblacion'] .
                    "', IDProvincia='" . $campos['idprovincia'] .
                    "', CodigoPostal='" . $campos['codigopostal'] .
                    "', Telefono='" . $campos['telefono'] .
                    "', Fax='" . $campos['fax'] .
                    "', Movil='" . $campos['movil'] .
                    "', EMail='" . $campos['email'] .
                    "', Web='" . $campos['web'] .
                    "', CContable='" . $campos['ccontable'] .
                    "', IDBanco='" . $campos['idbanco'] .
                    "', IDOficina='" . $campos['idoficina'] .
                    "', Digito='" . $campos['digito'] .
                    "', Cuenta='" . $campos['cuenta'] .
                    "', RecargoEqu='" . $campos['recargo'] .
                    "', Observaciones='" . CodificaTexto($campos['observaciones']) .
                    "', Avisos='" . CodificaTexto($campos['avisos']) .
                    "', FActualizacion='" . date("Y-m-d H:i:s") .
                    "', Vigente='" . $campos['vigente'] .
                    "', FechaNacimiento='" . AlmacenaFecha($campos['fechanacimiento']) .
                    "', Iban='" . $campos['iban'] .
                    "', Bic='" . $campos['bic'] .
                    "', Mandato='" . $campos['mandato'] .
                    "', FechaMandato='" . AlmacenaFecha($campos['fechaMandato']) .
                    "' where IDInquilino='" . $campos['idinquilino'] . "'";
            $res = mysql_query($sql);
            if (!$res)
                Mensaje("No se han podido actualizar los datos. Inténtelo de nuevo");
        }
        break;

    case 'Borrar':
        if (BorrarInquilino($campos['idinquilino']))
            Limpia();
        break;

    case 'Crear':
        $error = '';
        $error = ValidaCC($campos['idbanco'], $campos['idoficina'], $campos['digito'], $campos['cuenta']);
        if (strlen($error) == 2) {
            $campos['digito'] = $error;
            $error = "";
        }
        if ($campos['razonsocial'] == '')
            $error = "Debe indicar la Raz&oacute;n Social del inquilino.";
        if ($campos['idprovincia'] == '')
            $error = "Debe indicar una provincia.";
        if ($error != '')
            Mensaje($error);
        else {
            if ($campos['idinquilino'] == '') {
                $res = mysql_query("select MAX(IDInquilino) from inquilinos");
                if ($res)
                    $cod = mysql_fetch_array($res);
                else
                    $cod[0] = 0;
                $campos['idinquilino'] = $cod[0] + 1;
            }
            $campos['ccontable'] = $campos['idinquilino'];
            if ($campos['nombrecomercial'] == '')
                $campos['nombrecomercial'] = $campos['razonsocial'];
            $campos['vigente'] = '1';
            if ($campos['recargo'] == '')
                $campos['recargo'] = 0;
            $valores = "'" . $campos['idinquilino'] . "','"
                    . CodificaTexto($campos['razonsocial']) . "','"
                    . CodificaTexto($campos['nombrecomercial']) . "','"
                    . $campos['cif'] . "','"
                    . CodificaTexto($campos['direccion']) . "','"
                    . $campos['poblacion'] . "','"
                    . $campos['idprovincia'] . "','"
                    . $campos['codigopostal'] . "','"
                    . $campos['telefono'] . "','"
                    . $campos['fax'] . "','"
                    . $campos['movil'] . "','"
                    . $campos['email'] . "','"
                    . $campos['web'] . "','"
                    . $campos['ccontable'] . "','"
                    . $campos['idbanco'] . "','"
                    . $campos['idoficina'] . "','"
                    . $campos['digito'] . "','"
                    . $campos['cuenta'] . "','"
                    . $campos['recargo'] . "','"
                    . CodificaTexto($campos['observaciones']) . "','"
                    . CodificaTexto($campos['avisos']) . "','"
                    . date("Y-m-d H:i:s") . "','"
                    . $campos['vigente'] . "','"
                    . AlmacenaFecha($campos['fechanacimiento']) . "','"
                    . $campos['iban'] . "','"
                    . $campos['bic'] . "','"
                    . $campos['mandato'] . "','"
                    . AlmacenaFecha($campos['fechaMandato']) . "'";

            $sql = "INSERT INTO inquilinos VALUES (" . $valores . ")";
            $res = mysql_query($sql);
            if (!$res)
                Mensaje("No se ha podido crear. Inténtelo de nuevo.");
        }
        break;

    case 'Editar':
        $res = mysql_query("select * from inquilinos where (IDInquilino='" . $campos['idinquilino'] . "')");
        $row = mysql_fetch_array($res);
        $campos['razonsocial'] = DecodificaTexto($row['RazonSocial']);
        $campos['nombrecomercial'] = DecodificaTexto($row['NombreComercial']);
        $campos['cif'] = $row['Cif'];
        $campos['direccion'] = DecodificaTexto($row['Direccion']);
        $campos['poblacion'] = $row['Poblacion'];
        $campos['idprovincia'] = $row['IDProvincia'];
        $campos['codigopostal'] = $row['CodigoPostal'];
        $campos['telefono'] = $row['Telefono'];
        $campos['fax'] = $row['Fax'];
        $campos['idbanco'] = $row['IDBanco'];
        $campos['idoficina'] = $row['IDOficina'];
        $campos['digito'] = $row['Digito'];
        $campos['cuenta'] = $row['Cuenta'];
        $campos['ccontable'] = $row['CContable'];
        $campos['observaciones'] = DecodificaTexto($row['Observaciones']);
        $campos['web'] = $row['Web'];
        $campos['email'] = $row['EMail'];
        $campos['recargo'] = $row['RecargoEqu'];
        $campos['movil'] = $row['Movil'];
        $campos['factualizacion'] = $row['FActualizacion'];
        $campos['avisos'] = DecodificaTexto($row['Avisos']);
        $campos['vigente'] = $row['Vigente'];
        $campos['fechanacimiento'] = FechaEspaniol($row['FechaNacimiento']);
        $campos['iban'] = $row['Iban'];
        $campos['bic'] = $row['Bic'];
        $campos['mandato'] = $row['Mandato'];
        $campos['fechaMandato'] = FechaEspaniol($row['FechaMandato']);
        break;

    case 'Consulta':
        break;
}

function Limpia() {
    global $campos;
    $campos = "";
}

;

function BorrarInquilino($id) {
    $ok = 0;
    $m = "";
    $d = $_SESSION['DBDAT'] . $_SESSION['empresa'];

    //Buscar relaciones con otras tablas: inmuebles, recibos, .....
    $res = mysql_query("select IDInquilino from $d.inmuebles where IDinquilino='$id'");
    $n = mysql_num_rows($res);
    if ($n)
        $m = "No se puede borrar: ESTA RELACIONADO CON " . $n . " INMUEBLES.";

    $res = mysql_query("select IDinquilino from $d.inmuebles_inquilinos where IDinquilino='$id'");
    $n = mysql_num_rows($res);
    if ($n)
        $m = "No se puede borrar: ESTA RELACIONADO CON " . $n . " INMUEBLES.";

    $res = mysql_query("select IDinquilino from $d.recibos where IDinquilino='$id'");
    $n = mysql_num_rows($res);
    if ($n)
        $m = "No se puede borrar: TIENE " . $n . " RECIBOS.";

    if ($m == "") {
        $ok = mysql_query("delete from inquilinos where IDInquilino='$id' limit 1;");
    } else {
        Mensaje($m);
    }
    return($ok);
}

;

function Listado() {
    global $pagina, $filtro, $parametros, $orden, $columna, $valor;
    $gris = "#CCCCCC";
    $tampagina = DameParametro('LOPAP', 15);
    $foco = $_POST['foco'];
    if ($foco == '')
        $foco = 1;

    $sql = "select * from inquilinos where " . $filtro . " order by $orden";
    list($desderegistro, $totalregistros, $totalpaginas) = Paginar($sql, $pagina, $tampagina);

    $l['sql'] = "select t1.*,t2.Nombre as Provincia,Telefono from " . $_SESSION['DBEMP'] . ".inquilinos as t1," . $_SESSION['DBEMP'] . ".provincias as t2 where (t1.idprovincia=t2.Codigo) and $filtro order by $orden";
    $l['titulo'] = "LISTADO DE INQUILINOS";
    $l['columnas'] = "IDInquilino_T_0_N,RazonSocial_T_38_N,Direccion_T_22_N,Poblacion_T_10_N,Provincia_T_0_N,Telefono_T_10_N";
    $l['filtro'] = "[$columna=$valor] [Orden=$orden]";
    ?>
    <table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" class="Formularios">
        <tr><td colspan="2">
                <?php Paginacion($pagina, $totalpaginas, $totalregistros, "contenido.php?c=inquilinos&" . $parametros . "&Accion=Consulta&pagina=", "left", $gris, $l); ?>
            </td></tr>

        <tr class="Formularios">
            <th>C&oacute;digo</th>
            <th>Raz&oacute;n Social</th>
        </tr>
        <form name="listado" action="contenido.php" method="post">
            <input name="c" value="inquilinos" type="hidden">
            <input name="Accion" value="Editar" type="hidden">
            <input name="IDInquilino" value="" type="hidden">
            <input name="orden" value="<?echo $orden;?>" type="hidden">
            <input name="pagina" value="<?echo $pagina;?>" type="hidden">
            <input name="columna" value="<?echo $columna;?>" type="hidden">
            <input name="valor" value="<?echo $valor;?>" type="hidden">
            <input name="foco" value="" type="hidden">

    <?php
    Subrrayado(2);

    $res = mysql_query($sql);

    $ok = @mysql_data_seek($res, $desderegistro);
    if ($ok) {
        $i = 0;
        while ($row = mysql_fetch_array($res) and ($i < $tampagina)) {
            $i = $i + 1;
            ?>
                    <tr class='Formularios' id="linea<?php echo $i; ?>" title="<?php echo "Observaciones: ", DecodificaTexto($row['Observaciones']); ?>"
                        onmouseover="<?php echo "cambiacolor('linea", $i, "','#FFFF00');"; ?>"
                        onmouseout="<?php echo "cambiacolor('linea", $i, "','", $gris, "');"; ?>"
                        >
                        <td>
                            <input name="l<?echo $i;?>" type="button" value="<?echo $row['IDInquilino'];?>" onclick="IDInquilino.value = '<?echo $row['IDInquilino'];?>'; foco.value = '<?echo $i;?>'; submit();" class="formularios">
                        </td>
                        <td><?php echo DecodificaTexto($row['RazonSocial']); ?></td>
                    </tr>
                    <?php }?>
                    <script language="JavaScript" type="text/javascript">
                                document.listado.l<?php echo $foco; ?>.focus();        </script>
                </form>
                <?php }?>

                <tr><td colspan="2">
            <?php Paginacion($pagina, $totalpaginas, $totalregistros, "contenido.php?c=inquilinos&" . $parametros . "&Accion=Consulta&pagina=", "right", $gris, $l); ?>
                    </td></tr>
            </table>
        <?php
        }

        function Formulario() {
            global $campos, $pagina, $orden, $columna, $valor;

// PONER VALORES POR DEFECTO------------------------------------------
            if ($campos['idbanco'] == "")
                $campos['idbanco'] = "0000";
            if ($campos['idoficina'] == "")
                $campos['idoficina'] = "0000";
            if ($campos['digito'] == "")
                $campos['digito'] = "00";
            if ($campos['cuenta'] == "")
                $campos['cuenta'] = "0000000000";
            if ($campos['vigente'] == "")
                $campos['vigente'] = '1';
            if ($campos['fpago'] == "")
                $campos['fpago'] = 'Contado';
            if ($campos['recargo'] == "")
                $campos['recargo'] = 0;
            if ($campos['idprovincia'] == "")
                $campos['idprovincia'] = "18";
//--------------------------------------------------------------------
            ?>

            <table ID="MANTENIMIENTO" width="100%" border="0" class="formularios"  bgcolor="">
                <tr ID="TITULO">
                    <td colspan="2" align="center" class="boxtitlewhite">Mantenimiento de Inquilinos</td>
                </tr>

                <form action="contenido.php" method="post" name="formulario">
                    <input name="c" value="inquilinos" type="hidden">
                    <input name="orden" value="<?echo $orden;?>" type="hidden">
                    <input name="columna" value="<? echo $columna;?>" type="hidden">
                    <input name="valor" value="<? echo $valor;?>" type="hidden">
                    <input name="pagina" value="<?php echo $pagina; ?>" type="hidden">
                    <TR ID="CUERPO">
                        <TD WIDTH="100%" colspan="2">
                            C&oacute;digo:<input name="IDInquilino" type="text" size="10" maxlength="10" value="<?php echo $campos['idinquilino']; ?>" class="formularios" <?php if ($campos['idinquilino'] != '') echo "readonly"; ?>>
                            Raz&oacute;n Social:<input name="RazonSocial" type="text" size="40" maxlength="50" value="<?php echo $campos['razonsocial']; ?>" class="formularios">
                            <br>
                            N. Comercial:<input name="NombreComercial" type="text" size="60" maxlength="50" value="<?php echo $campos['nombrecomercial']; ?>" class="formularios">
                            <br/>
                            CIF:<input name="Cif" type="text" size="10" maxlength="10" value="<?php echo $campos['cif']; ?>" class="formularios" onchange="ValidaNif('formulario', 'Cif')">
                            Fecha Nacimiento:<input name="FechaNacimiento" type="text" size="10" maxlength="10" value="<?php echo $campos['fechanacimiento']; ?>" class="formularios">
                            Vigente:<input name="Vigente" type="checkbox" <?php if ($campos['vigente'] == 1) echo "checked"; ?> class="formularios">
                            <br/>
                            Direccion:<input name="Direccion" type="text" size="50" maxlength="50" value="<?php echo $campos['direccion']; ?>" class="formularios">
                            <br>
                            Poblaci&oacute;n:<input name="Poblacion" type="text" size="15" maxlength="30" value="<?php echo $campos['poblacion']; ?>" class="formularios">
                            Provincia:<?php Desplegable('IDProvincia', $_SESSION['DBEMP'] . '.provincias', 'CODIGO', 'NOMBRE', 'NOMBRE', $campos['idprovincia'], '', '', ''); ?>
                            C&oacute;d.Postal:<input name="CodigoPostal" type="text" size="5" maxlength="5" value="<?php echo $campos['codigopostal']; ?>" class="formularios">
                            <br>
                            Tel&eacute;fono:<input name="Telefono" type="text" size="10" maxlength="30" value="<?php echo $campos['telefono']; ?>" class="formularios">
                            Fax:<input name="Fax" type="text" size="10" maxlength="30" value="<?php echo $campos['fax']; ?>" class="formularios">
                            M&oacute;vil:<input name="Movil" type="text" size="10" maxlength="30" value="<?php echo $campos['movil']; ?>" class="formularios">
                            <br>
                            Cuenta Contable:<input name="CContable" type="text" size="10" maxlength="10" value="<?php echo $campos['ccontable']; ?>" class="formularios">
                            Rec.Equ:<input name="Recargo" type="text" size="5" maxlength="5" value="<?php echo $campos['recargo']; ?>" class="formularios">
                            Saldo:<input type="text" size="10" readonly value="<?echo SaldoInquilino($campos['idinquilino']);?>" class="BlancoFondoRojo">
                            <br>
                            Cta.Corriente:<?php CuentaCorriente('formulario', 'IDBanco', 'IDOficina', 'Digito', 'Cuenta'); ?><br>
                            Iban:<input name="Iban" type="text" size="34" maxlength="34" value="<?php echo $campos['iban']; ?>" class="formularios">
                            Bic:<input name="Bic" type="text" size="11" maxlength="11" value="<?php echo $campos['bic']; ?>" class="formularios"> 
                            <br/>
                            Mandato:<input name="Mandato" type="text" size="35" maxlength="35" value="<?php echo $campos['mandato']; ?>" class="formularios">
                            Fecha Mandato:<input name="FechaMandato" type="text" size="10" maxlength="10" value="<?php echo $campos['fechaMandato']; ?>" class="formularios">                        
                            <br/>
                            E-mail:<input name="EMail" type="text" size="33" maxlength="50" value="<?php echo $campos['email']; ?>" class="formularios">
                            Web:<input name="Web" type="text" size="30" maxlength="50" value="<?php echo $campos['web']; ?>" class="formularios">
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Observaciones:<br><textarea name="Observaciones" cols="70" rows="3" textarea="textarea" class="formularios"><?php echo $campos['observaciones']; ?></textarea>
                            <br>
                            Avisos:<br><textarea name="Avisos" cols="70" rows="3" textarea="textarea" class="formularios"><?php echo $campos['avisos']; ?></textarea><br>
            <?php echo $campos['factualizacion']; ?>
                        </td>
                        <td align="center">
            <?php if ($campos['idinquilino'] != '') FotoInquilino($campos['idinquilino'], 75); ?>
                        </td>
                    </tr>

            <?php Subrrayado(2); ?>
                    <table id="PIE" width="100%" class="formularios">
                        <tr>
                            <td align="left">
            <?php if ($campos['idinquilino'] != '') { ?>
                                    <a href="javascript:;" onclick="window.open('contenido.php?c=consultarecibos&t=Recibos&campo=recibos.IDInquilino&desde=<? echo $campos['idinquilino'];?>&hasta=<? echo $campos['idinquilino'];?>&resumido=N&Accion=Consulta', 'Recibos', 'width=900,height=620,resizable=yes,scrollbars=yes')">Recibos</a>
                                    <a href="javascript:;" onClick="window.open('contenido.php?c=alquilar&t=Alquilar&columna=<?echo $_SESSION['DBEMP'];?>.inquilinos.IDInquilino&valor=<?echo $campos['idinquilino'];?>', 'Alquiler', 'width=900,height=520,resizable=yes,scrollbars=yes')">Alquiler</a>
            <?php } ?>		
                            </td>
                            <td width="50%" align="center" class="boxtitlewhite">
            <?php if ($campos['idinquilino'] != '') { ?>
                                    <input name="Accion" type="submit" value="Guardar" class="formularios">
                                    <input name="Accion" type="submit" value="Borrar" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar el inquilino ", $campos['razonsocial']; ?>');">
            <?php } else { ?>
                                    <input name="Accion" type="submit" value="Crear" class="formularios">
            <?php } ?>
                                <input name="Accion" type="submit" value="Limpiar" class="formularios">
                            </td>
                        </tr>
            <?php Subrrayado(2); ?>
                </form>

                <table>

                </table>

            <?php
        }
        ?>

            <table width="100%">
                <tr valign="top">
                    <td width="300" bgcolor="#CCCCCC"><?php if ($accion != '') Listado(); ?></td>
                    <td bgcolor="#CCCCCC"><?php Formulario(); ?></td>
        </tr>
    </table>