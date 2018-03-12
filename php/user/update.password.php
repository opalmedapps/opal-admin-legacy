<?php

	/* To update a user's password */
	include_once('user.inc');

	$userObject = new Users;  // Object

	// Construct array from FORM params
	$userArray = array(
		'oldPassword'		=> $_POST['oldPassword'],
		'password'			=> $_POST['password'],
		'confirmPassword'	=> $_POST['confirmPassword'],
		'user'				=> $_POST['user'],
		'cypher'			=> $_POST['cypher']
	);

	// Call function 
	$response = $userObject->updatePassword($userArray);
	print json_encode($response); // Return response

?>
