<?php
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

$EMP=$_SESSION['DBEMP'];
$DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

require "engancha.php";
require "funciones/fechas.php";
require "funciones/desplegable.php";
require "funciones/recibos.php";


$tampagina=DameParametro('LOPAP',18);

$regpag=$_GET['regpag']; if($regpag=='') $regpag=$_POST['regpag'];
if($regpag=='') $regpag=$tampagina; else $tampagina=$regpag;

$pagina=$_GET['pagina'];
if (!isset($pagina)) $pagina=$_POST['pagina'];
if (!isset($pagina)) $pagina=1;

$resumido=$_POST['resumido'];
if($resumido=='') $resumido=$_GET['resumido'];
if($resumido=='') $resumido="S";

$accion=$_POST['Accion'];
if (!isset($accion)) $accion=$_GET['Accion'];

$campo=$_GET['campo'];
if($campo=='') $campo=$_POST['campo'];
if($campo=='') $campo="recibos.IDRecibo";

$desde = $_GET['desde']; if ($desde == '') $desde = $_POST['desde'];
$hasta = $_GET['hasta']; if ($hasta == '') $hasta = $_POST['hasta'];

if($_POST['cambio']=='S'){
    switch ($_POST['campo']){
        case 'recibos.IDRecibo':
            $desde='A00000'; $hasta='Z99999';
            $loncampo=6;
            break;
        case 'recibos.Fecha':
            $desde='01/01/'.date('Y'); $hasta='31/12/'.date('Y');
            $loncampo=10;
            break;
        case 'recibos.IDInquilino':
            $desde=''; $hasta='ZZZZZZZZZZ';
            $loncampo=10;
            break;
        case 'recibos.IDInmueble':
            $desde=''; $hasta='ZZZZZZ';
            $loncampo=6;
            break;
        case 'SUBSTRING(recibos.CuentaAbono,1,8)':
            $desde='00000000'; $hasta='99999999';
            $loncampo=8;
            break;
        case 'SUBSTRING(recibos.IDRemesa,1,8)':
            $desde='00000000'; $hasta='99999999';
            $loncampo=8;
            break;
        case 'recibos.Saldo':
            $desde='0.01'; $hasta='99999999';
            $loncampo=8;
            break;
    }
}

switch ($accion){
    case 'Imprimir':
        $d=$desde;$h=$hasta;
        if($campo=='recibos.Fecha') {$d=AlmacenaFecha($d);$h=AlmacenaFecha($h);}
        $url="recibopdf.php?orden=$orden&d=$d&h=$h&columna=$campo";
        AbreVentana($url,'Recibo','width=810,resizable=yes,scrollbars=yes,menubar=yes');
    break;

    case 'Recalcular Saldo':
        $d=$desde;$h=$hasta;
        if($campo=='recibos.Fecha') {$d=AlmacenaFecha($d);$h=AlmacenaFecha($h);}

        $sql="select IDRecibo from recibos where $campo>='$d' and $campo<='$h';";
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)) TotalizaRecibo($row['IDRecibo']);
    break;

    case 'Cobrar':
        AbreVentana('contenido.php?c=cobrarremesa&t=Cobrar Remesa&campo='.$campo.'&desde='.$desde.'&hasta='.$hasta,'CobrarRemesa','resizable=yes,scrollbars=yes,menubar=no,width=300,height=170');
    break;
    
    case 'Borrar':
        $d=$desde;$h=$hasta;
        if($campo=='recibos.Fecha') {$d=AlmacenaFecha($d);$h=AlmacenaFecha($h);}

        $sql="select IDRecibo from recibos where $campo>='$d' and $campo<='$h';";
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            $res1=mysql_query("delete from recibos_lineas where IDRecibo='".$row[0]."';");
            if ($res1) $res1=mysql_query("delete from recibos where IDRecibo='".$row[0]."';");
            //if (!$res1) Mensaje("No se ha podido borrar el recibo");
        }
    break;
}
?>

