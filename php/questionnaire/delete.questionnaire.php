<?php
include_once('questionnaire.inc');

$questionnaireId = strip_tags($_POST['ID']);
$userId = strip_tags($_POST['userId']);
$questionnaireObj = new Questionnaire($userId);

$response = $questionnaireObj->deleteQuestionnaire($questionnaireId);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
?>
