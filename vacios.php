<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];
$e=$_SESSION['DBEMP'];
?>

<table ID="CUERPO_LISTADO" width="100%" align="center" valign="top" bgcolor="#CCCCCC" class="formularios">
    <tr><th colspan="5" class="boxtitlewhite">RELACION DE INMUEBLES SIN ALQUILAR</th></tr>
	<tr class="Formularios">
        <th>Codigo</th>
        <th>Direccion Inmueble</th>
        <th>C.Postal</th>
        <th>Poblacion</th>
        <th>Provincia</th>
  	</tr>
<?php
Subrrayado(5);

$sql="select t1.*,t2.NOMBRE from $d.inmuebles as t1,$e.provincias as t2 where ISNULL(IDInquilino) and (t1.IDProvincia=t2.CODIGO) ORDER BY IDInmueble;";
$res=mysql_query($sql);

$i=0;
while ($row=mysql_fetch_array($res)){$i++;?>
    <tr	id="li<?php echo $i;?>" bgcolor="<?php echo $color;?>"
        onmouseover="<?php echo "cambiacolor('li",$i,"','#FFFF00');";?>"
	   	onmouseout="<?php echo "cambiacolor('li",$i,"','",$color,"');";?>"
    >
        <td><?php echo $row['IDInmueble'];?></td>
        <td><?php echo $row['Direccion'];?></td>
        <td><?php echo $row['CodigoPostal'];?></td>
        <td><?php echo $row['Poblacion'];?></td>
        <td><?php echo $row['NOMBRE'];?></td>
    </tr>
<?php }
Subrrayado(5);
?>

    <tr><th colspan="5">TOTAL INMUEBLES SIN ALQUILAR <?php echo $i;?></th></tr>
</table>

