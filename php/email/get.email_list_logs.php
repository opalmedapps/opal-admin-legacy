<?php
header('Content-Type: application/javascript');
/* To get list logs on a particular email */
include_once('email.inc');

$serials = json_decode($_POST['serials']);
$email = new Email; // Object
$emailLogs = $email->getEmailListLogs($serials);

echo json_encode($emailLogs);