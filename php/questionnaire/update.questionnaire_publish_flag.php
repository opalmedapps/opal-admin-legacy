<?php
	/* To publish a questionnaire */
	include_once('questionnaire.inc');

	// Retrieve FORM param
	$questionnaire_serNum = $_POST['questionnaire_serNum'];
	$user = $_POST['user'];

	$questionnaireObj = new Questionnaire; // Object

	// Call function
	$questionnaireObj->publishQuestionnaire($questionnaire_serNum, $user);
?>