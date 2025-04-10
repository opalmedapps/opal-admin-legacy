<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$patient = new Patient();
$questionnaire = new Questionnaire();

$data = json_decode(file_get_contents('php://input'), true);
$patient->updatePatient($data);

// If patient was successfully updated/synced
// get patient's Firebase username
// and update the "QuestionnaireDB.answerQuestionnaire.respondentDisplayName" field

$username = $patient->getPatientFirebaseUsername($data["mrns"]);

if (isset($username)) {
    $questionnaire->updateAnswerQuestionnaireRespondent(
        $username,
        $data["name"]["firstName"],
        $data["name"]["lastName"]
    );
}

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
