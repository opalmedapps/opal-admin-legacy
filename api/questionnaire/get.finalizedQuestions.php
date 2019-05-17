<?php
include_once('questionnaire.inc');

// Retrieve form param
$callback = strip_tags($_GET['callback']);
$userId = strip_tags($_GET["userId"]);

$question = new Question($userId);
$questionList = $question->getFinalizedQuestions();

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionList).')';
?>
