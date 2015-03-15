<?php

$AMARILLO="#FFFF00";
$ROJO="#FF0000";
$FONDOMENU="";

$SEPARADOR="\\t";
$SALTOLINEA="\\r\\n";
$ENCLOSED="\"";

function Resto($x,$y){
    return $x%$y;
}

function SacaFoto($idarti,$tamano){
    list($foto,$ancho,$alto)=MedidasFoto("catalogo/".$idarti);
	echo "<a href=\"javascript:;\" onClick=\"MM_openBrWindow('showitem.php?idarticulo=$idarti','imagen','width=650,height=525')\">";
	if ($ancho>$alto)
		echo "<img title='Ver ficha detallada del producto' border=0 width=",$tamano," src=$foto></a>";
	else
		echo "<img title='Ver ficha detallada del producto' border=0 height=",$tamano," src=$foto></a>";
}

function FotoInquilino($id,$tamano){
	list($foto,$ancho,$alto)=MedidasFoto("inquilinos/".$id);
	if ($ancho>$alto)
		echo "<img border=0 width=",$tamano," src='$foto'>";
	else
		echo "<img border=0 height=",$tamano," src='$foto'>";
}

function MedidasFoto($id) {
//Devuelve el nombre completo de la foto y sus medidas

	$imagengif=$id.".gif";
	$imagenjpg=$id.".jpg";

	if (file_exists($imagengif)) $foto=$imagengif;
	elseif (file_exists($imagenjpg)) $foto=$imagenjpg; else $foto="inquilinos/sinfoto.gif";
	$size = GetImageSize($foto);
	return array($foto,$size[0],$size[1]);
}

function Subrrayado($ncolumnas) {
	echo "<tr><td colspan=$ncolumnas background='images\subrrayado.gif'></td></tr>";
}

function Atras($mensaje){
	echo "<script language='JavaScript' type='text/JavaScript'>
			alert('$mensaje')
			history.go(-1)
		</script>";
}

function Mensaje($mensaje){
	echo "<script language='JavaScript' type='text/JavaScript'>
			alert('$mensaje')
		</script>";
}

function AbreVentana($url,$nombre,$parametros){
	echo "<script language='JavaScript' type='text/JavaScript'>
			window.open('".$url."','".$nombre."','".$parametros."')
		</script>";
}

function RecargaVentana($url){
	echo "<script language='JavaScript' type='text/JavaScript'>
			document.location.href='".$url."'
		</script>";
}

function CierraVentana(){
	echo "<script language='JavaScript' type='text/JavaScript'>
			window.close();
		</script>";
}

function Imprimir(){
	echo "<script language='JavaScript' type='text/JavaScript'>
			window.print();
		</script>";
};

function Paginar($sql,$pagina,$tampagina){
// EN BASE A LA SENTENCIA '$sql' Y AL TAMA�O DE LA P�GINA '$tampagina', DEVUELVE:
//		 EL N� DE REGISTRO POR EL QUE COMIENZA LA P�GINA $pagina
//		 EL N� TOTAL DE REGISTROS DE $sql
//		 EL N� TOTAL DE P�GINAS DE $sql

	$res=mysql_query($sql);
	$total_registros=mysql_num_rows($res);
	$total_paginas=floor($total_registros/$tampagina);
	if (($total_registros % $tampagina)>0) $total_paginas=$total_paginas+1;
	$desde=($pagina-1)*$tampagina;
	return array($desde,$total_registros,$total_paginas);
}

function Paginacion($pagina,$total_paginas,$total_registros,$url,$alineacion,$color,$listado){
?>
    <table width='100%' bgcolor='<?= $color;?>' border=0>
		<tr>
        <form name="LIS" action="listado.php" method="POST" target="blank">
        <?php if(is_array($listado)){?>
        <input name="LISs" value="<?php echo $listado['sql'];?>" type="hidden">
        <input name="LISt" value="<?php echo $listado['titulo'];?>" type="hidden">
        <input name="LISc" value="<?php echo $listado['columnas'];?>" type="hidden">
        <input name="LISf" value="<?php echo $listado['filtro'];?>" type="hidden">
        <?php }?>

            <td class="ta11px" align="<? echo $alineacion;?>">
            <?php echo "P&aacute;gina ",$pagina," de ",$total_paginas," (",$total_registros," registros)";
				if ($pagina>1) {
					echo " <a href='$url",1,"'><img src='images/primero1.gif' border='0' title='Inicio' width=12></a>";
			 		echo " <a href='$url",$pagina-1,"'><img src='images/anterior1.gif' border='0' title='Anterior' width=10></a>";
                    if ($pagina==$total_paginas){
                        echo " <img src='images/siguiente0.gif' border='0' width=10>";
                        echo " <img src='images/ultimo0.gif' border='0' width=12>";
                    }
				}
				if ($pagina<$total_paginas) {
                    if ($pagina==1){
                        echo " <img src='images/primero0.gif' border='0' width=12>";
                        echo " <img src='images/anterior0.gif' border='0' width=12>";
                    }
					echo " <a href='$url",$pagina+1,"'><img src='images/siguiente1.gif' border='0' title='Siguiente' width=10></a>";
					echo " <a href='$url",$total_paginas,"'><img src='images/ultimo1.gif' border='0' title='Final' width=12></a>";
				}
				if (is_array($listado) and ($total_registros>0)){?>
                    <input type="image" img src="images/imprimir.png" border="0" alt="Imprimir Listado">
				<?php }?>
            </td>
        </form>
		</tr>
	</table>
<?php
}

