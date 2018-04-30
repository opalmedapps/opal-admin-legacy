<?php
	/* To get logs on a particular email for highcharts */
	include_once('email.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serial = ( $_GET['serial'] === 'undefined' ) ? null : $_GET['serial'];

	$email = new Email; // Object

	// Call function
	$emailLogs = $email->getEmailChartLogs($serial);

	// Callback to http request
	print $callback.'('.json_encode($emailLogs).')';

?>
