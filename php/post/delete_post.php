<?php

	/* To delete a post */

	$post = new Post; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $post->removePost($serial);

    print json_encode($response); // Return response

?>
