<?php

	/* To update a user details */
	include_once('user.inc');

	$userObject = new Users; // Object

	// Construct array from FORM params
	$userDetails = array (
		'user'				=> array('id'=>$_POST['serial']),
		'password'			=> $_POST['password'],
		'confirmPassword' 	=> $_POST['confirmPassword'],
		'override'			=> true,
		'role'				=> $_POST['role'],
		'language'			=> $_POST['language'],
		'cypher'			=> $_POST['cypher']
	);

	// Call function to update password
	$response = $userObject->updateUser($userDetails);
	print json_encode($response); // return response
?> 