<?php
include_once('questionnaire.inc');

$questionnaire = new Questionnaire();
$questionnaireList = $questionnaire->getFinalizedQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnaireList);