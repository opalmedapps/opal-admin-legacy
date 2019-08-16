<?php
/* To insert a newly-created question */
include_once('questionnaire.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$questionArray = Question::validateAndSanitize($_POST);
if(!$questionArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question format");

$questionObj = new Question($OAUserId);
$questionObj->insertQuestion($questionArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
?>
