<?php
include_once('questionnaire.inc');

$OAUserId = strip_tags($_POST["OAUserId"]);
$question = new Question($OAUserId);
$questionList = $question->getFinalizedQuestions();

header('Content-Type: application/javascript');
echo json_encode($questionList);