<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object, check firebase credential

// Get all security questions
$language = strtolower($_POST['language']);

$backendApi = new NewOpalApiCall(
    '/api/security-questions/',
    'GET',
    $language,
    [],
    );

$response = $backendApi->execute(); // response is string json

header('Content-Type: application/javascript');
print $response; // Return response
