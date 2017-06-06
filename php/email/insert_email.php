<?php

	/* To insert a newly created email template */
	include_once('email.inc');

	// Construct array
	$emailArray	= array(
		'subject_EN' 	    => $_POST['subject_EN'],
		'subject_FR' 	    => $_POST['subject_FR'],
        'body_EN'           => str_replace(array('"', "'"), '\"', $_POST['body_EN']),
        'body_FR'           => str_replace(array('"', "'"), '\"', $_POST['body_FR']),
 		'type' 		        => $_POST['type']['serial']
	);

	$emailObject = new Email; // Object

	// Call function
	$emailObject->insertEmail($emailArray);
	
?>
