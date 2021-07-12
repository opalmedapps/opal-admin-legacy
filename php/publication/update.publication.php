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
http_response_code(HTTP_STATUS_SUCCESS);