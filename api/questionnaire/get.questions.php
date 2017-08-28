<?php

	/* To get a list of existing questions */
	include_once('questionnaire.inc');

	// Retrieve form param
	$callback = $_GET['callback'];

	$question = new Question(); // Object

	// Call function
	$questionList = $question->getQuestions();

	// Callback to http request
	print $callback.'('.json_encode($questionList).')';
?>