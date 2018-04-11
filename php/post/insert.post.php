<?php

	/* To insert a newly created post */
	include_once('post.inc');

	// Construct array from FORM params
	$postArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'body_EN'           => filter_var($_POST['body_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'body_FR'           => filter_var($_POST['body_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
        'publish_date'      => $_POST['publish_date'],
        'triggers'          => $_POST['triggers'],
 		'type' 		        => $_POST['type']['name'],
 		'user'				=> $_POST['user']
	);

	$postObject = new Post; // Object

	// Call function
	$postObject->insertPost($postArray);
	
?>
