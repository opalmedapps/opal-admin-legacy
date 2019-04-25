<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$questionId = strip_tags($_GET['questionId']);
$userId = strip_tags($_GET['userid']);

$question = new Question($userId);
$questionDetails = $question->getQuestionDetails($questionId);

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionDetails).')';
?>
