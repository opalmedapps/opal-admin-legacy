<?php
include_once('publication.inc');

//$questionnaire_serNum = strip_tags($_POST['questionnaire_serNum']);

$publicationList = $_POST["flagList"];

$publishedQuestionnaire = new Publication();
$clearedPublishList = $publishedQuestionnaire->validateAndSanitizePublicationList($publicationList);

$publishedQuestionnaire->updatePublicationFlags($clearedPublishList);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
http_response_code(HTTP_STATUS_SUCCESS);