<?php
include_once("../config.php");

$questionnaire = new Questionnaire();
$questionnairesList = $questionnaire->getQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnairesList);
?>
