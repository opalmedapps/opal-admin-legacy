<?php
	/* To get a list of existing posts */

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$post = new Post; // Object

	// Call function
	$existingPostList = $post->getExistingPosts();

	// Callback to http request
	print $callback.'('.json_encode($existingPostList).')';

?>
