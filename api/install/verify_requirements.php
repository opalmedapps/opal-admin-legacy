<?php
	header('Content-Type: application/javascript');
	/* To verify installation requirements */

	$callback 	= $_GET['callback'];

	$pathname 	= __DIR__;
	$abspath 	= str_replace('api' . DIRECTORY_SEPARATOR . 'install', '', $pathname);

	include_once($abspath . 'php' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Install.php');

	$installObj = new Install; // Object

	// Call function
	$response = $installObj->verifyRequirements($abspath);

	print $callback.'('.json_encode($response).')';

?>
