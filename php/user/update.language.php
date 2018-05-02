<?php

	/* To update a user's language */
	include_once('user.inc');

	$userObject = new Users;  // Object

	// Construct array from FORM params
	$userDetails = array(
		'id'				=> $_POST['id'],
		'language'			=> $_POST['language']
	);
	
	// Call function 
	$response = $userObject->updateLanguage($userDetails);
	print json_encode($response); // Return response

?>
