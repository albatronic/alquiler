<?php>
    //SI NO EXISTE EL LOGO DE FONDO 'logoalba.png', NO MUESTRO NINGUNO
    $f="images/logoalba.png";
    if (!file_exists($f)) $f="";
?>
<table width="100%" align="center" background="">
<tr height="20"><td></td></tr>
<tr><td align="center"><img src="<?php echo $f;?>" height="350" border="0"</td></tr>
</table>
