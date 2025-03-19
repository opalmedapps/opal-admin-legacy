<?php
header('Content-Type: application/javascript');
/* To get logs on a particular educational material for charts */
include_once('educational-material.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$eduMat = new EduMaterial;