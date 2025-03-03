<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get list logs on a particular email */
include_once('email.inc');

$serials = json_decode($_POST['serials']);
$email = new Email; // Object
$emailLogs = $email->getEmailListLogs($serials);

echo json_encode($emailLogs);