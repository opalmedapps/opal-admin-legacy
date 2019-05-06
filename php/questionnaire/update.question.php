<?php
header('Content-Type: application/javascript');
/* To update a question */
include_once('questionnaire.inc');

$questionArray = Question::validateAndSanitize($_POST);
$userId = $questionArray["userId"];

$questionObj = new Question($userId);
$questionObj->updateQuestion($questionArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);

?>
