<?php
/**
 * User: Dominic Bourdua
 * Date: 6/18/2019
 * Time: 2:11 PM
 */

include_once('questionnaire.inc');
$OAUserId = strip_tags($_POST['OAUserId']);

print_R($_POST);die();

$questionnaire = new PublishedQuestionnaire($OAUserId);
$questionnaire->insertPublishedQuestionnaire($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);