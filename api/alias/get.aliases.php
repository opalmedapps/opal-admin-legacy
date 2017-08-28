<?php

	/* To get a list of existing alias */

	include_once('alias.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$alias = new Alias; // Object

	// Call function
	$existingAliasList = $alias->getAliases();

	// Callback to http request
	print $callback.'('.json_encode($existingAliasList).')';

?>
