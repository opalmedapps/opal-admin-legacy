<?php
include_once('questionnaire.inc');

$questionId = strip_tags($_POST['questionId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$question = new Question($OAUserId);
$questionDetails = $question->getQuestionDetails($questionId);
unset($questionDetails['question']);
unset($questionDetails["tableName"]);
unset($questionDetails["subTableName"]);
unset($questionDetails["display"]);
unset($questionDetails["definition"]);
unset($questionDetails["question"]);

header('Content-Type: application/javascript');
echo json_encode($questionDetails);
?>
