<?php

	/* Determine if username is taken */

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$username = $_GET['username'];

	$userObj = new Users; // Object

	// Call function
	$Response = $userObj->usernameAlreadyInUse($username);

	// Callback to http request
	print $callback.'('.json_encode($Response).')';

?>