<?php

	/* To get a list of existing tags */
	include_once('questionnaire.inc');

	// Retrieve form param
	$callback = $_GET['callback'];

	$tag = new Tag(); // Object

	// Call function 
	$tagList = $tag->getTags();

	// Callback to http request
	print $callback.'('.json_encode($tagList).')';
?>