<?php
	
	include_once('questionnaire.inc');

	$groups = $_POST['groupArray'];
	$questionGroupArray = array(
		'groups'	=> $groups
	);

	$questionnaireObj = new Questionnaire;

	$response = $questionnaireObj->addGroupToQuestionnaire($questionGroupArray);
	print json_encode($response);
?>