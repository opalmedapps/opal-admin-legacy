<?php
	/* To get a list of existing color tags */

	// Retrieve FORM param
    $callback   = $_GET['callback'];
    $type       = $_GET['type'];    

	$alias = new Alias; // Object

	// Call function
	$colorTags = $alias->getExistingColorTags($type);

	// Callback to http request
	print $callback.'('.json_encode($colorTags).')';

?>
