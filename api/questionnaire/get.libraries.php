<?php
	/* To get a list of existing libraries */
	include_once('questionnaire.inc');

	// Retrieve form params
	$callback = $_GET['callback'];
	$userid = $_GET['userid'];

	$library = new Library(); // Object

	// Call function
	$libraryList = $library->getLibraries($userid);

	// Callback to http request
	print $callback.'('.json_encode($libraryList).')';
?>