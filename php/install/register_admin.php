<?php

	/* To insert a newly created admin user */

	$pathname 	= getcwd();
	$abspath 	= str_replace('php/install', '', $pathname); 

	include_once($abspath . 'php/classes/Install.php');

	$adminCreds = array(
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password']
	);

	$installObj = new Install; 

	// Call function 
	$response = $installObj->registerAdminUser($adminCreds);
	
	// Return response
	print json_encode($response);


?>