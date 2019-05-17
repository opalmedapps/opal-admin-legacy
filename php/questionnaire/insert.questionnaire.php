<?php

include_once('questionnaire.inc');

$userId = strip_tags($_POST['userId']);
print_r($_POST);

$questionnaire = new Questionnaire($userId);
$questionnaireArray = $questionnaire->validateAndSanitize($_POST);
if(!$questionnaireArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid questionnaire format");

$questionnaire->insertQuestionnaire($questionnaireArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);

?>
