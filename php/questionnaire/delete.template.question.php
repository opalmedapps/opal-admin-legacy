<?php
header('Content-Type: application/javascript');
/* To delete a question */
include_once('questionnaire.inc');

// Retrieve FORM param
$templateQuestionId = strip_tags($_POST['ID']);

// Call function
$templateQuestionObj = new TemplateQuestion(); // Object

$response = $templateQuestionObj->deleteTemplateQuestion($templateQuestionId);

print json_encode($response); // Return response
?>
