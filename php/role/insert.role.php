<?php

include_once('role.inc');

$role = new Role();
$role->insertRole($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);