<table width="100%" align="center" class="combofamilias">
    <tr><th class="blancoazul">BUSQUEDA DE RECIBOS</th></tr>

    <form name="formulario" action="contenido.php" method="post">
    <input name="c" type="hidden" value="consultarecibos">
    <input name="cambio" type="hidden" value="N">

    <tr>
        <td align="center">
            Buscar por:
            <select name="campo" class="formularios" onchange="cambio.value='S';submit();">
                <option value="recibos.IDRecibo" <?php if ($campo=="recibos.IDRecibo") echo "SELECTED";?>>N. Recibo</option>
                <option value="recibos.Fecha" <?php if ($campo=="recibos.Fecha") echo "SELECTED";?>>Fecha</option>
                <option value="recibos.IDInquilino" <?php if ($campo=="recibos.IDInquilino") echo "SELECTED";?>>Inquilino</option>
                <option value="recibos.IDInmueble" <?php if ($campo=="recibos.IDInmueble") echo "SELECTED";?>>Inmueble</option>
                <option value="SUBSTRING(recibos.CuentaAbono,1,8)" <?php if ($campo=="SUBSTRING(recibos.CuentaAbono,1,8)") echo "SELECTED";?>>Banco Remesa</option>
                <option value="SUBSTRING(recibos.IDRemesa,1,8)" <?php if ($campo=="SUBSTRING(recibos.IDRemesa,1,8)") echo "SELECTED";?>>Codigo Remesa</option>
                <option value="recibos.Saldo" <?php if ($campo=="recibos.Saldo") echo "SELECTED";?>>Saldo</option>
            </select>&nbsp
            Orden:
            <select name="orden" class="formularios" onchange="submit();">
                <option value="ASC" <?php if($orden=="ASC") echo "SELECTED";?>>ASC</OPTION>
                <option value="DESC" <?php if($orden=="DESC") echo "SELECTED";?>>DESC</OPTION>
            </select>
            Desde:<input name="desde" type="text" value="<?php echo $desde;?>" size="10" maxlength="<?php echo $loncampo;?>" class="formularios">&nbsp;
            Hasta:<input name="hasta" type="text" value="<?php echo $hasta;?>" size="10" maxlength="<?php echo $loncampo;?>" class="formularios">&nbsp;
            Resumido:<?php DesplegableSN('resumido',$resumido,'formularios');?>&nbsp
            Re.Pag:<input name="regpag" value="<?php echo $regpag;?>" type="text" size="4" class="formularios">&nbsp;
            <input name="Accion" value="Consulta" type="submit" class="formularios">
        </td>
    </tr>

    <tr>
        <td align="center">Sobre los seleccionados:&nbsp;
            <input name="Accion" value="Imprimir" type="submit" class="formularios">&nbsp;
            <?php if($esadm){?>
                <input name="Accion" value="Cobrar" type="submit" class="formularios">&nbsp;
                <input name="Accion" value="Recalcular Saldo" type="submit" class="formularios">&nbsp;
                <input name="Accion" value="Borrar" type="submit" class="formularios" onclick="return Confirma('ESTA SEGURO QUE DESEA BORRAR TODOS LOS RECIBOS SELECCIONADOS ?');">
            <?php }?>
        </td>
    </tr>
    </form>
</table>


<?php
    if($resumido=="S") ListadoResumido($campo,$desde,$hasta,$pagina);
    if($resumido=="N") ListadoDetallado($campo,$desde,$hasta,$pagina);

?>


