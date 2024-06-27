<?php

    include_once('HospitalPushNotification.php');
    $pathname 	= __DIR__;
    $abspath 	= str_replace('php', 'modules', $pathname);

    $patientSerNum  = escapeshellarg($_POST['patientSerNum']);
    $ser            = escapeshellarg($_POST['ser']);
    $typeRequest    = escapeshellarg($_POST['typeRequest']);

    $execStr = "perl " . $abspath . "/PushNotificationFromPHP.pm $patientSerNum $ser $typeRequest";

    system($execStr);
?>