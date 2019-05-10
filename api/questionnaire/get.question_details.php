<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$questionId = strip_tags($_GET['questionId']);
$userId = strip_tags($_GET['userId']);

$question = new Question($userId);
$questionDetails = $question->getQuestionDetails($questionId);
unset($questionDetails['question']);
unset($questionDetails["tableName"]);
unset($questionDetails["subTableName"]);
unset($questionDetails["display"]);
unset($questionDetails["definition"]);
unset($questionDetails["question"]);

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionDetails).')';
?>
