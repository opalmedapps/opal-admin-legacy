<?php
	/* To update questionnaire information from POST request */
	include_once('questionnaire.inc'); // Load library

	// Construct array from FORM params
	$questionnaireArray = array(
		'serNum'			=> $_POST['serNum'],
		'name_EN'	 		=> $_POST['name_EN'],
		'name_FR'	 		=> $_POST['name_FR'],
		'private' 			=> $_POST['private'],
		'publish' 			=> $_POST['publish'],
		'last_updated_by'	=> $_POST['last_updated_by'],
		'tags'				=> $_POST['tags'],
		'groups'			=> $_POST['groups'],
		'filters'			=> $_POST['filters'],
		'user'				=> $_POST['user']
	);

	$questionnaireObj = new Questionnaire; // Object

	// Call function
	$response = $questionnaireObj->updateQuestionnaire($questionnaireArray); 

	print json_encode($response); // Return response
?>