<?php

	/* To update a user details */

	$userObject = new Users;

	$userArray = array (
		'user'		=> array('id'=>$_POST['serial']),
		'password'	=> $_POST['password'],
		'override'	=> true 
	);

	// Call function to update password
	$response = $userObject->updatePassword($userArray);
	print json_encode($response); // return response
?> 