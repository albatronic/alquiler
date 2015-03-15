<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/desplegable.php";

$accion=$_POST['accion'];
$desdei=$_POST['desdei'];
$hastai=$_POST['hastai'];
$mes=$_POST['mes'];
$valor=$_POST['valor'];
$listado=$_POST['listado'];
$parametros="desdei=$desdei&hastai=$hastai&mes=$mes";

$e=$_SESSION['DBEMP'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];

switch ($accion) {
    case 'Si':
        $i=0;
        $resultado="";
        $sql="UPDATE inmuebles_inquilinos SET PorcentajeSubida=$valor WHERE ((IDInquilino>='$desdei') and (IDInquilino<='$hastai') and (month(FechaSubida)='$mes') and (year(FechaSubida)='".date('Y')."'));";
        $res=mysql_query($sql);

        if ($listado=='S') {?>
            <script language="JavaScript" type="text/javascript">
            window.open('liscambioipc?<?echo $parametros;?>');
            </script>
        <?php }

        break;

	case '':
        ?>
        <table align="center"><tr height="100"><td></td></tr></table>

        <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Aplicar el cambio de IPC</th></tr>
        <form name="form" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="cambioipc">
        <tr><td colspan="2">Desde Inquilino:<input name="desdei" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Inquilino:<input name="hastai" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">A partir del mes:<?php DesplegableMeses('mes',date('m'),'');?></td></tr>
        <tr><td colspan="2">Nuevo Valor:<input name="valor" type="text" size="5" maxlength="6" class="formularios"></td></tr>
        <tr><td colspan="2">Imprimir Listado:<?php DesplegableSN('listado','S','formularios');?></td></tr>
        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top"><td>Desea cambiar el importe de la subida? <input name="accion" value="Si" type="submit" class="formularios"></td>
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