<?php
include_once('questionnaire.inc');

// Retrieve form param
$callback = strip_tags($_GET['callback']);
$OAUserId = strip_tags($_GET["OAUserId"]);

$question = new Question($OAUserId);
$questionList = $question->getFinalizedQuestions();

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionList).')';
?>
