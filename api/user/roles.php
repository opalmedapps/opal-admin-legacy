<?php
    /* To get a list of existing roles in our DB */
    include_once('user.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];

    $userObject = new Users; // Object

    // Call function
    $roles = $userObject->getRoles();

    // Callback to http request
    print $callback.'('.json_encode($roles).')';
?>
