<?php
header('Content-Type: application/javascript');
/* To get logs on a particular email for highcharts */
include_once('email.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$email = new Email; // Object
$emailLogs = $email->getEmailChartLogs($serial);

echo json_encode($emailLogs);