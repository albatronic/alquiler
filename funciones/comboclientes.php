<?php
// Muestra una lista desplegable de clientes. Y pone como seleccionado el cliente
// cuyo código se pasa como parámetro.

function ComboClientes($id){?>
<select name="IDCliente" class="ComboFamilias">
	<option value="">Indique un cliente</option>
	<?php
	$res=mysql_query("select IDCliente, RazonSocial from clientes order by RazonSocial");
	while ($cli=mysql_fetch_array($res)){?>
		<option value="<?php echo $cli['IDCliente'];?>" <?php if ($cli['IDCliente']==$id) echo "SELECTED";?>><?php echo $cli['RazonSocial'];?></option>
	<?php }?>
</select>

<?php
}
?>
