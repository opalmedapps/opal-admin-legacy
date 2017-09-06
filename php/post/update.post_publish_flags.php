<?php 

	/* To call Post Object to update post when the "Publish Flag" checkbox has been changed */
	include_once('post.inc');

	$postObject = new Post; // Object

	// Retrieve FORM params
	$postFlags	= $_POST['flagList'];
	
	// Call function
    $response = $postObject->updatePostFlags($postFlags);
    print json_encode($response); // Return response
?>


