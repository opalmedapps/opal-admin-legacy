<?php
/**
 * User: Yves Ferdinand
 * Date: 7/11/2021
 */

include_once("../config.php");

$document = new Document();
$document->insertDocument($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);