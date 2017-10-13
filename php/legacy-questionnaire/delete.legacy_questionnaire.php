<?php

	/* To delete a legacy questionnaire */
	include_once('legacy-questionnaire.inc');

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Retrieve FORM param
	$serial = $_POST['serial'];

	// Call function
    $response = $legacyQuestionnaire->deleteLegacyQuestionnaire($serial);
    print json_encode($response); // Return response

?>
