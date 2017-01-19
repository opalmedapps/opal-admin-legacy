<?php
	/* To get a list of ARIA expressions */

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
