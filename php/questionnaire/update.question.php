<?php

include_once('questionnaire.inc');

$questionArray = Question::validateAndSanitize($_POST);
if(!$questionArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question format");

$questionObj = new Question();
$questionObj->updateQuestion($questionArray);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);