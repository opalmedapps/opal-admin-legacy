<?php
    /* To generate a QRCode and return the path length */
    include_once('hospital-map.inc');

	// Retrieve FORM params
	$callback   = $_GET['callback'];
    $qrid       = $_GET['qrid'];
    $oldqrid    = $_GET['oldqrid'];

    $hosMap = new HospitalMap; // Object

    // Call function 
    $qrCode = $hosMap->generateQRCode($qrid, $oldqrid);

    // Callback to http request
    print $callback.'('.json_encode($qrCode).')';
?>
