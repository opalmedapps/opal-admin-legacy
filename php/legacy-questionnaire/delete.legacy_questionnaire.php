<?php

	/* To delete a legacy questionnaire */
	include_once('legacy-questionnaire.inc');

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];
	$user 	= $_POST['user'];

	// Call function
    $response = $legacyQuestionnaire->deleteLegacyQuestionnaire($serial, $user);
    print json_encode($response); // Return response

?>
