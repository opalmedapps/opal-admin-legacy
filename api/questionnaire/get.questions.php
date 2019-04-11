<?php
	header('Content-Type: application/javascript');
	/* To get a list of existing questions */
	include_once('questionnaire.inc');

	// Retrieve form param
	$callback = $_GET['callback'];
	$userId = $_GET["userid"];

	$question = new Question($userId); // Object

	// Call function
	$questionList = $question->getQuestions();

	// Callback to http request
	print $callback.'('.json_encode($questionList).')';
?>
