<?php
	/* To write to configuration files */

	$pathname 	= __DIR__;
	$abspath 	= str_replace('php/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	// Construct array from FORM params
	$configs = array(
		'opal' 			=> $_POST['opal'],
		'clinical' 		=> $_POST['clinical'],
		'urlpath'		=> $_POST['urlpath']
	);

	$installObj = new Install; // Object

	// Call function 
	$response = $installObj->writeConfigurations($configs);

	// Return response
	print json_encode($response);

?>

