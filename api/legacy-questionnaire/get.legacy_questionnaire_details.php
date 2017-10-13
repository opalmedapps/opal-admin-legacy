<?php

	/* To get details on a legacy questionnaire */
	include_once('legacy-questionnaire.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = $_GET['serial'];

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
	$legacyQuestionnaireDetails = $legacyQuestionnaire->getLegacyQuestionnaireDetails($serial);

	// Callback to http request
	print $callback.'('.json_encode($legacyQuestionnaireDetails).')';

?>
