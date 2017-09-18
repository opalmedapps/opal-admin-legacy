<?php
	/* To check ARIA database connection */

	$pathname 	= __DIR__;
	$abspath 	= str_replace('php/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	// Construct array from FORM params
	$ariaCreds = array(
		'host' 			=> $_POST['host'],
		'port' 			=> $_POST['port'],
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password'],
		'document_path'	=> $_POST['document_path']
	);

	$installObj = new Install; // Object

	// Call function 
	$response = $installObj->checkAriaConnection($ariaCreds);

	// Return response
	print json_encode($response);

?>

