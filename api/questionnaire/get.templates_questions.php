<?php
include_once('questionnaire.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$templateQuestion = new TemplateQuestion($OAUserId); // Object
$templateQuestionList = $templateQuestion->getTemplatesQuestions();

header('Content-Type: application/javascript');
echo json_encode($templateQuestionList);