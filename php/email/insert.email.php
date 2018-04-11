<?php

	/* To insert a newly created email template */
	include_once('email.inc');

	// Construct array from FORM params
	$emailArray	= array(
		'subject_EN' 	    => $_POST['subject_EN'],
		'subject_FR' 	    => $_POST['subject_FR'],
        'body_EN'           => filter_var($_POST['body_EN'], FILTER_SANITIZE_MAGIC_QUOTES),
        'body_FR'           => filter_var($_POST['body_FR'], FILTER_SANITIZE_MAGIC_QUOTES),
		'type' 		        => $_POST['type']['serial'],
		'user'				=> $_POST['user']
	);

	$emailObject = new Email; // Object

	// Call function
	$emailObject->insertEmail($emailArray);
	
?>
