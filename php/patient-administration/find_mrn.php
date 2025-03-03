<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$pat = new PatientAdministration(); // Object
$response = $pat->findPatientByMRN($_POST);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
