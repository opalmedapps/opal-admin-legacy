<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$alertId = strip_tags($_POST['alertId']);

$alert = new Alert(); // Object
$response = $alert->getAlertDetails($alertId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response
