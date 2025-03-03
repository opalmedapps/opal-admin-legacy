<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get logs on a particular email for charts */
include_once('email.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$email = new Email; // Object
