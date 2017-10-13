<?php

	/* To get a list of existing legacy questionnaires */

	include_once('legacy-questionnaire.inc');

	// Retrieve FORM param
	$callback = $_GET['callback'];

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
	$legacyQuestionnaireList = $legacyQuestionnaire->getLegacyQuestionnaires();

	// Callback to http request
	print $callback.'('.json_encode($legacyQuestionnaireList).')';

?>
