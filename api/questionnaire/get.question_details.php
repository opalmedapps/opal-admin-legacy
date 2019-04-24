<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$questionId = strip_tags($_GET['questionSerNum']);
$userId = strip_tags($_GET['userid']);

$questionId = 915;
$userId = 20;

$question = new Question($userId);
$questionDetails = $question->getQuestionDetails($questionId);

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionDetails).')';
?>
