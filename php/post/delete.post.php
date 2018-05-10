<?php

	/* To delete a post */
	include_once('post.inc');

	$post = new Post; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user 	= $_POST['user'];

	// Call function
    $response = $post->deletePost($serial, $user);

    print json_encode($response); // Return response

?>
