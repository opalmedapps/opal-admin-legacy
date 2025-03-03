<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get cron logs for charts */
include_once('cron.inc');

// Retrieve FORM params
$contents = json_decode($_POST['contents'], true);

$cron = new Cron; // Object
