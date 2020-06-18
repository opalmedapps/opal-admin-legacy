<?php

include_once('post.inc');

$sanitizedPost = Post::validateAndSanitize($_POST);
if(!$sanitizedPost)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid post format");

$postObject = new Post();
$response["message"] = $postObject->updatePost($sanitizedPost);
$response["code"] = HTTP_STATUS_SUCCESS;

header('Content-Type: application/javascript');
echo json_encode($response);