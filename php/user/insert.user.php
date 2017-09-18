<?php
	/* To insert a newly created user */
	include_once('user.inc');

	// Construct array from FORM params
	$userArray = array(
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password'],
		'role'			=> $_POST['role']
	);

	$userObj = new Users; // Object

	// Call function 
	$userObj->registerUser($userArray);

?>