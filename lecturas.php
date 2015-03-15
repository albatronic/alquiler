<?php
session_start();
if ($_SESSION['iu'] == '')
    exit;
$idagente = $_SESSION['iu'];
$esadm = $_SESSION['esadm'];
$d = $_SESSION['DBDAT'] . $_SESSION['empresa'];
$e = $_SESSION['DBEMP'];

require "engancha.php";
require "funciones/textos.php";
require "funciones/formatos.php";

$accion = $_POST['accion'];
$banco = $_POST['banco'];
$columna = $_POST['columna'];
if ($columna == '')
    $columna = "inmuebles.Poblacion";
$valor = $_POST['valor'];
?>

<script language="JavaScript" type="text/javascript">
    function Valida(anterior,valor,modi,valoraviso){
        document.lecturas[modi].value='1';
        if ( (document.lecturas[valor].value-document.lecturas[anterior].value) < 0 ){
            if ( confirm('Vuelta de contador?') ) return true;
            else {
                document.lecturas[modi].value = '0';
            }
        }
        if ( (document.lecturas[valor].value-document.lecturas[anterior].value) > eval(valoraviso) ){
            if ( confirm('La Diferencia es superior a '+valoraviso+'. Es correcto?') ) return true;
            else {
                document.lecturas[modi].value = '0';
                return false;
            };
        };
    }
</script>

<table id="SELECCION" width="100%" align="center" bgcolor="#CCCCCC" class="formularios">
    <tr><th class="boxtitlewhite" colspan="4">LECTURAS DE CONSUMOS</th></tr>
    <tr>
    <form name="Consulta" action="contenido.php" method="post">
        <input name="c" value="lecturas" type="hidden">
        <input name="accion" value="Consulta" type="hidden">
        <td align="center">Cuenta Cobro:
            <select name="banco" class="ComboFamilias" onchange="submit();">
                <option value="1" <?php if ($banco == "1")
    echo "selected"; ?>>Todos</option>
                <?php
                $sql = "SELECT DISTINCT $d.inmuebles.IDBanco as IDBanco, $e.bancos.Banco as Banco
                    FROM $d.inmuebles, $e.bancos
                    WHERE $d.inmuebles.IDBanco=$e.bancos.IDBanco
                    ORDER BY $d.inmuebles.IDBanco;";
                $res = mysql_query($sql);
                while ($row = mysql_fetch_array($res)) {
                    $aux = "inmuebles.IDBanco=" . $row['IDBanco'];
                ?>
                    <option value="<?php echo $aux; ?>" <?php if ($banco == $aux)
                            echo "selected"; ?>><?php echo $row['Banco']; ?></option>
<?php } ?>
            </select>
        </td>
        <td align="center">Buscar por:
            <select name="columna" class="ComboFamilias">
                <option value="inmuebles.Direccion" <?php if ($columna == "inmuebles.Direccion")
                        echo "selected"; ?>>Direccion</option>
                <option value="inmuebles.IDInmueble" <?php if ($columna == "inmuebles.IDInmueble")
                        echo "selected"; ?>>Codigo</option>
                <option value="inmuebles.Poblacion" <?php if ($columna == "inmuebles.Poblacion")
                        echo "selected"; ?>>Poblacion</option>
            </select>
        </td>
        <td>
    		Valor(?):<input name="valor" value="<?php echo $valor; ?>" type="text" size="40" maxlength="50" class="formularios">
                </td>
                <td align="center">
                    <input type="image" src="images\lupa.png">
                </td>
                <script language="JavaScript" type="text/javascript">
                    document.Consulta.banco.focus();
                </script>
            </form>
        </tr>
        </table>

