<?php
	/* To get a list of existing posts */
	include_once('post.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$post = new Post; // Object

	// Call function
	$existingPostList = $post->getPosts();

	// Callback to http request
	print $callback.'('.json_encode($existingPostList).')';

?>
