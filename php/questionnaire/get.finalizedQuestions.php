<?php
include_once("../config.php");

$question = new Question();
$questionList = $question->getFinalizedQuestions();

header('Content-Type: application/javascript');
echo json_encode($questionList);