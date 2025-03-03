<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get a list email types */

include_once('email.inc');

$emailObj = new Email; // Object
$types = $emailObj->getEmailTypes();

echo json_encode($types);