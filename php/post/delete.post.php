<?php

include_once('post.inc');

$postId = strip_tags($_POST["serial"]);

$postObject = new Post();
$response["message"] = $postObject->deletePost($postId);
$response["code"] = HTTP_STATUS_SUCCESS;

header('Content-Type: application/javascript');
echo json_encode($response);