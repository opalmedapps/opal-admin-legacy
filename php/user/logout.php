<?php
    /* Simple logout script */ 
    include_once('user.inc');

    // Retrieve post data
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata);

    $userDetails = array(
    	'userser'	=> $request->id,
    	'sessionid'	=> $request->sessionid
    );

    $userObject = new Users;

    // Call function
    $response = $userObject->userLogout($userDetails);
    print json_encode($response); // Return response

?>
