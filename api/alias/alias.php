<?php

	/* To get a list of existing alias */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$alias = new Alias; // Object

	// Call function
	$existingAliasList = $alias->getExistingAliases();

	// Callback to http request
	print $callback.'('.json_encode($existingAliasList).')';

?>