function DameDatosEmpresa(){
    $sql="select ".$_SESSION['DBEMP'].".empresas.*,".$_SESSION['DBEMP'].".provincias.NOMBRE as Provincia from ".$_SESSION['DBEMP'].".empresas,".$_SESSION['DBEMP'].".provincias where (IDEmpresa=".$_SESSION['empresa'].") and (IDProvincia=".$_SESSION['DBEMP'].".provincias.CODIGO)";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
	$d="<font class='ta18pxazul'>".$row['RazonSocial']."</font><br>";
	$d=$d."CIF: ".$row['Cif']."<br>";
	$d=$d.$row['Direccion']."<br>";
	$d=$d.$row['CodigoPostal']." ".$row['Poblacion']." ".$row['Provincia']."<br>";
	$d=$d."Tlf.: ".$row['Telefono']." Fax: ".$row['Fax']."<br>";
	$d=$d."<a href='mailto:".$row['EMail']."'>".$row['EMail']."</a><br>";
	$d=$d."<a href=http://".$row['Web'].">".$row['Web']."</a><br>";
	return($d);
}

function Eslogan(){
    $logo2=DameParametro('LOGO2','');
?>
	<table width="100%" class="formularios">
        <tr>
            <td align="right"><b><?php echo DameParametro('ESLOG','');?></b></td>
            <td width="115"><?php if ($logo2!='') {?><img src="images/<?php echo $logo2;?>" border="0"><?php }?></td>
        </tr>
    </table>
<?php
}

function DameParametro($IDParametro,$defecto){
//DEVUELVE EL VALOR DEL PARAMETRO.

    $sql="select Valor from ".$_SESSION['DBEMP'].".parametros where IDParametro='".$IDParametro."'";
    $res=mysql_query($sql);
    $row=mysql_fetch_array($res);
    if ($row['Valor']=='') return($defecto); else return($row['Valor']);
}

function Bloquea($tabla){
	$r=mysql_query("lock tables ".$tabla." write;");
}

function Desbloquea($tabla){
	$r=mysql_query("unlock tables ".$tabla.";");
}

function Etiqueta($idarticulo){?>
	<table width="100%" class="formularios" height="78" border="1">
		<tr><td align="right">123,48 </td></tr>
		<tr><td align="right">123,48 </td></tr>
	</table>
<?php
}

function CuentaCorriente($formulario,$IDBanco,$IDOficina,$Digito,$Cuenta) {
    global $campos;
?>
    <input name="<?php echo $IDBanco;?>" type="text" size="4" maxlength="4" value="<?php echo $campos['idbanco'];?>" class="formularios">
    <img src="images/lupa.png" border="0" onclick="MuestraBancos('<?php echo $formulario;?>','<?php echo $IDBanco;?>');">&nbsp;&nbsp;
    <input name="<?php echo $IDOficina;?>" type="text" size="4" maxlength="4" value="<?php echo $campos['idoficina'];?>" class="formularios">
    <img src="images/lupa.png" border="0" onclick="MuestraOficinas('<?php echo $formulario;?>','<?php echo $IDBanco;?>','<?php echo $IDOficina;?>');">&nbsp;&nbsp;
    <input name="<?php echo $Digito;?>" type="text" size="2" maxlength="2" value="<?php echo $campos['digito'];?>" class="formularios" readonly>&nbsp;&nbsp;
    <input name="<?php echo $Cuenta;?>" type="text" size="10" maxlength="10" value="<?php echo $campos['cuenta'];?>" class="formularios">&nbsp;&nbsp;
<?php
}

