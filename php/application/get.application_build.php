<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get the application build */

include_once('application.inc');

$appObject = new Application; // Object
$build = $appObject->getApplicationBuild();

echo json_encode($build);
