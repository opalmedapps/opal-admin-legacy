<?php

	/* To update source database enabled flags if any changes */
	include_once('application.inc');

	$applicationObj = new Application; // Object

	// Call function
    $response = $applicationObj->updateSourceDatabases($_POST);

    print json_encode($response); // Return response

?>
