<?php

// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
include_once("../config.php");

$patientObj = new PatientAdministration(); //Object

$patientObj->updatePatientEmail($_POST);
http_response_code(HTTP_STATUS_SUCCESS);