<?php
/* To get a list of distinct answer type categories */
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$OAUserId = strip_tags($_GET['OAUserId']);

$answerType = new QuestionType($OAUserId); // Object
$answerTypeCategoryList = $answerType->getQuestionTypeList();

// Callback to http request
header('Content-Type: application/javascript');
print $callback.'('.json_encode($answerTypeCategoryList).')';
?>
