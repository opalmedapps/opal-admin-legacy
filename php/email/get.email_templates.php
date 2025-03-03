<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get a list of existing email templates */

include_once('email.inc');

$emailObj = new Email; // Object
$existingEmailList = $emailObj->getEmailTemplates();

echo json_encode($existingEmailList);