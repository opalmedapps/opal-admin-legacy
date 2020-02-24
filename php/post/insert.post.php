<?php

include_once('post.inc');

$OAUserId = strip_tags($_POST["OAUser"]["id"]);
$sessionId = strip_tags($_POST["OAUser"]["sessionid"]);

$sanitizedPost = Post::validateAndSanitize($_POST);
if(!$sanitizedPost)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid post format");

$postObject = new Post($OAUserId, $sessionId);
$postObject->insertPost($sanitizedPost);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);