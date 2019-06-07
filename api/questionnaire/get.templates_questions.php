<?php
header('Content-Type: application/javascript');
/* To get a list of answer types */
include_once('questionnaire.inc');

// Retrieve FORM params
$callback = $_GET['callback'];
$OAUserId = strip_tags($_GET['OAUserId']);

$templateQuestion = new TemplateQuestion($OAUserId); // Object

// Call function
$templateQuestionList = $templateQuestion->getTemplatesQuestions();

// Callback to http request
print $callback.'('.json_encode($templateQuestionList).')';
?>
