<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "funciones/recibos.php";
require "funciones/desplegable.php";
require "funciones/fechas.php";
require "modulos.php";
require "engancha.php";

$EMP=$_SESSION['DBEMP'];
$DAT=$_SESSION['DBDAT'].$_SESSION['empresa'];

//RECOGIDA DE PARAMETROS
//-----------------------------------------------------------------------------

$accion=$_POST['Accion'];
if ($accion=='') $accion=$_GET['Accion'];

$clave=$_GET['IDRecibo'];
if ($clave=='') $clave=$_POST['IDRecibo'];
$campos['idrecibo']=$clave;
if ($campos['idrecibo']=='') {
	Mensaje("SE HA PERDIDO EL ENLACE CON EL N. DE RECIBO.");
	exit;	
}
if (!$esadm) {
	$accion=''; //Para que no haga nada, solo mostrar las l�neas
}

$filtro="where (IDRecibo='".$campos['idrecibo']."')";	
$parametros="clave=".$clave;

	
//Parametros de formulario de Mantenimiento
$campos['idlinea']=$_POST['IDLinea'];
$campos['idconcepto']=$_POST['IDConcepto'];
$campos['fecha']=$_POST['Fecha'];
$campos['periodo']=$_POST['Periodo'];
$campos['unidades']=$_POST['Unidades'];
if ($campos['unidades']=='') $campos['unidades']=1;
$campos['precio']=$_POST['Precio'];
$campos['valoractual']=$_POST['ValorActual'];
$campos['importe']=$campos['unidades']*$campos['precio']*(1-$campos['descuento']/100);
$campos['valoranterior']=$_POST['ValorAnterior'];
?>

<?PHP
//CONTROL DE LA ACCION A REALIZAR
//-------------------------------------------------------
switch ($accion) {
	case 'Limpiar':
		Limpia();
		break;
		
	case 'G':
        $error="";
		if ($campos['idconcepto']=='') $error="Debe indicar un codigo de concepto";
		if ($error==''){
			$sql="select Precio,Consumo from $EMP.conceptos where IDConcepto='".$campos['idconcepto']."';";
			$res=mysql_query($sql);
			$con=mysql_fetch_array($res);
			if ($con['Precio']!=''){
                if($campos['precio']=='') $campos['precio']=$con['Precio'];
                if($con['Consumo']=='N'){
                    $campos['unidades']=1;
                    $campos['importe']=round($campos['precio']/$campos['valoranterior']*$campos['valoractual'],2);
                }
                if($con['Consumo']=='S'){
                    $campos['unidades']=$campos['valoractual']-$campos['valoranterior'];
                    $campos['importe']=$campos['unidades']*$campos['precio'];
                }

	        	$sql="update recibos_lineas set IDConcepto='".$campos['idconcepto']."', Fecha='".AlmacenaFecha($campos['fecha'])."', Unidades='".$campos['unidades']."', Precio='".$campos['precio']."',
						Periodo='".$campos['periodo']."', Importe='".$campos['importe']."', ValorAnterior='".$campos['valoranterior']."', ValorActual='".$campos['valoractual']."'
						where (IDRecibo='".$campos['idrecibo']."' and IDLinea='".$campos['idlinea']."') limit 1;";
        		$res=mysql_query($sql);
				if ($res)
					{	TotalizaRecibo($campos['idrecibo']);
						Limpia();
					}
				else Mensaje("No se han podido actualizar los datos. Intentelo de nuevo");
			} else Mensaje("El concepto no existe");
		} else Mensaje($error);
		break;

	case 'B':
		if (BorrarLinea($campos['idrecibo'],$campos['idlinea'])) Limpia();
		else Mensaje("No se han podido eliminar la linea. Intentelo de nuevo");
		break;

	case 'Crear':
        $error="";
		if ($campos['idconcepto']=='') $error="Debe indicar un codigo de concepto";
		if ($error==''){
			$sql="select Precio,Consumo from $EMP.conceptos where IDConcepto='".$campos['idconcepto']."';";
			$res=mysql_query($sql);
			$con=mysql_fetch_array($res);
			if ($con['Precio']!=''){
                if($campos['precio']=='') $campos['precio']=$con['Precio'];
                if($con['Consumo']=='N'){
                    $campos['unidades']=1;
                    $campos['importe']=round($campos['precio']/$campos['valoranterior']*$campos['valoractual'],2);
                }
                if($con['Consumo']=='S'){
                    $campos['unidades']=$campos['valoractual']-$campos['valoranterior'];
                    $campos['importe']=$campos['unidades']*$campos['precio'];
                }
				
		    	$valores="'".$campos['idrecibo']."','".AlmacenaFecha($campos['fecha'])."','".$campos['idconcepto']."','".$campos['periodo']."','".$campos['valoranterior']."','".
                	   $campos['valoractual']."','".$campos['unidades']."','".$campos['precio']."','".$campos['importe']."'";
    	    	$sql="INSERT INTO recibos_lineas
						(IDRecibo, Fecha, IDConcepto, Periodo, ValorAnterior, ValorActual, Unidades, Precio, Importe)
						VALUES (".$valores.")";
				$res=mysql_query($sql);
				if ($res) {TotalizaRecibo($campos['idrecibo']); Limpia();}
				else Mensaje("NO SE HA PODIDO CREAR LA LINEA. INTENTELO DE NUEVO");
			} else Mensaje("El concepto no existe");
		} else Mensaje($error);
		break;
}

function Limpia(){
	global $campos;
	$aux=$campos['idrecibo'];
	$campos="";
	$campos['idrecibo']=$aux;
	$campos['unidades']=1;
	$campos['precio']=0;
};
$gris="#CCCCCC";

