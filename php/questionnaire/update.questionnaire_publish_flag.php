<?php
include_once('questionnaire.inc');

$questionnaire_serNum = strip_tags($_POST['questionnaire_serNum']);
$OAUserId = strip_tags($_POST["OAUserId"]);
$sessionId = strip_tags($_POST["sessionId"]);
$questionnaireList = $_POST["flagList"];

$publishedQuestionnaire = new PublishedQuestionnaire(strip_tags($_POST['OAUserId']));
$clearedPublishList = $publishedQuestionnaire->validateAndSanitizePublicationList($questionnaireList);
$publishedQuestionnaire->updatePublicationFlags($clearedPublishList);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);


//print_r($clearedPublishList);die();

// Call function
//$questionnaireObj->publishQuestionnaire($questionnaire_serNum, $user);
?>