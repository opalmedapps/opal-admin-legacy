<?php
include_once('questionnaire.inc');

$questionnaireId = strip_tags($_POST['ID']);
$questionnaireObj = new Questionnaire();

$response = $questionnaireObj->deleteQuestionnaire($questionnaireId);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
?>
