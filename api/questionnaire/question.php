<?php
// get list of questions existed in libraries
include_once('questionnaire.inc');

$callback = $_GET['callback'];

$question = new Question();

$questionList = $question->getQuestions();

// Callback to http request
print $callback.'('.json_encode($questionList).')';
?>