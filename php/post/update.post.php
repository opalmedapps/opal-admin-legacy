<?php 

	/* To update a post for any changes */
	include_once('post.inc');

	$postObject = new Post; // Object 

	// Construct array from FORM params
	$postArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'body_EN'           => filter_var($_POST['body_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'body_FR'           => filter_var($_POST['body_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
        'publish_date'      => $_POST['publish_date'],
        'filters'           => $_POST['filters'],
 		'serial' 	        => $_POST['serial'],
 		'type' 		        => $_POST['type'],
 		'user'				=> $_POST['user']
	);

	// Call function
    $response = $postObject->updatePost($postArray);

    print json_encode($response); // Return response

?>
