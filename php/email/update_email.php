<?php 

	/* To update an email for any changes */
	include_once('email.inc');

	$emailObject = new Email; // Object 

	// Construct array
	$emailArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'body_EN'           => str_replace(array('"', "'"), '\"', $_POST['body_EN']),
        'body_FR'           => str_replace(array('"', "'"), '\"', $_POST['body_FR']),
 		'serial' 	        => $_POST['serial'],
 		'type' 		        => $_POST['type']
	);

	// Call function
    $response = $emailObject->updatePost($emailArray);

    print json_encode($response); // Return response

?>
