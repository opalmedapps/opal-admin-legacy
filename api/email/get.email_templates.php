<?php
header('Content-Type: application/javascript');
/* To get a list of existing email templates */

include_once('email.inc');

$emailObj = new Email; // Object
$existingEmailList = $emailObj->getEmailTemplates();

echo json_encode($existingEmailList);