<?php
function Cerrar_Sesion()
	{
		$_SESSION = array(); 
		session_destroy(); 
	}
?>