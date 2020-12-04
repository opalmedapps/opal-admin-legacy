<?php
    $pathname 	= __DIR__;
    $abspath 	= str_replace('php', 'modules', $pathname);

    $patientSerNum    = $_POST['patientSerNum'];
    $ser                   = $_POST['ser'];
    $typeRequest      = $_POST['typeRequest'];

    $execStr = "perl " . $abspath . "/PushNotificationFromPHP.pm $patientSerNum $ser $typeRequest";

    system($execStr);
?>