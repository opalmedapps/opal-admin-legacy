<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$diagnosisObject = new Diagnosis();
$diagnoses = $diagnosisObject->getPatientDiagnoses($_POST);

header('Content-Type: application/javascript');
echo json_encode($diagnoses);
