<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";

$accion=$_POST['accion'];
$unidad=$_POST['unidad'];

$e=$_SESSION['DBEMP'];
$d=$_SESSION['DBDAT'].$_SESSION['empresa'];


switch ($accion) {
	case 'Si':
        echo "<table class='formularios' align='center' border='1' bgcolor='#CCCCCC'><tr><th>TABLA</th><th>Estado</th></tr>";

        $sql="SHOW TABLES FROM $e";
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            $fichero=$e.".".$row[0];
            $sql = "LOAD DATA INFILE '$unidad$fichero.txt' REPLACE
                    INTO TABLE $fichero
                    FIELDS TERMINATED BY '$SEPARADOR'
                    ENCLOSED BY '$ENCLOSED'
                    ESCAPED BY '\\\'
                    LINES TERMINATED BY '$SALTOLINEA' ;";
            $res1=mysql_query($sql);
            if ($res1=='1') $estado="OK"; else $estado=$sql;
            echo "<tr><td>",$fichero,"</td><td>",$estado,"</td></tr>";
        }

        mysql_query("TRUNCATE $d.recibos"); //VACIAR LA TABLA DE RECIBOS
        $sql="SHOW TABLES FROM $d";
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            $fichero=$d.".".$row[0];
            $sql = "LOAD DATA INFILE '$unidad$fichero.txt' REPLACE
                    INTO TABLE $fichero
                    FIELDS TERMINATED BY '$SEPARADOR'
                    ENCLOSED BY '$ENCLOSED'
                    ESCAPED BY '\\\'
                    LINES TERMINATED BY '$SALTOLINEA' ;";
            $res1=mysql_query($sql);
            if ($res1=='1') $estado="OK"; else $estado="ERROR";
            echo "<tr><td>",$fichero,"</td><td>",$estado,"</td></tr>";
        }
		echo "</table>";
        break;
		
	case '':
        $unidad=DameParametro('UCSEG','** FALTA DEFINIR EL PARAMETRO "UCSEG"');
        ?>
        <table class="formularios" align="center">
        <tr><td height="50"></td></tr>
        <tr><th colspan="2" class="blancoazul">PROCESO DE RECUPERACION DE COPIA DE SEGURIDAD</th></tr>
        <tr><td height="30"></td></tr>
        <tr>
        <td>
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="restore">
            Unidad origen:<input name="unidad" type="text" size="50" value="<?echo $unidad;?>" class="formularios">
            <br><br>
            Tenga en cuenta, que todos los datos existentes serán sustituidos<br>
            con los de la última copia de seguridad<br><br>
            ¿Recuperar copia de seguridad de la base de datos? <input name="accion" value="Si" type="submit" class="formularios">
            </form>
        </td>
        <td align="left" valign="bottom">
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="inicial">
            <input name="accion" value="No" type="submit" class="formularios">
            </form>
        </td>
        </tr>
        <?php
        break;
}
?>
