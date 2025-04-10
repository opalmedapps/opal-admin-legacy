<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get details of a particular email template */

include_once('email.inc');

$serial = strip_tags($_POST['serial']);
$emailObj = new Email; // Object
$emailDetails = $emailObj->getEmailDetails($serial);

echo json_encode($emailDetails);
