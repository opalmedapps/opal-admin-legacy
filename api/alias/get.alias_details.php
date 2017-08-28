<?php

	/* To get details on a particular alias */
	include_once('alias.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$alias = new Alias; // Object

	// Call function
	$AliasDetails = $alias->getAliasDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($AliasDetails).')';

?>
