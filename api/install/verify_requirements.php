<?php 

	/* To verify installation requirements */

	$callback 	= $_GET['callback'];

	$pathname 	= getcwd();
	$abspath 	= str_replace('api/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	$installObj = new Install; // Object

	// Call function
	$response = $installObj->verifyRequirements($abspath);

	print $callback.'('.json_encode($response).')';

?>