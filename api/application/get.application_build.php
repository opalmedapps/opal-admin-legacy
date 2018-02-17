<?php

	/* To get the application build */

	include_once('application.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$appObject = new Application; // Object

	// Call function
	$build = $appObject->getApplicationBuild();

	// Callback to http request
	print $callback.'('.json_encode($build).')';

?>
