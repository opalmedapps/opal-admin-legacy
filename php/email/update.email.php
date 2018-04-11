<?php 

	/* To update an email for any changes */
	include_once('email.inc');

	$emailObject = new Email; // Object 

	// Construct array from FORM params
	$emailArray	= array(
		'subject_EN' 	    => $_POST['subject_EN'],
		'subject_FR' 	    => $_POST['subject_FR'],
        'body_EN'           => filter_var($_POST['body_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'body_FR'           => filter_var($_POST['body_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
 		'serial' 	        => $_POST['serial'],
		'type' 		        => $_POST['type'],
		'user'				=> $_POST['user']
	);

	// Call function
    $response = $emailObject->updateEmail($emailArray);

    print json_encode($response); // Return response

?>
