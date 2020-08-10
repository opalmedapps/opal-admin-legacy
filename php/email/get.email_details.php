<?php
header('Content-Type: application/javascript');
/* To get details of a particular email template */

include_once('email.inc');

$serial = strip_tags($_POST['serial']);
$emailObj = new Email; // Object
$emailDetails = $emailObj->getEmailDetails($serial);

echo json_encode($emailDetails);