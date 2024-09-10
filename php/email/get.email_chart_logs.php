<?php
header('Content-Type: application/javascript');
/* To get logs on a particular email for charts */
include_once('email.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$email = new Email; // Object
