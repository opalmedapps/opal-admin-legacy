<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('custom.code.inc');

$customCodeId = strip_tags($_POST['customCodeId']);
$moduleId = strip_tags($_POST['moduleId']);

$customCode = new CustomCode(); // Object
$response = $customCode->getCustomCodeDetailsAPI($customCodeId, $moduleId);

header('Content-Type: application/javascript');
echo json_encode($response); // Return response