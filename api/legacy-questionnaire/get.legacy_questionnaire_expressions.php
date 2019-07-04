<?php
header('Content-Type: application/javascript');
/* To get a list of questionnaire expressions from the legacy questionnaire database */
include_once('legacy-questionnaire.inc');

$legacyQuestionnaire = new LegacyQuestionnaire; // Object
$legacyQuestionnaireExpressions = $legacyQuestionnaire->getLegacyQuestionnaireExpressions();

echo json_encode($legacyQuestionnaireExpressions);