<?
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "engancha.php";
require "funciones/fechas.php";
require "funciones/desplegable.php";
require "funciones/recibos.php";

$EMP=$_SESSION['DBEMP'];
$DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];


$campo=$_GET['campo']; if($campo=='') $campo=$_POST['campo'];
$desde=$_GET['desde']; if($desde=='') $desde=$_POST['desde'];
$hasta=$_GET['hasta']; if($hasta=='') $hasta=$_POST['hasta'];
$accion=$_POST['accion'];

function RefrescaPadre($campo,$desde,$hasta){
    echo "<script language='JavaScript' type='text/JavaScript'>
    url=window.opener.document.location.href + '?c=consultarecibos&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"';
    window.opener.document.location.href=url;
    </script>";
}

switch ($accion){
    case 'No':
        CierraVentana();
    break;
    
    case 'Si':
        $m='';
        $fecha=$_POST['fecha'];
        $conceptocobro=DameParametro('COBRO','00');

        $d=$desde;$h=$hasta;
        if($campo=='recibos.Fecha') {$d=AlmacenaFecha($d);$h=AlmacenaFecha($h);}

        $sql="select IDRecibo from recibos where $campo>='$d' and $campo<='$h';";
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            if(!CobrarRecibo($row['IDRecibo'],$conceptocobro,$fecha))
                $m.='No se ha podido cobrar el recibo '.$row['IDRecibo'].'<br>';
        }
        RefrescaPadre($campo,$desde,$hasta);
        if($m!='') echo $m; else CierraVentana();
    
    break;

	case '':
        ?>
        <form name="form" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="cobrarremesa">
        <input name="t" type="hidden" value="Cobro Remesa">
        <input name="campo" type="hidden" value="<?echo $campo;?>">
        <input name="desde" type="hidden" value="<?echo $desde;?>">
        <input name="hasta" type="hidden" value="<?echo $hasta;?>">

        <table class="combofamilias" align="center" border="0" width="95%">
        <tr><th class="blancoazul" colspan="2">Cobrar Remesa</th></tr>

        <tr height="20"><td colspan="2"></td></tr>
        <tr>
            <td>Fecha de Cobro (ddmmaaaa):</td>
            <td><input name="fecha" value="<?echo date('dmY');?>" type="text" size="8" maxlength="8" class="formularios"></td>
        </tr>

        <tr height="30"><td colspan="2"></td></tr>
        <tr valign="top">
            <td align="right">¿Desea cobrar la remesa?&nbsp<input name="accion" value="Si" type="submit" class="formularios"></td>
            <script language="JavaScript" type="text/javascript">
            document.form.fecha.focus();
            </script>
        </form>
        <form name="form" action="contenido.php" method="POST">
            <td align="left">
                <input name="c" type="hidden" value="cobrarremesa">
                <input name="accion" value="No" type="submit" class="formularios">
            </td>
        </tr>
        </table>
        </form>
        <?
        break;
}
?>
