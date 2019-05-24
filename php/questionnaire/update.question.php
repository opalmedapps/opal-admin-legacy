<?php
header('Content-Type: application/javascript');
/* To update a question */
include_once('questionnaire.inc');

$questionArray = Question::validateAndSanitize($_POST);
if(!$questionArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question format");

$OAUserId = $questionArray["OAUserId"];

$questionObj = new Question($OAUserId);
$questionObj->updateQuestion($questionArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);

?>
