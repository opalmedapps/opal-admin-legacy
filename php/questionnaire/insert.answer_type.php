<?php
	/* To insert a newly-created answer type into our database */
	include_once('questionnaire.inc');

	// Construct array from FORM params
	$answerTypeArray = array(
		'name_EN'			=> $_POST['name_EN'],
		'name_FR'			=> $_POST['name_FR'],
		'category_EN'		=> $_POST['category_EN'],
		'category_FR'		=> $_POST['category_FR'],
		'private'			=> $_POST['private'],
		'last_updated_by'	=> $_POST['last_updated_by'],
		'created_by'		=> $_POST['created_by'],
		'options'			=> $_POST['options']
	);

	$answerTypeObj = new AnswerType; // Object

	// Call function
	$answerTypeObj->insertAnswerType($answerTypeArray);
?>