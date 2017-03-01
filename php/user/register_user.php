<?php
	/* To insert a newly created user */
	include_once('user.inc');

	$userArray = array(
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password'],
		'role'			=> $_POST['role']
	);

	$userObj = new Users; 

	// Call function 
	$userObj->registerUser($userArray);

?>