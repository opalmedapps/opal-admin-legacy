<?php
include_once('questionnaire.inc');

$templateQuestion = new TemplateQuestion(); // Object
$templateQuestionList = $templateQuestion->getTemplatesQuestions();

header('Content-Type: application/javascript');
echo json_encode($templateQuestionList);