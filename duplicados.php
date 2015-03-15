<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];
?>

<table ID="CUERPO_LISTADO" width="30%" align="center" valign="top" bgcolor="" class="combofamilias">
    <tr><th colspan="5" class="boxtitlewhite">RELACION DE ALQUILERES DUPLICADOS</th></tr>
	<tr class="Formularios">
            <th>C&oacute;digo</th>
        <th>Veces alquilado</th>
  	</tr>
<?php
Subrrayado(2);

$sql="select IDInmueble,count(IDInmueble) as N from $d.inmuebles_inquilinos where fechafin>='".date('Ymd')."' group by IDInmueble having N>1 order by N desc;";
$res=mysql_query($sql);

$i=0;
while ($row=mysql_fetch_array($res)){$i++;?>
    <tr	id="li<?php echo $i;?>" bgcolor="<?php echo $color;?>"
        onmouseover="<?php echo "cambiacolor('li",$i,"','#FFFF00');";?>"
	   	onmouseout="<?php echo "cambiacolor('li",$i,"','",$color,"');";?>"
    >
        <td><?php echo $row['IDInmueble'];?></td>
        <td align="right"><?php echo $row['N'];?></td>
    </tr>
<?php }
Subrrayado(2);
?>

    <tr><th colspan="5">TOTAL ALQUILERES DUPLICADOS <?php echo $i;?></th></tr>
</table>

