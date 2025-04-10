<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
include_once("../config.php");

// Construct array from FORM params
$testResult = new TestResult; // Object

$response = $testResult->insertTestResult($_POST);
print json_encode($response); // Return response
