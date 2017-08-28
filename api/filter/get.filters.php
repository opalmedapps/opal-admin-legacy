<?php
	/* To get filters (expression, dx, doctor, resource)*/
	include_once('filter.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$filterObject = new Filter; // Object

	// Call function
	$filters = $filterObject->getFilters();

	// Callback to http request
	print $callback.'('.json_encode($filters).')';

?>
