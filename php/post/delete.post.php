<?php

include_once('post.inc');

$OAUserId = strip_tags($_POST["OAUser"]["id"]);
$sessionId = strip_tags($_POST["OAUser"]["sessionid"]);
$postId = strip_tags($_POST["serial"]);

$postObject = new Post($OAUserId, $sessionId);
$response["message"] = $postObject->deletePost($postId);
$response["code"] = HTTP_STATUS_SUCCESS;

header('Content-Type: application/javascript');
echo json_encode($response);