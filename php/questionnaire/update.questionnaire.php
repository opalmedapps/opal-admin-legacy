<?php
include_once('questionnaire.inc');

$questionnaire = new Questionnaire();
$questionnaireArray = $questionnaire->validateAndSanitize($_POST);

if(!$questionnaireArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire format");

$questionnaire->updateQuestionnaire($questionnaireArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
