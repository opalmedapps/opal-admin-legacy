<?php

	/* To insert a newly created email template */
	include_once('email.inc');

	// Construct array
	$emailArray	= array(
		'name_EN' 	        => $_POST['name_EN'],
		'name_FR' 	        => $_POST['name_FR'],
        'body_EN'           => str_replace(array('"', "'"), '\"', $_POST['body_EN']),
        'body_FR'           => str_replace(array('"', "'"), '\"', $_POST['body_FR']),
 		'type' 		        => $_POST['type']['serial']
	);

	$emailObject = new Email; // Object

	// Call function
	$emailObject->insertEmail($emailArray);
	
?>
