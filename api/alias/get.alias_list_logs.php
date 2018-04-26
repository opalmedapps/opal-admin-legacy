<?php
	/* To get list logs on a particular alias */
	include_once('alias.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);
	$type = ( $_GET['type'] === 'undefined' ) ? null : $_GET['type'];
	

	$alias = new Alias; // Object

	// Call function
	$aliasLogs = $alias->getAliasListLogs($serials, $type);

	// // Callback to http request
	print $callback.'('.json_encode($aliasLogs).')';

?>
