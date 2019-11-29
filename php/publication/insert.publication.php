<?php
/**
 * User: Dominic Bourdua
 * Date: 6/18/2019
 * Time: 2:11 PM
 */

include_once('publication.inc');


$OAUserId = strip_tags($_POST['OAUserId']);
$sessionId = strip_tags($_POST['user']['sessionid']);

$publication = new Publication($OAUserId, $sessionId);
$publication->insertPublication($_POST);

print_r($_POST);die();


header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);