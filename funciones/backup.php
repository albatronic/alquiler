<?php
// This code was created by phpMyBackupPro v.2.1 
// http://www.phpMyBackupPro.net
// Recibe un array con el nombre de la/s base/s de dato/s a copiar.

$_POST['db']=$basesdedatos;
$_POST['tables']="on";
$_POST['data']="on";
$_POST['drop']="on";
$_POST['zip']="zip";
//$_POST['mysql_host']="-1";
$period=(3600*24)*0;
$security_key="";
// This is the relative path to the phpMyBackupPro v.2.1 directory
@chdir("../phpmybackupPro/");
include("backup.php");
@chdir("../alquiler/");
?>
