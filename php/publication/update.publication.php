<?php
/**
 * User: Dominic Bourdua
 * Date: 6/20/2019
 * Time: 10:09 AM
 */

include_once('publication.inc');

$publication = new Publication();
$publication->updatePublication($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);