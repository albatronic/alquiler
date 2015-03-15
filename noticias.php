<?php
	if ($_SESSION['iu']=='') exit;
	require "engancha.php";
	
	$hoy=date("Y-m-d");

	$res=mysql_query("select * from noticias where (Vigencia>='$hoy')");
	if (mysql_num_rows($res)){ 
?>
<table name="NOTICIAS" width="100%" align="center" bgcolor="#FFFF99" valign="top">
  <tr>
    <td colspan="2" align="center" class="BlancoFondoRojo">!! NOTICIAS DE INTERES ¡¡</td>
  </tr>
  <?php
	Subrrayado(2);
	while ($row=mysql_fetch_array($res)) {
		if ($row['Emergente']=='0'){?>
        <tr class='Formularios'>
            <td><img src="images/bola.gif"></td>
            <td><?php echo $row['Noticia'];?></td>
        </tr>
  <?php
		Subrrayado(2);
		} else AbreVentana("popupnoticia.php?id=".$row['IDNoticia'],"noticia".$row['IDNoticia'],"width=300,height=350, scrollbar=yes");
	}
?>
    <tr><td height="10" colspan="2"></td></tr>
</table>
<?php
}
?>
