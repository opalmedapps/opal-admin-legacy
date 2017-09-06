<?php
	
	/* To insert a created question group to questionnaire */
	include_once('questionnaire.inc');

	// Construct array from FORM param
	$questionGroupArray = array(
		'groups'	=> $_POST['groupArray']
	);

	$questionnaireObj = new Questionnaire; // Object

	// Call function
	$response = $questionnaireObj->insertQuestionGroupToQuestionnaire($questionGroupArray);
	print json_encode($response); // return response
?>