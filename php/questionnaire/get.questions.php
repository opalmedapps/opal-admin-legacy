<?php
include_once("../config.php");

$question = new Question();
$questionList = $question->getQuestions();

header('Content-Type: application/javascript');
echo json_encode($questionList);
?>
