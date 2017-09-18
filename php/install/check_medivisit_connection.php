<?php
	/* To check MediVisit database connection */

	$pathname 	= __DIR__;
	$abspath 	= str_replace('php/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	// Construct array from FORM params
	$mediVisitCreds = array(
		'host' 			=> $_POST['host'],
		'name' 			=> $_POST['name'],
		'port' 			=> $_POST['port'],
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password'],
	);

	$installObj = new Install; // Object

	// Call function 
	$response = $installObj->checkMediVisitConnection($mediVisitCreds);

	// Return response
	print json_encode($response);

?>

