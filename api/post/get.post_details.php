<?php
	/* To get details on a particular post */
	include_once('post.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$post = new Post; // Object

	// Call function
	$postDetails = $post->getPostDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($postDetails).')';

?>
