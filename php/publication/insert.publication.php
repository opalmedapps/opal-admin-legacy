<?php
/**
 * User: Dominic Bourdua
 * Date: 6/18/2019
 * Time: 2:11 PM
 */

include_once('publication.inc');

$publication = new Publication();
$publication->insertPublication($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);