<?php

	/* To insert a newly-created question group */
	include_once('questionnaire.inc');

	// Construct array from FORM params
	$questionGroupArray = array(
		'name_EN'			=> $_POST['name_EN'],
		'name_FR'			=> $_POST['name_FR'],
		'category_EN'		=> $_POST['category_EN'],
		'category_FR'		=> $_POST['category_FR'],
		'private'			=> $_POST['private'],
		'created_by'		=> $_POST['created_by'],
		'last_updated_by'	=> $_POST['last_updated_by'],
		'library_serNum'	=> $_POST['library_serNum']
	);

	$questionGroupObj = new QuestionGroup; // Object

	// Call function
	$questionGroupObj->insertQuestionGroup($questionGroupArray);

?>