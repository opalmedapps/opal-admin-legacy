<?php

	/* To delete an alias */
	include_once('alias.inc');

	$alias = new Alias; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user = $_POST['user'];

	// Call function
    $response = $alias->deleteAlias($serial, $user);
    print json_encode($response); // Return response

?>
