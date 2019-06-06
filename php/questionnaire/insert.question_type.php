<?php
/* To insert a newly-created answer type into our database */
include_once('questionnaire.inc');

// Construct array from FORM params
$answerTypeObj = new QuestionType(strip_tags($_POST["OAUserId"])); // Object
$answerTypeObj->insertQuestionType($_POST);

header('Content-Type: application/javascript');
$response['message'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);


?>
