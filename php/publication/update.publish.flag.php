<?php
include_once('publication.inc');

//$questionnaire_serNum = strip_tags($_POST['questionnaire_serNum']);

$OAUserId = strip_tags($_POST["OAUserId"]);
$sessionId = strip_tags($_POST["sessionId"]);
$publicationList = $_POST["flagList"];

$publishedQuestionnaire = new Publication($OAUserId, $sessionId);
$clearedPublishList = $publishedQuestionnaire->validateAndSanitizePublicationList($publicationList);

$publishedQuestionnaire->updatePublicationFlags($clearedPublishList);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);
