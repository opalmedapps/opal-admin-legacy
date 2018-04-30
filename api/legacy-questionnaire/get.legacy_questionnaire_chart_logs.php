<?php
	/* To get logs on a particular legacy questionnaire for highcharts */
	include_once('legacy-questionnaire.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = ( $_GET['serial'] === 'undefined' ) ? null : $_GET['serial'];

	$legacyQuestionnaire = new LegacyQuestionnaire; // Object

	// Call function
	$legacyQuestionnaireLogs = $legacyQuestionnaire->getLegacyQuestionnaireChartLogs($serial);

	// Callback to http request
	print $callback.'('.json_encode($legacyQuestionnaireLogs).')';

?>
