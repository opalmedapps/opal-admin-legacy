<?php
include_once('questionnaire.inc');

$question = new Question();
$questionList = $question->getQuestions();

header('Content-Type: application/javascript');
echo json_encode($questionList);
?>
