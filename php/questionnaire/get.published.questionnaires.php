<?php
include_once("../config.php");

$questionnaire = new Questionnaire();
$questionnairesList = $questionnaire->getPublishedQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnairesList);