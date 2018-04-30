<?php
	/* To get list logs on a particular legacy questionnaire */
	include_once('legacy-questionnaire.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
	$legacyQuestionnaireLogs = $legacyQuestionnaire->getLegacyQuestionnaireListLogs($serials);

	// // Callback to http request
	print $callback.'('.json_encode($legacyQuestionnaireLogs).')';

?>
