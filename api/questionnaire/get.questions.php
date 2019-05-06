<?php
include_once('questionnaire.inc');

// Retrieve form param
$callback = $_GET['callback'];
$userId = $_GET["userId"];

$question = new Question($userId);
$questionList = $question->getQuestions();

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionList).')';
?>
