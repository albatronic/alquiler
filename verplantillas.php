<?
	if ($_SESSION['iu']=='') exit;
	$agente=$_SESSION['iu'];

    require "conecta.php";
    require "funciones/variables.php";
    
    $f=$_GET['f'];
    $accion=$_GET['accion'];
    switch ($accion){
        case "Borrar":
            @unlink('plantillas/'.$f);
        break;
        
        case "Variables":
            $v=array();
            $v=VariablesDocumento('plantillas/'.$f);
        break;
	}	
?>
<table width="100%" align="center" bgcolor="" valign="top" BORDER=1>
	<TR><Th colspan="2">RELACIÓN DE PLANTILLAS DE CONTRATOS</th></TR>
	<TR>
		<TD width="50%" valign="top">
    		<TABLE class="formularios">
    		<?
			$d = dir("plantillas");
			$i=0;
	     	while($registro=$d->read())
				if (($registro!='.') and ($registro!='..') and ($registro!='index.php')){$i=$i+1;?>
    			<tr class="Formularios" id="linea<? echo $i;?>"
           			onmouseover="<? echo "cambiacolor('linea",$i,"','#FFFF00');";?>"
    	      		onmouseout="<? echo "cambiacolor('linea",$i,"','",$gris,"');";?>"
                >
                    <td><a href="contenido.php?c=verplantillas&f=<?echo $registro;?>&accion=Borrar"><img src="images/botonborrar.png" border='0' onclick="return Confirma('<?echo "Desea eliminar la plantilla ",$registro;?>');"></a></td>
                    <td><a href="plantillas/<?echo $registro;?>" target="_blank"><?echo $registro;?></a></td>
                    <td><a href="contenido.php?c=verplantillas&f=<?echo $registro;?>&accion=Variables"><img src="images/lupa.png" border='0'></a></td>
                </tr>
			<?}
	   		$d->close();
    	  	?>
    		</TABLE>
		</TD>

		<td valign="top">
    		<TABLE class="formularios" align="center">
            <?
            if (is_array($v)){?>
                <tr><th colspan="2">VARIABLES DEL DOCUMENTO<br> "<?echo $f;?>"</th></tr>
                <tr><th>NOMBRE VARIABLE</th><th>COLUMNA BASE DE DATOS</th></tr>
                <?
                Subrrayado(2);
                foreach ($v as $key=>$value){?>
                    <tr>
                        <td><?echo $key;?></td>
                        <td><?echo $value;?></td>
                    </tr>
                <?}
            }?>		
            </table>
		</td>
	</TR>
</table>
