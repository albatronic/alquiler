<?php
session_start();
if ($_SESSION['iu']=='') exit;
$idagente=$_SESSION['iu'];
$esadm=$_SESSION['esadm'];

require "conecta.php";

$accion=$_POST['accion'];
$unidad=$_POST['unidad'];


switch ($accion) {
	case 'Si':
        $e=$_SESSION['DBEMP'];
        $d=$_SESSION['DBDAT'].$_SESSION['empresa'];
        $basesdedatos=array($e,$d);
        require "funciones/backup.php";
	
        break;
		
	case '':
        $unidad=DameParametro('UCSEG','** FALTA DEFINIR EL PARAMETRO "UCSEG"');
        ?>
        <table class="formularios" align="center">
        <tr><td height="50"></td></tr>
        <tr><th colspan="2" class="blancoazul">PROCESO DE COPIA DE SEGURIDAD</th></tr>
        <tr><td height="30"></td></tr>
        <tr>
        <td>
            <form name="form" action="contenido.php" method="POST">
            <input name="c" type="hidden" value="backup">
            ¿Desea hacer copia de seguridad de la base de datos? <input name="accion" value="Si" type="submit" class="formularios">
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
