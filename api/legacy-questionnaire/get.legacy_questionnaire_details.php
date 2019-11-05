<?php
header('Content-Type: application/javascript');
/* To get details on a legacy questionnaire */
include_once('legacy-questionnaire.inc');

$serial = strip_tags($_POST['serial']);
$legacyQuestionnaire = new LegacyQuestionnaire; // Object
$legacyQuestionnaireDetails = $legacyQuestionnaire->getLegacyQuestionnaireDetails($serial);

echo json_encode($legacyQuestionnaireDetails);