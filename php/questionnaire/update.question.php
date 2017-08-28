<?php
	/* To update a question */
	include_once('questionnaire.inc');

	// Construct array from FORM params
	$questionArray = array(
		'serNum'				=> $_POST['serNum'],
		'text_EN'				=> $_POST['text_EN'],
		'text_FR'				=> $_POST['text_FR'],
		'answertype_serNum'		=> $_POST['answertype_serNum'],
		'questiongroup_serNum'	=> $_POST['questiongroup_serNum'],
		'last_updated_by'		=> $_POST['last_updated_by']
	);

	$questionObj = new Question; // Object

	// Call function
	$response = $questionObj->updateQuestion($questionArray);
	print json_encode($response); // Return response
?>