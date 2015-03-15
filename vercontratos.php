<?php
	if ($_SESSION['iu']=='') exit;
	$agente=$_SESSION['iu'];
	
    $f=$_GET['f'];
	if($f!=''){ //Borrar fichero
       @unlink('contratos/'.$f);
	}
?>
<table width="100%" align="center" bgcolor="#FFFF99" valign="top">
	<TR><Th>RELACION DE CONTRATOS</th></TR>
	<TR>
		<TD>
		<TABLE class="formularios">
		<?php
			$d = dir("contratos");
	     	while($registro=$d->read())
				if (($registro!='.') and ($registro!='..') and ($registro!='index.php')){
                ?>
				<tr>
                    <td><a href="contenido.php?c=vercontratos&f=<?echo $registro;?>"><img src="images/botonborrar.png" border='0'></a></td>
                    <td><a href="contratos/<?echo $registro;?>" target="_blank"><?php echo $registro;?></a></td>
                </tr>
			<?php }
	   		$d->close();
	  	?>
		</TABLE>
		</TD>
	</TR>
</table>
