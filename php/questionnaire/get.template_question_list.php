<?php
include_once('questionnaire.inc');

$answerType = new TemplateQuestion();
$answerTypeCategoryList = $answerType->getTemplateQuestionList();

header('Content-Type: application/javascript');
echo json_encode($answerTypeCategoryList);
