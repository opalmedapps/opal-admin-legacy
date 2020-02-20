<?php
/**
 * User: Dominic Bourdua
 * Date: 6/20/2019
 * Time: 10:09 AM
 */

include_once('publication.inc');


$OAUserId = strip_tags(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $_POST['OAUserId']));
$sessionId = strip_tags(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $_POST['sessionid']));

$publication = new Publication($OAUserId, $sessionId);



$publication->updatePublication($_POST);


header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);