<?php
// GENERA UNA LISTA DESPLEGABLE CON LOS SIGUIENTES PARAMETROS:
//	$nombre			: nombre que tendr� la lista dentro del formulario
//	$tabla			: tabla de la que obtener los datos
//	$columnaclave	: nombre de la columna de la tabla que servir� como clave del select
//	$columnatexto	: nombre de la columna de la tabla que servir� como texto a mostrar en el select
//	$columnaorden	: nombre de la columna de la tabla por la que se realiza el 'order by'
//	$claveselected	: valor que se quiere que aparezca como seleccionado en la lista desplegable.

function Desplegable($nombre,$tabla,$columnaclave,$columnatexto,$columnaorden,$claveselected,$evento,$clase,$estilo){?>

<select name='<?php echo $nombre;?>' <?php echo $evento," ";?>class='<?php if ($clase=='') echo "ComboFamilias"; else echo $clase;?>'<?php if ($estilo!='') echo " style='",$estilo,"'";?>>
	<option value=''>::Indique un valor</option>
	<?php
	$sql="select ".$columnaclave.",".$columnatexto." from ".$tabla." order by ".$columnaorden;
	$res=mysql_query($sql);
	while ($row=mysql_fetch_array($res)){
		echo "<option value='",$row[$columnaclave],"'";
		if ($row[$columnaclave]==$claveselected) echo "SELECTED";
		echo ">",$row[$columnatexto],"</option>\n";	
	}?>
</select>
<?php
}

function DesplegableSucursal($nombre,$empresa,$claveselected,$evento){?>
<select name='<?php echo $nombre;?>' <?php echo $evento," ";?>class="ComboFamilias">
	<option value=''>::Indique una Sucursal</option>
    <?php
	$sql="select IDSucursal,Nombre from ".$_SESSION['DBEMP'].".sucursales where(IDEmpresa=$empresa) order by Nombre";
	$res=mysql_query($sql);
	while ($row=mysql_fetch_array($res)){?>
		<option value="<?php echo $row['IDSucursal'];?>"<?php if ($row['IDSucursal']==$claveselected) echo " SELECTED";?>><?php echo $row['Nombre'];?></option>	
	<?php }?>
</select>
<?php
}

function DesplegableBanco($nombre,$claveselected,$clase,$evento){?>
<select name='<?php echo $nombre;?>' <?php echo $evento," ";?>class='<?php echo $clase;?>'>
	<option value=''>::Indique un Banco</option>
    <?php
	$sql="select IDBanco,Banco from ".$_SESSION['DBEMP'].".bancos order by IDBanco";
	$res=mysql_query($sql);
	while ($row=mysql_fetch_array($res)){?>
		<option value="<?php echo $row['IDBanco'];?>"<?php if ($row['IDBanco']==$claveselected) echo " SELECTED";?>><?php echo $row['IDBanco'],"-",$row['Banco'];?></option>	
	<?php }?>
</select>
<?php
}

function DesplegableDias($nombre,$dia,$estilo){
$d[1]="01";$d[2]="02";$d[3]="03";
$d[4]="04";$d[5]="05";$d[6]="06";
$d[7]="07";$d[8]="08";$d[9]="09";
$d[10]="10";$d[11]="11";$d[12]="12";
$d[13]="13";$d[14]="14";$d[15]="15";
$d[16]="16";$d[17]="17";$d[18]="18";$d[19]="19";
$d[20]="20";$d[21]="21";$d[22]="22";
$d[23]="23";$d[24]="24";$d[25]="25";
$d[26]="26";$d[27]="27";$d[28]="28";
$d[29]="29";$d[30]="30";$d[31]="31";
?>
<select name="<?php echo $nombre;?>" class="secciones" style="<?php echo $estilo;?>">
  <?php
        $i=1;
        while ($i<32){?>
                <option value="<?php echo $d[$i];?>"<?php if ($i==$dia) echo " SELECTED";?>><?php echo $d[$i];?></option>
        <?php
                $i=$i+1;
        }?>
</select>
<?php

}

function DesplegableMeses($nombre,$mes,$estilo){
$m[1][1]="Enero"; $m[1][0]="01";
$m[2][1]="Febrero"; $m[2][0]="02";
$m[3][1]="Marzo"; $m[3][0]="03";
$m[4][1]="Abril"; $m[4][0]="04";
$m[5][1]="Mayo"; $m[5][0]="05";
$m[6][1]="Junio"; $m[6][0]="06";
$m[7][1]="Julio"; $m[7][0]="07";
$m[8][1]="Agosto"; $m[8][0]="08";
$m[9][1]="Septiembre"; $m[9][0]="09";
$m[10][1]="Octubre"; $m[10][0]="10";
$m[11][1]="Noviembre"; $m[11][0]="11";
$m[12][1]="Diciembre"; $m[12][0]="12";
?>
        <select name="<?php echo $nombre;?>" class="secciones" style="<?php echo $estilo;?>">
		  <?php
		  	$i=1;
			while ($i<13){?>
				<option value="<?php echo $m[$i][0];?>"<?php if ($i==$mes) echo " SELECTED";?>><?php echo $m[$i][1];?></option>
			<?php
				$i=$i+1;
			}?>
        </select>
<?php
}

function DesplegableAno($nombre,$ano,$estilo){
?>
        <select name="<?php echo $nombre;?>" class="formularios" style="<?php echo $estilo;?>">
            <option value="0">Todos</option>
		  <?php
		  	$i=2000;
			while ($i<2050){?>
				<option value="<?php echo $i;?>"<?php if ($i==$ano) echo " SELECTED";?>><?php echo $i;?></option>
			<?php
				$i=$i+1;
			}?>
        </select>
<?php
}

function DesplegableSN($nombre,$valor,$clase){
?>
    <select name="<?php echo $nombre;?>" class="<?php echo $clase;?>">
        <option value="S" <?php if ($valor=="S") echo "selected";?>>Si</option>
        <option value="N" <?php if ($valor=="N") echo "selected";?>>No</option>
    </select>
<?php
}


function DesplegablePlantillas($nombre,$carpeta,$valor,$evento,$clase){
?>
    <select name="<?php echo $nombre;?>" <?php echo $evento," ";?>class="<?php echo $clase;?>">
        <option value="">Seleccione un formato</option>
<?php
    $d=dir($carpeta);
   	while($c=$d->read())
    	if (($c!='.') and ($c!='..') and ($c!='index.php'))
	{?>
		<option value="<?php echo $c;?>" <?php if ($c==$valor) echo " SELECTED";?>><?php echo $c;?></option>
	<?php
	}
	$d->close();
	?>
    </select>
<?php
}
?>