?>
	
<HTML>
<HEAD>
    <TITLE>Albatronic</TITLE>
	<link href="estilos.css" rel="stylesheet" type="text/css">
</HEAD>

	<body topmargin="0" leftmargin="10" marginwidth="2" marginheight="2" style="border:0" bgcolor="">
            <noscript>Tu navegador no soporta JavaScript. La p&aacute;gina no funcionar&aacute; correctamente! </noscript>

<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC">
	<tr class="Formularios">
    <th>Concepto</th>
    <th>Fecha</th>
    <th>Periodo</th>
    <th>Precio</th>
    <th>V.Anterior/<br>Dias Mes</th>
    <th>V.Actual/<br>Dias</th>
    <th>Unidades</th>
    <th>Importe</th>
    <th></th>
  	</tr>


<?php	
	Subrrayado(9);
	$sql="select * from recibos_lineas $filtro order by IDLinea";
	$res=mysql_query($sql);
	$i=0;
	while ($row=mysql_fetch_array($res)){ $i=$i+1;?>
            <form name="formulario<?php echo $i;?>" action="editrecibolineas.php" method="post">
            <input name="IDRecibo" type="hidden" value="<?php echo $row['IDRecibo'];?>">
            <input name="IDLinea" type="hidden" value="<?php echo $row['IDLinea'];?>">
            <tr id="l<?php echo $i;?>"
                    onmouseover="<?php echo "cambiacolor('l",$i,"','#FFFF00');";?>"
                    onmouseout="<?php echo "cambiacolor('l",$i,"','",$gris,"');";?>"
            >
                <td>
                    <?php Desplegable("IDConcepto",$EMP.".conceptos","IDConcepto","Concepto","IDConcepto",$row['IDConcepto'],"","formularios","");?>
                    <?php if ($esadm){?><a href="javascript:;" onClick="window.open('contenido.php?c=conceptos','Conceptos','menubar=yes')"><img src="images/lupa.png" border=0></a><?php }?>
                </td>
                <td><input name="Fecha" value="<?php echo FechaEspaniol($row['Fecha']);?>" type="text" size="10" maxlength="10" class="formularios"></td>
                <td><input name="Periodo" value="<?php echo $row['Periodo'];?>" type="text" size="21" maxlength="21" class="formularios"></td>
                <td align="right"><input name="Precio" value="<?php echo $row['Precio'];?>" type="text" size="12" maxlength="12" class="formularios"></td>
                <td align="right"><input name="ValorAnterior" value="<?php echo $row['ValorAnterior'];?>" type="text" size="7" maxlength="10" class="formularios"></td>
                <td align="right"><input name="ValorActual" value="<?php echo $row['ValorActual'];?>" type="text" size="7" maxlength="10" class="formularios"></td>
                <td align="right"><input name="Unidades" value="<?php echo $row['Unidades'];?>" readonly type="text"  size="7" maxlength="10" class="formularios"></td>
                <td align="right"><input name="Importe" value="<?php echo $row['Importe'];?>" readonly type="text" size="7" maxlength="10" class="formularios"></td>
                <td align="right">
                    <?php if ($esadm) {?>
                    <input name="Accion" value="G" type="submit" class="formularios">
                    <input name="Accion" value="B" type="submit" class="formularios" onclick="return Confirma('Desea Eliminar la linea');">
                    <?php }?>
                </td>
            </tr>
            </form>
<?php }?>

	<form name="formulario0" action="editrecibolineas.php" method="post">
		<input name="IDRecibo" type="hidden" value="<?php echo $campos['idrecibo'];?>">
		<input name="IDLinea" type="hidden" value="0">
	<?php if ($esadm){?>
	<TR ID="CUERPO" class="formularios">
            <td>
        <?php Desplegable("IDConcepto",$EMP.".conceptos","IDConcepto","Concepto","IDConcepto",'',"","formularios","");?>
                    <?php if ($esadm){?><a href="javascript:;" onClick="window.open('contenido.php?c=conceptos','Conceptos','menubar=yes')"><img src="images/lupa.png" border=0></a><?php }?>
            </td>
            <td><input name="Fecha" value="<?php echo date('d/m/Y');?>" type="text" size="10" maxlength="10" class="formularios"></td>
            <td><input name="Periodo" type="text" size="21" maxlength="21" value="" class="formularios"></td>
            <td align="right"><input name="Precio" value="" type="text" size="12" maxlength="12" class="formularios"></td>
            <td align="right"><input name="ValorAnterior" type="text" size="7" maxlength="10" value="0" class="formularios"></td>
            <td align="right"><input name="ValorActual" type="text" size="7" maxlength="10" value="0" class="formularios"></td>
            <td align="right"><input name="Unidades" type="text" size="7" maxlength="10" value="1" readonly class="formularios"></td>
            <td align="right"><input name="Importe" type="text" size="7" maxlength="10" value="0" readonly class="formularios"></td>
	</tr>
	<?php }?>

	<?php Subrrayado(9);?>
	<tr id="PIE">
            <td align="left" colspan="4" class="formularios">
            </td>
            <td align="right" colspan="8">
                <?php
                 if ($esadm){?>
                        <input name="Accion" type="submit" value="Crear" class='formularios'>
                        <input name="Accion" type="submit" value="Limpiar" class='formularios'>
                <?php }?>
            </td>
	</tr>
	<?php Subrrayado(9);?>
	</form>
	<script language="JavaScript" type="text/javascript">
	document.formulario0.IDConcepto.focus();
	</script>
</table>
</body> 
</HTML>
