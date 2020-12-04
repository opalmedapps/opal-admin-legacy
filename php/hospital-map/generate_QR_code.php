<?php
header('Content-Type: application/javascript');
/* To generate a QRCode and return the path length */
include_once('hospital-map.inc');

$qrid = $_POST['qrid'];
$oldqrid = $_POST['oldqrid'];
$hosMap = new HospitalMap; // Object
$qrCode = $hosMap->generateQRCode($qrid, $oldqrid);

echo json_encode($qrCode);