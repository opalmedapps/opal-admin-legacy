<?php

	/* To delete an alias */

	$alias = new Alias; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $alias->removeAlias($serial);
    print json_encode($response); // Return response

?>
