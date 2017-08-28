<?php
	/* To get a list of expressions from a particular source database*/
	include_once('alias.inc');

	// Retrieve FORM param
	$callback       = $_GET['callback'];
    $sourceDBSer    = $_GET['sourcedbser'];
	$type           = $_GET['type'];

	$alias = new Alias; // Object

	// Call function
	$expressionList = $alias->getExpressions($sourceDBSer, $type);

	// Callback to http request
	print $callback.'('.json_encode($expressionList).')';

?>
