<?php
	/* To get a list of source databases */
	include_once('alias.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];
	$type = $_GET['type'];

	$alias = new Alias; // Object

	// Call function
	$sourceDBList = $alias->getSourceDatabases();

	// Callback to http request
	print $callback.'('.json_encode($sourceDBList).')';

?>
