<?php
	/* To insert a newly-created question */
	include_once('questionnaire.inc');

	// Construct an array from FORM params
	$questionArray = array(
		'text_EN'				=> $_POST['text_EN'],
		'text_FR'				=> $_POST['text_FR'],
		'answertype_serNum'		=> $_POST['answertype_serNum'],
		'questiongroup_serNum'	=> $_POST['questiongroup_serNum'],
		'last_updated_by'		=> $_POST['last_updated_by'],
		'created_by'			=> $_POST['created_by']
	);

	$questionObj = new Question; // Object

	// Call function
	$questionObj->insertQuestion($questionArray);
?>