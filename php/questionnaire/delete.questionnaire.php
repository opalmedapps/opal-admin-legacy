<?php
include_once('questionnaire.inc');

$questionnaireId = strip_tags($_POST['ID']);
$questionnaireObj = new Questionnaire();

$response = $questionnaireObj->deleteQuestionnaire($questionnaireId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);