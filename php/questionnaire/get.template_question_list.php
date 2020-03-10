<?php
include_once('questionnaire.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$answerType = new TemplateQuestion($OAUserId);
$answerTypeCategoryList = $answerType->getTemplateQuestionList();

header('Content-Type: application/javascript');
echo json_encode($answerTypeCategoryList);
