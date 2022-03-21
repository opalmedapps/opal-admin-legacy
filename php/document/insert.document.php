<?php

include_once("../config.php");

$document = new TriggerDocument();
$document->insertDocument($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);