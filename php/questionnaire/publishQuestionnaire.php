<?php
	
	include_once('questionnaire.inc');

	$questionnaire_serNum = $_POST['questionnaire_serNum'];

	$questionnaireObj = new Questionnaire;

	$questionnaireObj->publishQuestionnaire($questionnaire_serNum);
?>