<?php
	
	include_once('questionnaire.inc');

	$questionnaire_serNum = $_POST['serNum'];

	$questionnaireObj = new Questionnaire;

	$response = $questionnaireObj->deleteQuestionnaire($questionnaire_serNum);

	print json_encode($response); // Return response
?>