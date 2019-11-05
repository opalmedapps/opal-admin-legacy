<?php
include_once('questionnaire.inc');

// Retrieve form param
$OAUserId = strip_tags($_POST["OAUserId"]);

$question = new Question($OAUserId);
$questionList = $question->getQuestions();

header('Content-Type: application/javascript');
echo json_encode($questionList);
?>
