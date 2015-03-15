<?php
function moneda($euros){
	echo number_format($euros,'2','.',',')," €";
}

function numero($numero){
	echo number_format($numero,'2','.',',');
}

function porcen($numero){
	echo number_format($numero,'3',',','.')," %";
}
?>
