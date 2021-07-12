<?php
include_once("../config.php");

$questionId = strip_tags($_POST['questionId']);

$question = new Question();
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
