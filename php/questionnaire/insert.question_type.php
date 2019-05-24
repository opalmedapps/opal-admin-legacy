<?php
/* To insert a newly-created answer type into our database */
include_once('questionnaire.inc');

// Construct array from FORM params

$questionTypeArray = QuestionType::validateAndSanitize($_POST);

if(!$questionTypeArray)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");

$answerTypeObj = new QuestionType($questionTypeArray["OAUserId"]); // Object

// Call function
$answerTypeObj->insertQuestionType($questionTypeArray);

header('Content-Type: application/javascript');
$response['message'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);


?>
