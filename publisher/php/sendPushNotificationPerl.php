<?php

    include_once('HospitalPushNotification.php');
    $pathname 	= __DIR__;
    $abspath 	= str_replace('php', 'modules', $pathname);

    $patientSerNum  = HospitalPushNotification::sanitizeInput($_POST['patientSerNum']);
    $ser            = HospitalPushNotification::sanitizeInput($_POST['ser']);
    $typeRequest    = HospitalPushNotification::sanitizeInput($_POST['typeRequest']);

    $execStr = "perl " . $abspath . "/PushNotificationFromPHP.pm $patientSerNum $ser $typeRequest";

    system($execStr);
?>