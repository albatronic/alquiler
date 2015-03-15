<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";

$accion=$_POST['accion'];


switch ($accion) {
	case 'Si':
        echo "<table class='formularios' align='center' border='1' bgcolor='#CCCCCC'><tr><th>TABLA</th><th>Estado</th></tr>";

        $sql="SHOW TABLES FROM ".$_SESSION['DBEMP'];
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            $res1=mysql_query("OPTIMIZE TABLE ".$_SESSION['DBEMP'].".".$row[0]);
            $estado=mysql_fetch_array($res1);
            echo "<tr><td>",$estado[0],"</td><td>",$estado[3],"</td></tr>";
        }

        $sql="SHOW TABLES FROM ".$_SESSION['DBDAT'].$_SESSION['empresa'];
        $res=mysql_query($sql);
        while ($row=mysql_fetch_array($res)){
            $res1=mysql_query("OPTIMIZE TABLE ".$_SESSION['DBDAT'].$_SESSION['empresa'].".".$row[0]);
            $estado=mysql_fetch_array($res1);
            echo "<tr><td>",$estado[0],"</td><td>",$estado[3],"</td></tr>";
        }
		echo "</table>";
        break;
		
	case '':
        ?>
        <table class="formularios" align="center"><tr>
        <td>
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="optimizar">
            ¿Desea optimizar la base de datos? <input name="accion" value="Si" type="submit" class="formularios">
            </form>
        </td>
        <td align="left">
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="inicial">
            <input name="accion" value="No" type="submit" class="formularios">
            </form>
        </td>
        <?php
        break;
}
?>
