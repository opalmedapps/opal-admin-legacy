<?php
	/* To update questionnaire information from POST request */
	include_once('questionnaire.inc'); // Load library

	$questionnaireArray = array(
		'serNum'			=> $_POST['serNum'],
		'name_EN'	 		=> $_POST['name_EN'],
		'name_FR'	 		=> $_POST['name_FR'],
		'private' 			=> $_POST['private'],
		'publish' 			=> $_POST['publish'],
		'last_updated_by'	=> $_POST['last_updated_by'],
		'tags'				=> $_POST['tags'],
		'groups'			=> $_POST['groups'],
		'filters'			=> $_POST['filters']
	);

	$questionnaireObj = new Questionnaire;

	$response = $questionnaireObj->updateQuestionnaire($questionnaireArray); // Call class function

	print json_encode($response); // "Return" response
?>