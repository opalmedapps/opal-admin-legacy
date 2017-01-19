<?php 

	/* To call Users Object to validate login */

	$usr = new Users; // Object 
	
	// Store FORM params
	$usr->storeFormValues( $_POST );
	
	// Successful login
	if( $usr->userLogin() ) {
	
	    session_start(); // Begin session
		// Add session params
		$_SESSION[SESSION_KEY_NAME] = $usr->username; 
		$_SESSION[SESSION_KEY_LOGIN] = 1;
		$_SESSION[SESSION_KEY_USERID] = $usr->userid;

        print 1;
	} else { // Failed login

        print 0;

    }
?>
