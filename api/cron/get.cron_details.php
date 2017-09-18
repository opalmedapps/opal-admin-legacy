<?php 
	/* To get details on a cron profile */
	include_once('cron.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];

	$cron = new Cron; // Object

	// Call function
	$cronDetails = $cron->getCronDetails();

	// Callback to http request
	print $callback.'('.json_encode($cronDetails).')';

?>
