<script language="JavaScript" type="text/javascript">
	Centrar();
</script>
<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "engancha.php";
require "funciones/fechas.php";
require "funciones/recibos.php";

$EMP=$_SESSION['DBEMP'];
$DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

$campos=$_POST;
if(count($campos)==0) $campos=$_GET;

switch ($campos['Accion']){
    case 'Cobrar':
        $conceptocobro=DameParametro('COBRO','00');
        if(!CobrarRecibo($campos['idrecibo'],$conceptocobro,date('d/m/Y'))) Mensaje('No se ha podido cobrar el recibo '.$campos['idrecibo']);
    break;
    
    case 'Guardar':
        $total=round($campos['basemediacion']+$campos['base']*(1+$campos['iva']/100+$campos['recargo']/100-$campos['retencion']/100),2);
        $valores="SET Fecha='".AlmacenaFecha($campos['fecha'])."',".
                "Periodo='".$campos['periodo']."',".
                "BaseMediacion='".$campos['basemediacion']."',".
                "Base='".$campos['base']."',".
                "Iva='".$campos['iva']."',".
                "Recargo='".$campos['recargo']."',".
                "Retencion='".$campos['retencion']."',".
                "Total='".$total."',".
                "CuentaCargo='".$campos['cuentacargo']."',".
                "CuentaAbono='".$campos['cuentaabono']."',".
                "IDRemesa='".$campos['idremesa']."'";
        $sql="update recibos $valores where IDRecibo='".$campos['idrecibo']."';";
        $res=mysql_query($sql);
		if (!$res) Mensaje("No se han podido actualizar los datos. Inténtelo de nuevo");
		TotalizaRecibo($campos['idrecibo']);
    break;
    
    case 'Borrar':
        $res=mysql_query("delete from recibos_lineas where IDRecibo='".$campos['idrecibo']."';");
        if ($res) $res=mysql_query("delete from recibos where IDRecibo='".$campos['idrecibo']."';");
        if (!$res) Mensaje("No se ha podido borrar el recibo");
    break;

    case 'Buscar':
        $campos['idrecibo']=$campos['idbuscar'];
    break;

}

$sql="select t1.*,t2.RazonSocial,t3.Direccion
    from recibos as t1,$EMP.inquilinos as t2,inmuebles as t3
    where IDRecibo='".$campos['idrecibo']."' and
    t1.IDInquilino=t2.IDInquilino and
    t1.IDInmueble=t3.IDInmueble;";
$res=mysql_query($sql);
$row=mysql_fetch_array($res);
if($row['Saldo']<>0) $color="BlancoFondoRojo"; else $color="BlancoFondoVerde";

$sql="select Banco from $EMP.bancos where IDBanco='".substr($row['CuentaCargo'],0,4)."';";
$res=mysql_query($sql);
$banco=mysql_fetch_array($res);

$sql="select Direccion from $EMP.bancos_oficinas where IDBanco='".substr($row['CuentaCargo'],0,4)."' and IDOficina='".substr($row['CuentaCargo'],4,4)."';";
$res=mysql_query($sql);
$oficina=mysql_fetch_array($res);

?>


<form name="formulario" action="contenido.php" method="POST">
<input name="c" value="editrecibo" type="hidden">
<input name="idrecibo" value="<?php echo $row['IDRecibo'];?>" type="hidden">

