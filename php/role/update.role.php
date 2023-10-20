<?php

include_once('role.inc');

$role = new Role();
$role->updateRole($_POST);
// call new backend api to update add/remove users form User Managers group as per changes in role access rights
$role->updateUserGroupNewBackend($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
