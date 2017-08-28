<?php
	/* To check Opal database connection */

	$pathname 	= __DIR__;
	$abspath 	= str_replace('php/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	// Construct array from FORM params
	$opalCreds = array(
		'host' 			=> $_POST['host'],
		'port'			=> $_POST['port'],
		'name'			=> $_POST['name'],
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password']
	);

	$installObj = new Install; // Object

	// Call function 
	$response = $installObj->checkOpalConnection($opalCreds);

	// Return response
	print json_encode($response);

?>

