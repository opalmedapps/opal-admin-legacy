<?php
include_once('questionnaire.inc');

$questionnaireId = strip_tags($_POST['ID']);
$OAUserId = strip_tags($_POST['OAUserId']);
$questionnaireObj = new Questionnaire($OAUserId);

$response = $questionnaireObj->deleteQuestionnaire($questionnaireId);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
?>
