<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/desplegable.php";

$accion=$_POST['accion'];
$desdec=$_POST['desdec'];
$hastac=$_POST['hastac'];

$e=$_SESSION['DBEMP'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];

switch ($accion) {
    case 'Si':
        $i=0;
        $resultado="";
        $sql="SELECT IDConcepto,Concepto,Precio FROM ".$e.".conceptos WHERE ((IDConcepto>='$desdec') and (IDConcepto<='$hastac')) ORDER BY IDConcepto;";
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            $sql="UPDATE ".$d.".inmuebles_conceptos SET Importe='".$row['Precio']."' WHERE (IDConcepto='".$row['IDConcepto']."');";
            mysql_query($sql);
            $afectados=mysql_affected_rows();
            $i++;
            $resultado=$resultado."<tr><td>".$row['IDConcepto']."</td><td>".$row['Concepto']."</td><td align='right'>".$row['Precio']."</td><td align='right'>".$afectados."</td></tr>";
        }
        $resultado="<tr><th colspan='2'>Concepto</th><th>Precio</th><th>Conceptos Cambiados</th></tr>".$resultado;
        $resultado="<table align='center' class='formularios' width='60%' border='1' bgcolor='#CCCCCC'>".$resultado."</table>";
        echo $resultado;
        break;

	case '':
        ?>
        <table align="center"><tr height="100"><td></td></tr></table>

        <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Aplicar el cambio del importe de los conceptos</th></tr>
        <form name="form" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="cambioimporteconceptos">
        <tr><td colspan="2">Desde Concepto:<input name="desdec" type="text" size="3" maxlength="2" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Concepto:<input name="hastac" type="text" size="3" maxlength="2" class="formularios"></td></tr>
        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top"><td>¿Desea cambiar el importe de los conceptos indicados? <input name="accion" value="Si" type="submit" class="formularios"></td>
    <script language="JavaScript" type="text/javascript">
    document.form.desdec.focus();
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
