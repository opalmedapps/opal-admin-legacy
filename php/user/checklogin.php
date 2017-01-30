<?php 

	/* To call Users Object to validate login */

	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);

	$usr = new Users; // Object 
	
	// Store FORM params
	$usr->storeFormValues( $request );
	
	// Successful login
	if( $usr->userLogin() ) {
	
	    session_start(); // Begin session
		// Add session params
		$_SESSION[SESSION_KEY_NAME] = $usr->username; 
		$_SESSION[SESSION_KEY_LOGIN] = 1;
		$_SESSION[SESSION_KEY_USERID] = $usr->userid;

		$response = array(
			'success'	=> 1,
			'user'		=> array(
				'id'		=> $usr->userid,
				'username'	=> $usr->username,
				'role'		=> 'admin'
			)
		);

        print json_encode($response);

	} else { // Failed login

		$response = array(
			'success'	=> 0,
			'user'		=> null
		);
        print json_encode($response);

    }
?>
