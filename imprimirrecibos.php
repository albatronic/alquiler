<?php
session_start();
if ($_SESSION['iu']=='') exit;

require "funciones/desplegable.php";

?>
<script language="JavaScript" type="text/javascript">

function Lanza(formulario){
    var url,db,hb;
    
    db=document[formulario].desdeb.value+document[formulario].desdeo.value;
    hb=document[formulario].hastab.value+document[formulario].hastao.value;

    url='recibopdf.php?orden=ASC&columna=SUBSTRING(CuentaAbono,1,8)&d='+db+'&h='+hb+'&fe='+document[formulario].fe.value+'&salto='+document[formulario].saltopagina.value;
    window.open(url,'Recibos','menubar=yes,scrollbars=yes');
}
</script>

    <table align="center"><tr height="100"><td colspan="2"></td></tr></table>
    <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Impresion de Recibos</th></tr>
        <form name="formulario" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="imprimirrecibos">
        <tr><td colspan="2">
            Desde Banco Remesa:
            <input name="desdeb" type="text" size="4" maxlength="4" class="formularios">
            <input name="desdeo" type="text" size="4" maxlength="4" class="formularios">
        </td></tr>
<script language="JavaScript" type="text/javascript">
document.formulario.desdeb.focus();
</script>
        <tr><td colspan="2">
            Hasta Banco Remesa:
            <input name="hastab" type="text" size="4" maxlength="4" class="formularios">
            <input name="hastao" type="text" size="4" maxlength="4" class="formularios">
        </td></tr>
        <tr><td colspan="2">
            Fecha de Emision:
            <input name="fe" type="text" value="<?php echo date('d/m/Y');?>" size="10" maxlength="10" class="formularios">
        </td></tr>
        <tr><td colspan="2">Saltar Pagina?<?php DesplegableSN('saltopagina','N','formularios');?></td></tr>

        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top">
            <td>
                Desea imprimir los recibos indicados?
                <input name="accion" value="Si" type="button" class="formularios" onclick="Lanza('formulario');" accesskey="S">
            </td>
        </form>
        <td align="left">
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="inicial">
            <input name="accion" value="No" type="submit" class="formularios">
            </form>
        </td>
        </tr>
    </table>

