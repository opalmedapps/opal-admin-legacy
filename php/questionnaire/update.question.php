<?php
header('Content-Type: application/javascript');
/* To update a question */
include_once('questionnaire.inc');

$questionArray = Question::validateAndSanitize($_POST);
$userId = $questionArray["userId"];

$questionObj = new Question($userId);
$response = $questionObj->updateQuestion($questionArray);

print_r($questionArray);die();

print json_encode($response); // Return response
?>
