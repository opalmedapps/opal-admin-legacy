<?php
header('Content-Type: application/javascript');
/* To get a list of existing legacy questionnaires */

include_once('legacy-questionnaire.inc');

$legacyQuestionnaire = new LegacyQuestionnaire; // Object
$legacyQuestionnaireList = $legacyQuestionnaire->getLegacyQuestionnaires();

echo json_encode($legacyQuestionnaireList);