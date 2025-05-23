<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$patientObj = new PatientAdministration(); //Object
$response = $patientObj->getAllAccessLevel();

header('Content-Type: application/javascript');
print json_encode($response); // Return response
