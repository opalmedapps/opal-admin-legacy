<?php

/* To delete a question */
include_once('questionnaire.inc');

// Retrieve FORM param
$templateQuestionId = strip_tags($_POST['ID']);

// Call function
$templateQuestionObj = new TemplateQuestion(); // Object

$response = $templateQuestionObj->deleteTemplateQuestion($templateQuestionId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);