<?php
function ListadoResumido($campo,$desde,$hasta,$pagina){
    global $tampagina,$regpag,$orden,$columnaorden;
    $de=$_SESSION['DBEMP'];
    		
    if($columnaorden=='') $columnaorden=$campo;
    $parametros="campo=$campo&desde=$desde&hasta=$hasta&resumido=S&regpag=$regpag&orden=$orden&columnaorden=$columnaorden";
    $d=$desde;$h=$hasta;
    if ($campo=='recibos.Fecha') {$d=AlmacenaFecha($desde);$h=AlmacenaFecha($hasta);}
    
    $filtro="($campo>='$d' and $campo<='$h' and $de.inquilinos.IDInquilino=recibos.IDInquilino and inmuebles.IDInmueble=recibos.IDInmueble)";
    $sql="select IDRecibo,recibos.IDInquilino,recibos.IDInmueble,Fecha,Total,Saldo,recibos.IDRemesa,inmuebles.Direccion,$de.inquilinos.RazonSocial from recibos,inmuebles,$de.inquilinos where $filtro ORDER BY $columnaorden $orden;";
    list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
    $tot=0;
    $totsaldo=0;
?>

<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="7">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=consultarecibos&".$parametros."&pagina=","left",$gris,"");?>
	</td></tr>
	<tr class="Formularios">
        <th colspan="2">
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDRecibo&resumido=",$resumido;?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Recibo
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDRecibo&resumido=",$resumido;?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.Fecha&resumido=",$resumido;?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Fecha
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.Fecha&resumido=",$resumido;?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInmueble&resumido=",$resumido;?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Inmueble
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInmueble&resumido=",$resumido;?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInquilino&resumido=",$resumido;?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Inquilino
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInquilino&resumido=",$resumido;?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>Importe</th>
        <th>Saldo</th>
  	</tr>
<?php	
	Subrrayado(7);

	$res=mysql_query($sql);
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
			$i=$i+1;
            if($row['Saldo']<>0) $color="BlancoFondoRojo"; else $color="BlancoFondoVerde";
            $tot=$tot+$row['Total'];
            $totsaldo=$totsaldo+$row['Saldo'];
		?>
			<tr class="Formularios" id="linea<?php echo $i;?>"
       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
	      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
		    	<td>
                    <a href="javascript:;" onclick="window.open('recibopdf.php?d=<?php echo $row['IDRecibo'];?>&h=<?php echo $row['IDRecibo'];?>','Recibo','width=850,height=390,resizable=yes,scrollbars=yes,menubar=yes');"><img src="images/imprimir.png" border="0" alt="Imprimir Recibo"></a>
                    <a href="javascript:;" onclick="window.open('contenido.php?c=editrecibo&t=Editar Recibo&Accion=Editar&idrecibo=<?php echo $row['IDRecibo'];?>','EditRecibo','width=960 height=500,resizable=yes,scrollbars=yes,menubar=yes');"><img src="images/botoneditar.png" border="0"></a>
                </td>
                <td><?php echo $row['IDRecibo']; if ($row['IDRemesa']!="") echo "<b>R</b>";?></td>
                <td><?php echo FechaEspaniol($row['Fecha']);?></td>
                <td><a href="contenido.php?c=consultarecibos&resumido=S&campo=recibos.IDInmueble&desde=<?php echo $row['IDInmueble'];?>&hasta=<?php echo $row['IDInmueble'];?>&regpag=<?php echo $regpag;?>"><?php echo $row['IDInmueble'];?></a><?php echo " ",substr($row['Direccion'],0,40);?></td>
                <td><a href="contenido.php?c=consultarecibos&resumido=S&campo=recibos.IDInquilino&desde=<?php echo $row['IDInquilino'];?>&hasta=<?php echo $row['IDInquilino'];?>&regpag=<?php echo $regpag;?>"><?php echo $row['IDInquilino'];?></a><?php echo " ",substr($row['RazonSocial'],0,20);?></td>
                <td align="right"><?php echo $row['Total'];?></td>
                <td align="right" class="<?php echo $color;?>"><?php echo $row['Saldo'];?></td>
			</tr>
<?php
		}
	}?>
    <tr class="formularios">
        <td colspan=6 align="right"><b><?php echo $tot;?></b></td>
        <td align="right"><b><?php echo $totsaldo;?></b></td>
    </tr>
	<tr><td colspan="7">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=consultarecibos&".$parametros."&pagina=","right",$gris,"");?>
	</td></tr>
</table>		
<?php }


