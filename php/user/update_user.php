<?php

	/* To update a user details */

	$userObject = new Users;

	$userArray = array (
		'user'				=> array('id'=>$_POST['serial']),
		'password'			=> $_POST['password'],
		'confirmPassword' 	=> $_POST['confirmPassword'],
		'override'			=> true,
		'role'				=> $_POST['role'] 
	);

	// Call function to update password
	$response = $userObject->updateUser($userArray);
	print json_encode($response); // return response
?> 