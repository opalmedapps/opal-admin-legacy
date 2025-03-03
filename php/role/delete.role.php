<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('role.inc');

$roleId = strip_tags($_POST['roleId']);

$role = new Role(); // Object
$response = $role->deleteRole($roleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);