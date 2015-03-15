<?php
if ($_SESSION['iu']=='') exit;
$agente=$_SESSION['iu'];

$f=$_GET['f'];
if($f!=''){ //Borrar fichero
    @unlink('log/'.$f);
}
?>
<table width="100%" align="center" bgcolor="#FFFF99" valign="top">
    <TR><Th>FICHEROS HISTORICOS DE GENERACION DE RECIBOS</th></TR>
    <TR>
        <TD>
            <TABLE class="formularios">
            <?php
                    $d = dir("log");
            while($registro=$d->read())
                if (($registro!='.') and ($registro!='..') and ($registro!='index.php')){
                $nombre=substr($registro,0,-4); //Le quita el punto y la extensión
            ?>
                            <tr>
                <td><a href="contenido.php?c=logrecibos&f=<?echo $registro;?>"><img src="images/botonborrar.png" border='0'></a></td>
                <td><a href="contenido.php?c=log/<?echo $nombre;?>" target="_blank"><?php echo $nombre;?></a></td>
            </tr>
            <?php }
            $d->close();
            ?>
            </TABLE>
        </TD>
    </TR>
</table>
