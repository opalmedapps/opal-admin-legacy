<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$auditId = strip_tags($_POST['ID']);

$alert = new Audit(); // Object
$response = $alert->getAuditDetails($auditId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response