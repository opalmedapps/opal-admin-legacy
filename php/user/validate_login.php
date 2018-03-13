<?php 

	/* To validate login */
	include_once('user.inc');

	// Retrieve post data
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);

	$usr = new Users; // Object 
	
	// Store FORM params
	$usr->storeFormValues( $request );

	// Successful login
	if( $usr->userLogin() ) {
	
		$response = array(
			'success'	=> 1,
			'user'		=> array(
				'id'		=> $usr->userid,
				'username'	=> $usr->username,
				'role'		=> $usr->role,
				'language' 	=> $usr->language,
				'sessionid' => $usr->sessionid
			)
		);

        print json_encode($response); // Return response

	} else { // Failed login

		$response = array(
			'success'	=> 0,
			'user'		=> null
		);
        print json_encode($response); // Return response

    }
?>
