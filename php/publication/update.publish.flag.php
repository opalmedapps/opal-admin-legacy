<?php
include_once('publication.inc');

//$questionnaire_serNum = strip_tags($_POST['questionnaire_serNum']);

$publicationList = $_POST["flagList"];
$publishedQuestionnaire = new Publication();
$publishedQuestionnaire->updatePublicationFlags($publicationList);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);