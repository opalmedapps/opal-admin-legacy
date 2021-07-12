<?php
include_once("../config.php");

$questionnaire = new Questionnaire();
$questionnaireArray = $questionnaire->validateAndSanitize($_POST);

if(!$questionnaireArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire format");

$questionnaire->updateQuestionnaire($questionnaireArray);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);