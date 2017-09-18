<?php

	/* To insert a newly created admin user */
	include_once('install.inc');

	// Construct array from FORM params
	$adminCreds = array(
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password']
	);

	$installObj = new Install; // Object

	// Call function 
	$response = $installObj->registerAdminUser($adminCreds);
	
	// Return response
	print json_encode($response);


?>