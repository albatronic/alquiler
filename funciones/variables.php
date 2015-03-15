<?
function BuscaVariable($texto,$separador,$puntero){
    //Busco el primer separador
    $i=strpos($texto,$separador,$puntero);
    if($i===false) return(array('',-1)); //No lo encuentra
    $i=$i+strlen($separador);

    //Busco el segundo separador
    $f=strpos($texto,$separador,$i);
    if($f==false) return(array('',-1)); //No lo encuentra

    //La variable está entre los dos separadores
    $variable=TRIM(strtoupper(substr($texto,$i,$f-$i)));
    $f=$f+strlen($separador);
    return(array($variable,$f));
}

function LeeFichero($fichero){
    $texto="";
    if (file_exists($fichero)){
        $f=fopen($fichero,"rb");
        $texto=(fread($f,filesize($fichero)));
        fclose($f);
    }
    return($texto);
}

function VariablesDocumento($f){
    //DEVUELVE UN ARRAY CON LAS VARIABLES QUE EXISTEN EN EL DOCUMENTO PASADO
    //LAS VARIABLES DEBEN ESTAR DELIMITIDAS POR EL/LOS CARACTERE/S SEPARADOS DEFINIDO/S EN EL PARAMETRO 'SEPAR'
    //EL INDICE DEL ARRAY SERÁ EL NOMBRE DE LA VARIABLE Y EL VALOR PARA CADA INDICE SERÁ EL NOMBRE
    //DE LA COLUMNA DE LA TABLA DE LA BASE DE DATOS SEGUN LA CORRESPONDENCIA DEFINIDA EN LA TABLA 'VARIABLES'

    $texto=LeeFichero($f);
    $separador=DameParametro('SEPAR','');
    $largo=strlen($texto);
    $i=0;
    $v=array();
    
    while(($i<$largo) and ($i>=0)){
        list($variable,$i)=BuscaVariable($texto,$separador,$i);
        if($i>0) {
            $sql="select Columna from variables where IDVariable='$variable'";
            $res=mysql_query($sql);
            $row=mysql_fetch_array($res);
            $v[$variable]=$row['Columna'];
        }
    }
    return $v;
}
?>