<?php
                    Subrrayado(4);
                    switch ($accion) {
                        case "Grabar":
                            $i = 0;
                            $nactu = 0;
                            $v = $_POST;
                            while ($i < $v['nvalores']) { //Reccorro todos los valores pasados como parametros
                                $i++;
                                $idi = $v['idi' . $i];
                                $idc = $v['idc' . $i];
                                $anterior = $v['anterior' . $i];
                                $valor = $v['valor' . $i];
                                if ($v['modi' . $i] == "1") { //Pero solo grabo los registros modificados.
                                    $nactu++;
                                    $sql = "UPDATE inmuebles_conceptos SET ValorAnterior='$anterior', ValorActual='$valor'
                                    WHERE ((IDInmueble='$idi') and (IDConcepto='$idc')) limit 1;";
                                    if (!mysql_query($sql))
                                        Mensaje("No se grabaron los datos. Intentelo de nuevo.");
                                }
                            }
                            if ($nactu > 0)
                                Mensaje("Se guardaron $nactu lecturas.");
                            break;

                        case "Consulta":
                            $banco = $_POST['banco'];
                            $columna = $_POST['columna'];
                            $valor = $_POST['valor'];

                            $c = str_replace("?", "%", $valor);
                            if ($c == '')
                                $c = "1"; else
                                $c=$columna . " like '$c'";
                            $filtro = "($banco) and ($c)";
                            Listado($filtro);
                            break;
                    }

                    function Listado($filtro) {
                        global $d, $e;

                        $valoraviso = DameParametro('AVLEC', 1000);

                        $sql = "SELECT inmuebles_conceptos.*,$e.conceptos.Concepto,$e.conceptos.Diferencia,inmuebles.Direccion
        FROM inmuebles_conceptos , $e.conceptos, inmuebles
        WHERE ( (inmuebles_conceptos.idconcepto=$e.conceptos.IDConcepto ) and
            ($e.conceptos.Consumo='S') and
            (inmuebles_conceptos.IDInmueble=inmuebles.IDInmueble) and (" . $filtro . ") )
        ORDER BY IDInmueble ASC, IDconcepto ASC";

                        $res = mysql_query($sql);
?>

                        <table ID="CUERPO_LISTADO" width="100%" align="center" bgcolor="#CCCCCC" class="formularios">
                            <tr class="Formularios">
                                <th>Direccion Inmueble</th>
                                <th>Concepto de Consumo</th>
                                <th>Importe</th>
                                <th>Lectura<br>Anterior</th>
                                <th>Lectura<br>Actual</th>
                            </tr>

                            <form name="lecturas" action="contenido.php" method="POST">
                                <input name="c" type="hidden" value="lecturas">

<?php
                        Subrrayado(5);
                        $inmanterior = "";
                        $i = 0;

                        while ($row = mysql_fetch_array($res)) {
                            $i = $i + 1;
?>
                            <tr class="Formularios" id="li<?php echo $i; ?>" bgcolor="<?php echo $color; ?>"
                                onmouseover="<?php echo "cambiacolor('li", $i, "','#FFFF00');"; ?>"
                                onmouseout="<?php echo "cambiacolor('li", $i, "','", $color, "');"; ?>"
                                >
<?php
                            if ($inmanterior != $row['IDInmueble']) {
                                if ($i > 1)
                                    Subrrayado(5);
                                $texto = "(" . $row['IDInmueble'] . ") " . DecodificaTexto($row['Direccion']);
                                $clase = "blancoazul";
                            } else {
                                $clase = "";
                                $texto = "";
                            }
                            $inmanterior = $row['IDInmueble'];
?>
                        <td class="<?php echo $clase; ?>"><?php echo $texto; ?></td>
                        <td><?php echo "(", $row['IDConcepto'], ") ", DecodificaTexto($row['Concepto']); ?></td>
                        <td align="right"><?php echo $row['Importe']; ?></td>
                        <td align="right">
                            <input name="anterior<?php echo $i; ?>" value="<?php echo $row['ValorAnterior']; ?>" onchange="Valida('anterior<?php echo $i; ?>','valor<?php echo $i; ?>','modi<?php echo $i; ?>',<?php echo $row['Diferencia']; ?>);" type="text" class="formularios" size="7" maxlength="7" tabindex="0">
                        </td>
                        <td align="right">
                            <input name="valor<?php echo $i; ?>" value="<?php echo $row['ValorActual']; ?>" onchange="Valida('anterior<?php echo $i; ?>','valor<?php echo $i; ?>','modi<?php echo $i; ?>',<?php echo $row['Diferencia']; ?>);" type="text" class="formularios" size="7" maxlength="7" tabindex="<?php echo $i; ?>">
                            <input name="idi<?php echo $i; ?>" value="<?php echo $row['IDInmueble']; ?>" type="hidden">
                            <input name="idc<?php echo $i; ?>" value="<?php echo $row['IDConcepto']; ?>" type="hidden">
                            <input name="modi<?php echo $i; ?>" value="0" type="hidden">
                            <?php if ($row['ValorActual']<$row['ValorAnterior']) {?>
                                <img src="images/alerta.gif" border="0" alt="Alerta" title="Vuelta de Contador" />
                            <?php } ?>
                        </td>
                    </tr>
<?php
                        }
                        Subrrayado(5);
?>
                        <tr><td colspan="5" align="center" class="blancoazul">
                                <input name="accion" value="Grabar" type="submit" class="formularios">
                                <input name="nvalores" value="<?php echo $i; ?>" type="hidden">
                            </td></tr>
                        <script language="JavaScript" type="text/javascript">
                            document.lecturas.valor1.focus();
                        </script>
                    </form>

                </table>
<?php } ?>
