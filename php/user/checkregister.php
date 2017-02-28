<?php 

	/* To call Users Object to validate registration to application */
	include_once('user.inc');

	$usr = new Users; // Object

	// Store FORM params
	$usr->storeFormValues( $_POST );

	// If both password fields are the same 
	if( $_POST['password'] == $_POST['passConfirm'] ) {
		$usr->register($_POST);	// Register User
	    session_start(); // Start a session
		$_SESSION[SESSION_KEY_REGISTER] = 1;
	
        print 1;

	} else { // Password fields different 

        print 0;
	}
?>
