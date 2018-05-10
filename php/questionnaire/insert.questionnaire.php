<?php
	
	/* To insert a newly-created questionnaire */
	include_once('questionnaire.inc');

	// Construct array from FORM params
	$questionnaireArray = array(
		'name_EN'	 		=> $_POST['name_EN'],
		'name_FR'	 		=> $_POST['name_FR'],
		'private' 			=> $_POST['private'],
		'publish' 			=> $_POST['publish'],
		'last_updated_by'	=> $_POST['last_updated_by'],
		'created_by'		=> $_POST['created_by'],
		'tags'				=> $_POST['tags'],
		'questiongroups'	=> $_POST['groups'],
		'filters'			=> $_POST['filters'],
		'user' 				=> $_POST['user']
	);

	$questionnaireObj = new Questionnaire; // Object

	// Call function
	$questionnaireObj->insertQuestionnaire($questionnaireArray);
?>