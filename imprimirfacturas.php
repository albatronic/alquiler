<?php
session_start();
if ($_SESSION['iu']=='') exit;

require "funciones/desplegable.php";

$e=$_SESSION['DBEMP'];
?>
    <table align="center"><tr height="100"><td colspan="2"></td></tr></table>

    <table class="combofamilias" align="center">
        <tr><th class="blancoazul" colspan="2">Listado de Facturas Emitidas</th></tr>
        <form name="formulario" action="lisfacturas.php" method="POST" target="_blank">
        <tr><td colspan="2">Desde Fecha:<input name="desdef" VALUE="<?echo "01/01".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr><td colspan="2">Hasta Fecha:<input name="hastaf" VALUE="<?echo "31/12".date('/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
        <tr height="5"><td colspan="2"></td></tr>
        <tr><td colspan="2">
            Desde Serie:&nbsp
            <select name="desdes" class="formularios">
            <?$res=mysql_query("select IDSerie from $e.series_recibos where IDEmpresa='".$_SESSION['empresa']."' order by IDSerie ASC;");
            while ($row=mysql_fetch_array($res)){?>
            <option value="<?echo $row['IDSerie'];?>"><?echo $row['IDSerie'];?></option>
            <?}?>
            </select>
        </td></tr>
        <tr><td colspan="2">
            Hasta Serie:&nbsp
            <select name="hastas" class="formularios">
            <?$res=mysql_query("select IDSerie from $e.series_recibos where IDEmpresa='".$_SESSION['empresa']."' order by IDSerie DESC;");
            while ($row=mysql_fetch_array($res)){?>
            <option value="<?echo $row['IDSerie'];?>"><?echo $row['IDSerie'];?></option>
            <?}?>
            </select>
        </td></tr>
        <tr><td colspan="2">
            Título del listado:&nbsp<input name="titulo" type="text" size="35" class="formularios">
        </td></tr>
        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top">
            <td align="right">¿Desea generar el listado?&nbsp<input name="accion" value="Si" type="submit" class="formularios"></td>
            <script language="JavaScript" type="text/javascript">
            document.formulario.desdef.focus();
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

