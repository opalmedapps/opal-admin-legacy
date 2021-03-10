<?php
include_once("../config.php");

$alias = new Alias();
$response = $alias->deleteAlias($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