function ValidaCC($b,$o,$d,$c){
//Valida una cuenta corriente.
//Devuelve el digito de control si es correcta
//En caso contrario devuelve un mensaje de error

    $e=$_SESSION['DBEMP'];
    $mensaje="";
    
    //Validar Banco
    $res=mysql_query("select count(IDBanco) from $e.bancos where IDBanco='$b';");
    $row=mysql_fetch_array($res);
    if ($row[0]!=1) return("El banco indicado no existe.");
    
    //Validar Oficina
    $res=mysql_query("select count(IDBanco) from $e.bancos_oficinas where IDBanco='$b' and IDOficina='$o';");
    $row=mysql_fetch_array($res);
    if ($row[0]!=1) return("La Oficina bancaria indicada no existe.");

    if (strlen($c)<10) return("La cuenta corriente debe tener 10 d�gitos");

    if ($b.$o.$c=='000000000000000000') $dc='00'; else $dc=DigitoControl($b.$o,$c);
    return($dc);
}

Function DigitoControl($IentOfi,$InumCta)
{
	$APesos = Array(1,2,4,8,5,10,9,7,3,6); // Array de "pesos"
	$DC1=0;
	$DC2=0;
	$x=8;
	while($x>0) {
		$digito=$IentOfi[$x-1];
		$DC1=$DC1+($APesos[$x+2-1]*($digito));
		$x = $x - 1;
	}
	$Resto = $DC1%11;
	$DC1=11-$Resto;
	if ($DC1==10) $DC1=1;
	if ($DC1==11) $DC1=0;              // D�gito control Entidad-Oficina

	$x=10;
	while($x>0) {
		$digito=$InumCta[$x-1];
		$DC2=$DC2+($APesos[$x-1]*($digito));
		$x = $x - 1;
	}
	$Resto = $DC2%11;
	$DC2=11-$Resto;
	if ($DC2==10) $DC2=1;
	if ($DC2==11) $DC2=0;         // D�gito Control C/C

	$DigControl=($DC1)."".($DC2);   // los 2 n�meros del D.C.
    //if (strlen($DigControl)==3) $DigControl=substr($DigControl,1,2);
	return $DigControl;
}

function CopiaSeguridad($empresa,$unidad,$tipo){

        $e=$_SESSION['DBEMP'];
        $d=$_SESSION['DBDAT'].$empresa;

        $ok=1;
        $mensaje="<table class='formularios' align='center' border='1' bgcolor='#CCCCCC'><tr><th>TABLA</th><th>Estado</th></tr>";

        if(($tipo=='E') or ($tipo=='T')){
            $basesdedatos=array($e,);echo $e;
            require "funciones/backup.php";
        }

        if(($tipo=='D') or ($tipo=='T')){
            $basesdedatos=array($d,);echo $d;
            require "funciones/backup.php";
        }
		$mensaje=$mensaje."</table>";
		return array($ok,$mensaje);
}

function FormatoPapel($tipo,$formato){
    //DEVUELVE TODAS LAS MEDIDAS Y CARACTERISTICAS RELACIONADAS CON
    //EL TIPO DE DOCUMENTO Y FORMATO INDICADO.
    $emp=$_SESSION['DBEMP'];

    $sql="select t1.*,t2.* from $emp.documentos as t1,$emp.tipos_papel as t2
        where t1.IDTipo='$tipo' and t1.IDFormato='$formato' and t2.IDPapel=t1.IDPapel";
    $res=mysql_query($sql);
    return(mysql_fetch_array($res));
}

function MedidasPapel($id){

    $res=mysql_query("select Alto,Ancho,MargenSup,MargenIzq,LineasPorPagina,PapelContinuo from ".$_SESSION['DBEMP'].".tipos_papel where IDPapel='$id'");
    $row=mysql_fetch_array($res);
    if ($row[0]=='') return array('ALTO'=>-1);
    else return array('ALTO'=>$row[0],'ANCHO'=>$row[1],'MARGENSUP'=>$row[2],'MARGENIZQ'=>$row[3],'LOPAG'=>$row[4],'PAPELCONTIUNO'=>$row[5]);
}
?>

<script languaje="javaScript">
function Confirma(mensaje){
    if (confirm(mensaje)) return true; else return false;
}

function goPage(destiny) {
	if (destiny != ("")) { document.location.href = destiny; }
}

function cambiacolor(idc,colora) { 
	if (document.all) { 
		document.all[idc].style.background = colora; 
	} else if (document.getElementById) {
		document.getElementById(idc).style.background = colora; 
	}
}

function MuestraInmuebles(formulario,campoclave,campotexto){
	var url;
	url='selecinmueble.php?texto=' + '' + '&form=' + formulario + '&campoclave=' + campoclave + '&campotexto=' + campotexto;
	window.open(url,'INMUEBLES','width=520,height=690,scrollbars=yes,resizable=yes')
}

