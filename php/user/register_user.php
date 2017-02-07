<?php
	/* To insert a newly created user */

	$userArray = array(
		'username'		=> $_POST['username'],
		'password'		=> $_POST['password']
	);

	$userObj = new Users; 

	// Call function 
	$userObj->registerUser($userArray);

?>