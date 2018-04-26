<?php
	/* To get logs on a particular educational material for highcharts */
	include_once('educational-material.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = ( $_GET['serial'] === 'undefined' ) ? null : $_GET['serial'];

	$eduMat = new EduMaterial; // Object

	// Call function
	$educationalMaterialLogs = $eduMat->getEducationalMaterialChartLogs($serial);

	// Callback to http request
	print $callback.'('.json_encode($educationalMaterialLogs).')';

?>