function ListadoDetallado($campo,$desde,$hasta,$pagina){
	global $tampagina,$regpag,$orden,$columnaorden;
	$de=$_SESSION['DBEMP'];

    if($columnaorden=='') $columnaorden=$campo;
    $parametros="campo=$campo&desde=$desde&hasta=$hasta&resumido=N&regpag=$regpag&orden=$orden&columnaorden=$columnaorden";
    $d=$desde;$h=$hasta;
    if ($campo=='recibos.Fecha') {$d=AlmacenaFecha($desde);$h=AlmacenaFecha($hasta);}

    $filtro="($campo>='$d' and $campo<='$h' and recibos.IDRecibo=recibos_lineas.IDRecibo and recibos_lineas.IDConcepto=$de.conceptos.IDConcepto)";
    $sql="select recibos_lineas.*,recibos.IDInquilino,recibos.IDInmueble,recibos.Fecha,recibos.IDRemesa,$de.conceptos.Concepto from recibos_lineas,recibos,$de.conceptos where $filtro order by $columnaorden $orden,recibos.IDRecibo,recibos_lineas.IDConcepto;";
	list($desderegistro,$totalregistros,$totalpaginas)=Paginar($sql,$pagina,$tampagina);
?>

<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" >
    <tr><td colspan="11">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=consultarecibos&".$parametros."&pagina=","left",$gris,"lisrecibos.php?$parametros");?>
	</td></tr>
	<tr class="Formularios">
        <th colspan="2">
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDRecibo&resumido=N";?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Recibo
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDRecibo&resumido=N";?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInquilino&resumido=N";?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Inqui
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInquilino&resumido=N";?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInmueble&resumido=N";?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Inmu
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.IDInmueble&resumido=N";?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=ASC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.Fecha&resumido=N";?>"><img src="images/abajo.gif" border="0" alt="Ascendente"></a>
            Fecha
            <a href="contenido.php?c=consultarecibos&Accion=Consulta&orden=DESC<?php echo "&campo=",$campo,"&desde=",$desde,"&hasta=",$hasta,"&regpag=",$regpag,"&columnaorden=recibos.Fecha&resumido=N";?>"><img src="images/arriba.gif" border="0" alt="Descendente"></a>
        </th>
        <th>Concepto</th>
        <th>Precio</th>
        <th>V.Ant</th>
        <th>V.Actu</th>
        <th>Udes</th>
        <th>Importe</th>
  	</tr>
<?php	

	$res=mysql_query($sql);
	
	$ok=@mysql_data_seek($res,$desderegistro);
	if ($ok) {
		$i=1;
		$ant="";
		while ($row=mysql_fetch_array($res) and ($i<=$tampagina)) {
                    if ($row['IDRecibo']!==$ant) {
                        Subrrayado(11);$p=1;
                        $res1=mysql_query("select Direccion from inmuebles where IDInmueble='".$row['IDInmueble']."';");
                        $in=mysql_fetch_array($res1);
                        $res1=mysql_query("select RazonSocial from ".$_SESSION['DBEMP'].".inquilinos where IDInquilino='".$row['IDInquilino']."';");
                        $iq=mysql_fetch_array($res1);
                    }
            $ant=$row['IDRecibo'];
            $i=$i+1;
            if($row['Importe']<0) $color="BlancoFondoVerde"; else $color="";
		?>

			<tr class="Formularios" id="linea<?php echo $i;?>"
       			onmouseover="<?php echo "cambiacolor('linea",$i,"','#FFFF00'); nombreinmueble.value=in",$i,".value; nombreinquilino.value=iq",$i,".value;";?>"
	      		onmouseout="<?php echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
            >
		    	<td>
                    <input name="in<?php echo$i;?>" value="<?php echo $in[0];?>" type="hidden">
                    <input name="iq<?php echo$i;?>" value="<?php echo $iq[0];?>" type="hidden">
                    <?php if ($p){$p=0;?>
                    <a href="javascript:;" onclick="window.open('recibopdf.php?d=<? echo $row['IDRecibo'];?>&h=<?php echo $row['IDRecibo'];?>','Recibo','width=789,height=390,resizable=yes,scrollbars=yes,menubar=yes');"><img src="images/imprimir.png" border="0" alt="Imprimir Recibo"></a>
                    <a href="javascript:;" onclick="window.open('contenido.php?c=editrecibo&t=Editar Recibo&Accion=Editar&idrecibo=<?php echo $row['IDRecibo'];?>','EditRecibo','width=960,height=500,resizable=yes,scrollbars=yes,menubar=yes');"><img src="images/botoneditar.png" border="0"></a>
                    <?php }?>
                </td>
                <td><?php echo $row['IDRecibo']; if ($row['IDRemesa']!="") echo "<b>R</b>";?></td>
                <td><?php echo $row['IDInquilino'];?></td>
                <td><?php echo $row['IDInmueble'];?></td>
                <td><?php echo FechaEspaniol($row['Fecha']);?></td>
                <td><?php echo $row['IDConcepto']," ",$row['Concepto'];?></td>
                <td align="right"><?php echo $row['Precio'];?></td>
                <td align="right"><?php echo $row['ValorAnterior'];?></td>
                <td align="right"><?php echo $row['ValorActual'];?></td>
                <td align="right"><?php echo $row['Unidades'];?></td>
                <td align="right" class="<?php echo $color;?>"><?php echo $row['Importe'];?></td>
			</tr>
        <?php }
        Subrrayado(11);
	}?>
    <tr class="blancoazul"><td colspan="12" class="formularios">
        Inmueble:&nbsp<input name="nombreinmueble" value="" type="text" size="50" class="formularios" readonly>
        Inquilino:&nbsp<input name="nombreinquilino" value="" type="text" size="50" class="formularios" readonly>
    </td></tr>
    
	<tr><td colspan="11">
	<?php Paginacion($pagina,$totalpaginas,$totalregistros,"contenido.php?c=consultarecibos&".$parametros."&pagina=","right",$gris,"lisrecibos.php?$parametros");?>
	</td></tr>
</table>		
<?php }?>
