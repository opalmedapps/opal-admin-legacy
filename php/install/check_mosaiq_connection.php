<?php
	/* To check Mosaiq database connection */

	$pathname 	= __DIR__;
	$abspath 	= str_replace('php/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	// Construct array from FORM params
	$mosaiqCreds = array(
		'host' 			=> $_POST['host'],
		'document_path' => $_POST['document_path'],
		'port' 			=> $_POST['port'],
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password'],
	);

	$installObj = new Install; // Object

	// Call function 
	$response = $installObj->checkMosaiqConnection($mosaiqCreds);

	// Return response
	print json_encode($response);

?>

