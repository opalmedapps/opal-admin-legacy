<?php
include_once('questionnaire.inc');

$questionnaire = new Questionnaire();
$questionnairesList = $questionnaire->getQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnairesList);
?>
