<?php

include_once("../config.php");

$aliasObject = new Alias();
$response = $aliasObject->updateAliasPublishFlags($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);