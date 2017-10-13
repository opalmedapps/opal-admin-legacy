<?php
	/* To get a list of questionnaire expressions from the legacy questionnaire database */
	include_once('legacy-questionnaire.inc');

	// Retrieve FORM param
	$callback       = $_GET['callback'];

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
	$legacyQuestionnaireExpressions = $legacyQuestionnaire->getLegacyQuestionnaireExpressions();

	// Callback to http request
	print $callback.'('.json_encode($legacyQuestionnaireExpressions).')';

?>
