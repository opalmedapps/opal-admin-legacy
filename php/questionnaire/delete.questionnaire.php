<?php
	
	/* To delete a questionnaire */
	include_once('questionnaire.inc');

	// Retrieve FORM param
	$questionnaire_serNum = $_POST['serNum'];
	$user = $_POST['user'];

	$questionnaireObj = new Questionnaire; // Object

	// Call function
	$response = $questionnaireObj->deleteQuestionnaire($questionnaire_serNum, $user);

	print json_encode($response); // Return response
?>