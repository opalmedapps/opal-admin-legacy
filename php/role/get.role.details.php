<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('role.inc');

$roleId = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', strip_tags($_POST['roleId']));

$role = new Role(); // Object
$response = $role->getRoleDetails($roleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response