function MuestraInquilinos(formulario,campoclave,campotexto){
	var url;
	url='selecinquilino.php?texto=' + '' + '&form=' + formulario + '&campoclave=' + campoclave + '&campotexto=' + campotexto;
    window.open(url,'INQUILINOS','width=520,height=690,scrollbars=yes,resizable=yes')
}

function MuestraOficinas(formulario,campobanco,campooficina){
	var url;
	url='contenido.php?c=bancosoficinas&Banco=' + document[formulario][campobanco].value + '&form=' + formulario + '&campobanco=' + campobanco + '&campooficina=' + campooficina;
	window.open(url,'OFICINAS','width=500,height=655,scrollbars=yes,resizable=yes')
}	

function MuestraBancos(formulario,campobanco){
	var url;
	url='contenido.php?c=bancos&form=' + formulario + '&campobanco=' + campobanco;
	window.open(url,'BANCOS','width=500,height=590,scrollbars=yes,resizable=yes')
}
function SymError() {
  return true;
}

window.onerror = SymError;

function veteA(combo) {
	donde= combo.options[combo.selectedIndex].value;
	if (donde!="#") window.location.href=donde;
}

function CerrarVentana() {
	window.close();
}

function Centrar() {
	ancho=document.body.clientWidth;
	alto=document.body.clientHeight;
	var X = (screen.width-ancho)/2;
	var Y = (screen.height-alto)/2;
	window.moveTo(X,Y)
}

function DigitoControl(formulario){
var numero, total,total1,total2,subtotal,subtotal2;
var digitos;
numero = 0;
total = 0;
subtotal = 0;
if(document[formulario].banco.length==1) {
 document[formulario].banco.value = "000" + document[formulario].banco.value
  }
   else if(document[formulario].banco.length==2) {
    document[formulario].banco.value = "00" + document[formulario].banco.value
   }
   else if(document[formulario].banco.length==3){
    document[formulario].banco.value= "0" + document[formulario].banco.value
   }
if(document[formulario].sucursal.length==1) {
 document[formulario].sucursal.value = "000" + document[formulario].sucursal.value
  }
   else if(document[formulario].sucursal.length==2) {
    document[formulario].sucursal.value = "00" + document[formulario].sucursal.value
   }
   else if(document[formulario].sucursal.length==3){
    document[formulario].sucursal.value= "0" + document[formulario].sucursal.value
   }
numero = document[formulario].banco.value;
numero = numero + document[formulario].sucursal.value;
total = parseInt(total + (numero%10)*6);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*3);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*7);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*9);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*10);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*5);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*8);
numero = parseInt(numero/10);
total = parseInt(total + (numero%10)*4);
numero = parseInt(numero/10);
total = total%11;
subtotal = 11-(total%11)
if(subtotal==11) {
 subtotal=0;
 }
 else if(subtotal==10){
  subtotal=1;
}
total1 = 0;
total2=0;
total = document[formulario].cuenta.value;
total1 = total.charAt(9);
total2 = total1*6;
total1 = 0
total1= total.charAt(8);
total2= total2 + (total1*3);
total1=0;
total1= total.charAt(7);
total2= total2 + (total1*7);
total1=0
total1= total.charAt(6);
total2= total2 + (total1*9);
total1=0
total1= total.charAt(5);
total2= total2 + (total1*10);
total1=0
total1= total.charAt(4);
total2= total2 + (total1*5);
total1=0
total1= total.charAt(3);
total2= total2 + (total1*8);
total1=0
total1= total.charAt(2);
total2= total2 + (total1*4);
total1=0
total1= total.charAt(1);
total2= total2 + (total1*2);
total1=0
total1= total.charAt(0);
total2= total2 + (total1*1);
total = total2;
total = total %11;
subtotal2 = 11 - (total%11);
if (subtotal2 == 11) {
 subtotal2=0;
}
else if (subtotal2==10) {
 subtotal2=1;
}

document[formulario].dc.value = subtotal;
document[formulario].dc.value = document[formulario].dc.value + subtotal2;

}

function ValidaNif(documento,campo) {
    cadena = "TRWAGMYFPDXBNJZSQVHLCKET";
    mensaje='';
    caracteres=document.forms[documento].elements[campo].value.length;
    if ((caracteres < 7) || ( caracteres > 9)) { mensaje = 'Faltan caracteres'; }
    else {
        nif=document.forms[documento].elements[campo].value;
        primero=nif.substring(0,1);
        if(!isNaN(primero)){
            numeros=nif.substring(0,8);
            letra=nif.substring(8,1);
            posicion=numeros % 23;
            letraok=cadena.substring(posicion,posicion+1);
            document.forms[documento].elements[campo].value = numeros + letraok;
        }
    }
    if(mensaje!='') alert(mensaje);
}
</script>
