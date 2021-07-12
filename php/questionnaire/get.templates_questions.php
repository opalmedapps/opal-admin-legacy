<?php
include_once("../config.php");

$templateQuestion = new TemplateQuestion(); // Object
$templateQuestionList = $templateQuestion->getTemplatesQuestions();

header('Content-Type: application/javascript');
echo json_encode($templateQuestionList);