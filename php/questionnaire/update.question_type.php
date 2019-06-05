<?php
/**
 * User: Dominic Bourdua
 * Date: 6/4/2019
 * Time: 10:28 AM
 */

include_once('questionnaire.inc');

$questionTypeArray = QuestionType::validateAndSanitize($_POST);

if(!$questionTypeArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");

$answerTypeObj = new QuestionType($questionTypeArray["OAUserId"]); // Object
$OAUserId = $questionTypeArray["OAUserId"];

print_r($questionTypeArray);die();

$questionObj = new QuestionType($OAUserId);
$questionObj->updateQuestionType($questionTypeArray);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);