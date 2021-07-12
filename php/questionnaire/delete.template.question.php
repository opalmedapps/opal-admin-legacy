<?php
include_once("../config.php");

// Retrieve FORM param
$templateQuestionId = strip_tags($_POST['ID']);

// Call function
$templateQuestionObj = new TemplateQuestion(); // Object

$response = $templateQuestionObj->deleteTemplateQuestion($templateQuestionId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);