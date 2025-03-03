<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('HospitalPushNotification.php');
    $pathname 	= __DIR__;
    $abspath 	= str_replace('php', 'modules', $pathname);

    $patientSerNum  = escapeshellarg($_POST['patientSerNum']);
    $ser            = escapeshellarg($_POST['ser']);
    $typeRequest    = escapeshellarg($_POST['typeRequest']);

    $execStr = "perl " . $abspath . "/PushNotificationFromPHP.pm $patientSerNum $ser $typeRequest";

    system($execStr);
?>