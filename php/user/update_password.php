<?php

	/* To update a user's password */

	$userObject = new Users; 

	$userArray = array(
		'oldPassword'		=> $_POST['oldPassword'],
		'password'			=> $_POST['password'],
		'confirmPassword'	=> $_POST['confirmPassword'],
		'user'				=> $_POST['user']
	);

	// Call function 
	$response = $userObject->updatePassword($userArray);
	print json_encode($response); // Return response

?>
