<?php

	/* To delete a post */
	include_once('post.inc');

	$post = new Post; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $post->deletePost($serial);

    print json_encode($response); // Return response

?>
