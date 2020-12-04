<?php
include_once('questionnaire.inc');

$question = new Question();
$questionList = $question->getFinalizedQuestions();

header('Content-Type: application/javascript');
echo json_encode($questionList);