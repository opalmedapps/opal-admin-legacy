<?php
    /* To get a list of existing users in our DB */

	// Retrieve FORM params
	$callback = $_GET['callback'];

    $userObject = new Users; // Object

    // Call function
    $users = $userObject->getUsers();

    // Callback to http request
    print $callback.'('.json_encode($users).')';
?>
