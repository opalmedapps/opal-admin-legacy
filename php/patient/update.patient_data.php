<?php
include_once("../config.php");

$patient = new Patient();
$questionnaire = new Questionnaire();

$data = json_decode(file_get_contents('php://input'), true);
$patient->updatePatient($data);

// If patient was successfuly updated/synced
// get patient's Firebase username
// and update the "QuestionnaireDB.answerQuestionnaire.respondentDisplayName" field

$username = $patient->getPatientFirebaseUsername($data["ramq"]);

if (isset($username)) {
    $questionnaire->updateAnswerQuestionnaireRespondent(
        $username,
        $data["name"]["firstName"],
        $data["name"]["lastName"]
    );
}

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
