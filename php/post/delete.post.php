<?php
include_once("../config.php");

$postId = strip_tags($_POST["serial"]);

$postObject = new Post();
$response = $postObject->deletePost($postId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);