<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get details on a cron profile */
include_once('cron.inc');

$cron = new Cron; // Object
$cronDetails = $cron->getCronDetails();

// Callback to http request
echo json_encode($cronDetails);