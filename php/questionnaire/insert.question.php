<?php
/* To insert a newly-created question */
include_once('questionnaire.inc');


$userId = strip_tags($_POST['userId']);
$questionArray = Question::validateAndSanitize($_POST);

if(!$questionArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question format");
$questionObj = new Question($userId);
$questionObj->insertQuestion($questionArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
?>
