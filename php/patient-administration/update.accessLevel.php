<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object
$patientObj->updatePatientAccessLevel($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

// Update patient data_access in new opal DB
$data_access = $_POST['accessLevel'] == 1 ? 'NTK' : 'ALL';
$legacy_id = $_POST['PatientSerNum'];
$language = strtolower($_POST['language']);

$backendApi = new NewOpalApiCall(
    '/api/patients/legacy/'.$legacy_id.'/',
    'PUT',
    $language,
    ['data_access' => $data_access],
    );

$backendApi->execute();
