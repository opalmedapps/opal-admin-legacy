<?php 
    /* To get logs on a current user given a serial */
    include_once('user.inc');

	// Retrieve FORM params
    $callback   = $_GET['callback'];
    $userSer    = $_GET['userser'];

    $userObject = new Users; // Object

    // Call function
    $userLogs = $userObject->getUserActivityLogs($userSer); 

    // Callback to http request
    print $callback.'('.json_encode($userLogs).')';
?>
