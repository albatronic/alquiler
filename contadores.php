<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];
$e=$_SESSION['DBEMP'];


require "conecta.php";
require "funciones/desplegable.php";


$accion=$_POST['accion'];
$ide=$_GET['idempresa'];
if ($ide=='') $ide=$_POST['idempresa'];
if ($ide=='') exit;
$coniva=$_GET['coniva'];
if ($coniva!="") $filtro=" and ConIva='$coniva'"; else $filtro="";


$sql="select RazonSocial from $e.empresas where IDEmpresa=$ide;";
$res=mysql_query($sql);
$emp=mysql_fetch_array($res);

switch ($accion) {
    case "Grabar":
        $v=$_POST;
        if ($v['idc0']!=""){ //Añadir uno nuevo
            $valores="'$ide','".$v['idc0']."','".$v['contador0']."','".$v['coniva0']."'";
            $sql="INSERT INTO series_recibos VALUES ($valores);";
            mysql_query($sql);
        }
        $i=0;
        while ($i<$v['nvalores']){ //Reccorro todos los valores pasados como parametros
            $i++;
            $idc=$v['idc'.$i];
            $contador=$v['contador'.$i];
            $coniva=$v['coniva'.$i];
            $sql="UPDATE series_recibos SET Contador='$contador', ConIva='$coniva'
                  WHERE ((IDEmpresa='$ide') and (IDSerie='$idc')) limit 1;";
            if (!mysql_query($sql)) Mensaje("No se grabaron los datos. Intentelo de nuevo.");
        }
    break;

}
?>

<table width="100%" class="formularios">
    <tr><th class="blancoazul" colspan="3">Contadores de Recibos de<br><?php echo $emp[0];?></th></tr>
    <tr><th>Serie</th><th>Contador</th><th>Con Iva</th></tr>
    <?php Subrrayado(3);?>
    <form name="contadores" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="contadores">
        <input name="idempresa" type="hidden" value="<?php echo $ide;?>">

    <?php
    $sql="select * from series_recibos where IDEmpresa='$ide' $filtro;";
    $res=mysql_query($sql);
    $i=0;
    while ($row=mysql_fetch_array($res)){$i++;?>
    <tr>
        <td align="center"><input name="idc<?echo $i?>" type="text" value="<?echo $row['IDSerie'];?>" size="1" maxlength="1" class="formularios"></td>
        <td align="center"><input name="contador<?echo $i?>" type="text" value="<?echo $row['Contador'];?>" size="5" maxlength="5" class="formularios"></td>
        <td align="center"><?php DesplegableSN('coniva'.$i,$row['ConIva'],'formularios');?></td>
    </tr>

    <?php }?>
    <tr>
        <td align="center"><input name="idc0" type="text" size="1" maxlength="1" class="formularios"></td>
        <td align="center"><input name="contador0" type="text" size="5" maxlength="5" class="formularios"></td>
        <td align="center"><?php DesplegableSN('coniva0','S','formularios');?></td>
    </tr>
    <?php Subrrayado(3);?>
    <tr>
        <td colspan="3" align="center">
            <input name="accion" value="Grabar" type="submit" class="formularios">
            <input name="nvalores" value="<?php echo $i;?>" type="hidden">
        </td>
    </tr>
    <script language="JavaScript" type="text/javascript">
    document.contadores.idc0.focus();
    </script>
    </form>

</table>
