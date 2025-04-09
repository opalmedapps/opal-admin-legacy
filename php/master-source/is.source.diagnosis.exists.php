<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$sourceDiag = new MasterSourceDiagnosis(); // Object
$results = $sourceDiag->doesDiagnosisExists($_POST);

header('Content-Type: application/javascript');
echo json_encode($results);
