<?php

	/* To insert a newly created post */
	include_once('post.inc');

	// Construct array from FORM params
	$postArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'body_EN'           => str_replace(array('"', "'"), '\"', $_POST['body_EN']),
        'body_FR'           => str_replace(array('"', "'"), '\"', $_POST['body_FR']),
        'publish_date'      => $_POST['publish_date'],
        'filters'           => $_POST['filters'],
 		'type' 		        => $_POST['type']['name']
	);

	$postObject = new Post; // Object

	// Call function
	$postObject->insertPost($postArray);
	
?>