<table width="100%" border=1 class="degradadoazul">
<tr class="formularios">
    <td valign="top">
        <table width="100%" class="formularios">
            <tr><td>N. Recibo:</td>
                <td colspan="2">
                <input name="idbuscar" value="<?php echo $row['IDRecibo'];?>" type="text" size="6" class="formularios">
                <input name="Accion" value="Buscar" type="submit" class="formularios">&nbsp;
                </td>
            </tr>
            <tr><td>Fecha:</td><td colspan="2"><input name="fecha" value="<?php echo FechaEspaniol($row['Fecha']);?>" type="text" size="10" maxlength="10" class="formularios"></td></tr>
            <tr><td>Inquilino:</td><td colspan="2"><?php echo $row['IDInquilino']," - ",$row['RazonSocial'];?></td></tr>
            <tr><td>Inmueble:</td><td colspan="2"><?php echo $row['IDInmueble']," - ",$row['Direccion'];?></td></tr>
            <tr><td>Periodo:</td><td colspan="2"><input name="periodo" value="<?php echo $row['Periodo'];?>" type="text" size="22" maxlength="21" class="formularios"></td></tr>
            <tr>
                <td>Cuenta Cargo:</td>
                <td><input name="cuentacargo" value="<?php echo $row['CuentaCargo'];?>" type="text" size="22" maxlength="20" class="formularios"></td>
                <td align="left" class="letrapeq"><?php echo $banco[0],"<br>",$oficina[0];?></td>
            </tr>
            <tr><td>Iban/Bic Cargo:</td>
                <td><input name="ibancargo" value="<?php echo $row['IbanCargo'];?>" type="text" size="35" maxlength="34" class="formularios"></td>           
                <td><input name="biccargo" value="<?php echo $row['BicCargo'];?>" type="text" size="12" maxlength="11" class="formularios"></td>
            </tr>            
            <tr><td>Cuenta Abono:</td><td colspan="2"><input name="cuentaabono" value="<?php echo $row['CuentaAbono'];?>" type="text" size="22" maxlength="20" class="formularios"></td></tr>
            <tr><td>Iban/Bic Abono:</td>
                <td><input name="ibanabono" value="<?php echo $row['IbanAbono'];?>" type="text" size="35" maxlength="34" class="formularios"></td>
                <td><input name="bicabono" value="<?php echo $row['BicAbono'];?>" type="text" size="12" maxlength="11" class="formularios"></td>
            </tr>
            <tr><td>C&oacute;digo Remesa:</td><td colspan="2"><input name="idremesa" value="<?php echo $row['IDRemesa'];?>" type="text" size="15" maxlength="14" class="formularios"></td></tr>
        </table>
    </td>

    <td width="30%">
        <table width="100%" class="formularios" border=0>
            <tr><td>Mediacion:</td><td></td><td><input name="basemediacion" value="<?php echo $row['BaseMediacion'];?>" type="text" size="10" maxlength="10" class="formularios" readonly></td></tr>
            <tr><td>Base:</td><td></td><td><input name="base" value="<?php echo $row['Base'];?>" type="text" size="10" maxlength="10" class="formularios" readonly></td></tr>
            <tr>
                <td>Iva:</td>
                <td><input name="iva" value="<?php echo $row['Iva'];?>" type="text" size="5" maxlength="10" class="formularios"></td>
                <td><input type="text" value="<?php echo round($row['Base']*$row['Iva']/100,2);?>" size="10" readonly class="formularios"></td>
            </tr>
            <tr>
                <td>Retencion:</td>
                <td><input name="retencion" value="<?php echo $row['Retencion'];?>" type="text" size="5" maxlength="10" class="formularios"></td>
                <td><input type="text" value="<?php echo round(-1*$row['Base']*$row['Retencion']/100,2);?>" size="10" readonly class="formularios"></td>
            </tr>
            <tr>
                <td>Recargo:</td>
                <td><input name="recargo" value="<?php echo $row['Recargo'];?>" type="text" size="5" maxlength="10" class="formularios"></td>
                <td><input type="text" value="<?php echo round($row['Base']*$row['Recargo']/100,2);?>" size="10" readonly class="formularios"></td>
            </tr>
            <tr><td>TOTAL:</td><td></td><td><input name="total" value="<?php echo $row['Total'];?>" type="text" size="10" maxlength="10" class="blancoazul" readonly></td></tr>
            <tr><td>SALDO:</td><td></td><td><input name="saldo" value="<?php echo $row['Saldo'];?>" type="text" size="10" maxlength="10" class="<?php echo $color;?>" readonly></td></tr>
        </table>
    </td>
</tr>
<tr class="formularios">
    <td colspan="2" align="center">
    <?php if($row['IDRecibo']!=""){?>
        <?php if($esadm){?>
        <input name="Accion" value="Guardar" type="submit" class="formularios">
        <input name="Accion" value="Borrar" type="submit" class="formularios" onclick="return Confirma('<?php echo "Desea eliminar el recibo ",$campos['idrecibo'];?>');">
        <?php }?>
        <input name="Accion" value="Imprimir" type="button" class="formularios" onclick="window.open('recibopdf.php?d=<? echo $row['IDRecibo'];?>&h=<? echo $row['IDRecibo'];?>','Recibo','width=789,height=390,resizable=yes,scrollbars=yes,menubar=yes');Centrar('Recibo');">
        <input name="Accion" value="Cobrar" type="submit" class="formularios">&nbsp;
    <?php }?>
    </td>
</tr>
</table>
</form>

<TABLE align="center">
    <TR><TD>
	<?php if ($row['IDRecibo']!=''){
        $destino="editrecibolineas.php?Accion=Consulta&IDRecibo=".$row['IDRecibo'];
    ?>
    <iframe width="910" height="235" marginwidth="0"
            marginheight="0" hspace="0" vspace="0"
            frameborder="0" scrolling="yes"
            style="border-top:solid #FFFFFF 1px;border-right:solid #FFFFFF 1px;border-bottom:solid #78A3A5 1px;"
            src="<?php echo $destino;?>">
    </iframe>
	<?php }?>
    </TD></TR>
</TABLE>

