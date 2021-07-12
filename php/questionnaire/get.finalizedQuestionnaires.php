<?php
include_once("../config.php");

$questionnaire = new Questionnaire();
$questionnaireList = $questionnaire->getFinalizedQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnaireList);