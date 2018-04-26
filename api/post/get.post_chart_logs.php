<?php
	/* To get logs on a particular post for highcharts */
	include_once('post.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = ( $_GET['serial'] === 'undefined' ) ? null : $_GET['serial'];
	$type = ( $_GET['type'] === 'undefined' ) ? null : $_GET['type'];
	
	$post = new Post; // Object

	// Call function
	$postLogs = $post->getPostChartLogs($serial, $type);

	// Callback to http request
	print $callback.'('.json_encode($postLogs).')';

?>
