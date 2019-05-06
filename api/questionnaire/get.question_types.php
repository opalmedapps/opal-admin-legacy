<?php
header('Content-Type: application/javascript');
/* To get a list of answer types */
include_once('questionnaire.inc');

// Retrieve FORM params
$callback = $_GET['callback'];
$userId = strip_tags($_GET['userId']);

$questionType = new QuestionType($userId); // Object

// Call function
$questionTypeList = $questionType->getQuestionTypes();

// Callback to http request
print $callback.'('.json_encode($questionTypeList).')';
?>
