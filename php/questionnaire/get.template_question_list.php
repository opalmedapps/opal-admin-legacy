<?php
include_once("../config.php");

$answerType = new TemplateQuestion();
$answerTypeCategoryList = $answerType->getTemplateQuestionList();

header('Content-Type: application/javascript');
echo json_encode($answerTypeCategoryList);
