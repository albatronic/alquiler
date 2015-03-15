<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/fechas.php";
require "funciones/desplegable.php";

$accion=$_POST['accion'];
$dias=$_POST['dias'];
$vencidos=$_POST['vencidos'];

$e=$_SESSION['DBEMP'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];
?>

        <table class="formularios" align="center" width="100%">
            <tr><th class="blancoazul">Listado de Contratos Pr&oacute;ximos a Subir la Renta</th></tr>
        <form name="form" action="contenido.php" method="POST">
        <input name="c" type="hidden" value="proximassubidas">
        <tr>
            <td align="center">
                D&iacute;as que faltan:<input name="dias" value="<?php echo $dias;?>" type="text" size="3" maxlength="3" class="formularios">&nbsp;&nbsp;
                Mostrar los ya vencidos:<?php DesplegableSN('vencidos',$vencidos,'formularios');?>&nbsp;&nbsp;
                <input name="accion" value="Consulta" type="submit" class="formularios">
                <input name="accion" value="Resumen" type="submit" class="formularios">
            </td>
        </tr>
        <script language="JavaScript" type="text/javascript">
        document.form.dias.focus();
        </script>
        </form>
        <?Subrrayado(1);?>
        </table>
        
<?php
switch ($accion) {
    case 'Consulta':
        $i=0;
        $hoy=date("Y-m-d");
        if ($vencidos=='S') $v="(1=1)"; else $v="(FechaSubida>='$hoy')";

        $diavcto=date('d/m/Y',mktime(0,0,0,date('m'),date('d')+$dias,date('Y')));
        $vcto=date('Y-m-d',mktime(0,0,0,date('m'),date('d')+$dias,date('Y')));
        $sql="SELECT inmuebles.Direccion as Inmueble,$e.inquilinos.RazonSocial as Inquilino,inmuebles_inquilinos.*
            FROM inmuebles,$e.inquilinos,inmuebles_inquilinos
            WHERE
                (inmuebles.IDInmueble=inmuebles_inquilinos.IDInmueble) and
                ($e.inquilinos.IDInquilino=inmuebles_inquilinos.IDInquilino) and
                (FechaSubida<='$vcto') and $v
            ORDER BY FechaSubida;";
        $res=mysql_query($sql);
        ?>
        <table width="100%" class="formularios">
        <tr><th colspan="5" align="center" class="blancoazul">Listado de Inmuebles que hay que subir la renta el <?php echo $diavcto;?></th></tr>
        <tr><th>Inmueble</th><th>Inquilino</th><th>F.Inicio</th><th class="blancoazul">F.Subida</th><th>F.Fin</th></tr>
        <?php
        while ($row=mysql_fetch_array($res)){$i++; if ($row['FechaSubida']<$hoy) $color=$ROJO; else $color="";?>
            <tr bgcolor="<?php echo $color;?>">
                <td><?php echo $row['Inmueble'],"(",$row['IDInmueble'],")";?></td>
                <td><?php echo $row['Inquilino'],"(",$row['IDInquilino'],")";?></td>
                <td align="center"><?php echo FechaEspaniol($row['FechaInicio']);?></td>
                <td align="center"><?php echo FechaEspaniol($row['FechaSubida']);?></td>
                <td align="center"><?php echo FechaEspaniol($row['FechaFin']);?></td>
            </tr>
        <?php }?>
        </table>
        <?php
        break;

    case 'Resumen':
        $sql="select FechaSubida,count(FechaSubida) as Numero from inmuebles_inquilinos group by FechaSubida order by FechaSubida";
        $res=mysql_query($sql);
        ?>
        <table width="40%" class="formularios">
        <tr><th colspan="2" align="center" class="blancoazul">Resumen de Fechas de Subidas de Contratos</th></tr>
        <tr><th>Fecha Subida</th><th>N.Contratos</th></tr>
        <?php
        Subrrayado(2);
        while ($row=mysql_fetch_array($res)){?>
            <tr bgcolor="<?php echo $color;?>">
                <td align="center"><?php echo FechaEspaniol($row['FechaSubida']);?></td>
                <td align="right"><?php echo $row['Numero']?></td>
            </tr>
        <?php }?>
        </table>
        <?php
        break;
}
?>
