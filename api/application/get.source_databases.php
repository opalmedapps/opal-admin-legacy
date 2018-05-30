<?php

	/* To get source databases */

	include_once('application.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$appObject = new Application; // Object

	// Call function
	$sourceDatabases = $appObject->getSourceDatabases();

	// Callback to http request
	print $callback.'('.json_encode($sourceDatabases).')';

?>
