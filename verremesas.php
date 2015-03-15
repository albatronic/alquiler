<?
	if ($_SESSION['iu']=='') exit;
	$agente=$_SESSION['iu'];

    $EMP=$_SESSION['DBEMP'];
    $DAT=$_SESSION['DBDAT'];

    require "conecta.php";
    require "engancha.php";
    require "funciones/remesas.php";
 
    $f=$_GET['f'];
    $accion=$_GET['accion'];
    switch ($accion){
        case "Borrar":
            @unlink('remesas/'.$f);
            //QUITAR LA MARCA DE REMESADO EN LOS RECIBOS. TENGO QUE RECORRER TODAS LAS EMPRESAS.
            $marca=substr($f,6,14);
            $sql="select IDEmpresa from $EMP.empresas";
            $res=mysql_query($sql);
            while ($emp=mysql_fetch_array($res)){
                $sql="update $DAT".$emp[0].".recibos set IDRemesa='' where IDRemesa='$marca';";
                mysql_query($sql);
            }
        break;
        
        case "Ver":
            $informe=InformeRemesa('remesas/'.$f);
        break;
	}	
?>
<table width="100%" align="center" bgcolor="" valign="top" BORDER=1>
	<TR><Th colspan="2">MANTENIMIENTO DE REMESAS GENERADAS</th></TR>
	<TR>
		<TD width="30%" valign="top">
    		<TABLE class="formularios">
    		<?
			$d = dir("remesas");
			$i=0;
	     	while($registro=$d->read())
				if (($registro!='.') and ($registro!='..') and ($registro!='index.php')){$i=$i+1;?>
    			<tr class="Formularios" id="linea<? echo $i;?>"
           			onmouseover="<? echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
    	      		onmouseout="<? echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
                >
                    <td><a href="contenido.php?c=verremesas&f=<?echo $registro;?>&accion=Borrar"><img src="images/botonborrar.png" border='0' onclick="return Confirma('<?echo "Desea eliminar la remesa ",$registro;?>');"></a></td>
                    <td><a href="remesas/<?echo $registro;?>" target="_blank"><?echo $registro;?></a></td>
                    <td><a href="contenido.php?c=verremesas&f=<?echo $registro;?>&accion=Ver"><img src="images/lupa.png" border='0'></a></td>
                </tr>
			<?}
	   		$d->close();
    	  	?>
    		</TABLE>
		</TD>

		<td valign="top">
            <?echo $informe;?>
		</td>
	</TR>
</table>
