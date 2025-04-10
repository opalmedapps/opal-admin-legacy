<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/* To delete a custom code */
include_once('custom.code.inc');

$customCodeId = strip_tags($_POST['customCodeId']);
$moduleId = strip_tags($_POST['moduleId']);

$customCode = new CustomCode(); // Object
$response = $customCode->deleteCustomCode($customCodeId, $moduleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
