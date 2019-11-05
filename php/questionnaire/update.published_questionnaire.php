<?php
/**
 * User: Dominic Bourdua
 * Date: 6/20/2019
 * Time: 10:09 AM
 */

include_once('questionnaire.inc');


$OAUserId = strip_tags($_POST['OAUserId']);
$publishedQuestionnaire = new PublishedQuestionnaire($OAUserId);
$publishedQuestionnaire->updatePublishedQuestionnaire($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);