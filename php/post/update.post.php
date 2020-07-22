<?php
include_once("../config.php");

$sanitizedPost = Post::validateAndSanitize($_POST);
if(!$sanitizedPost)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid post format");

$postObject = new Post();
$response = $postObject->updatePost($sanitizedPost);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);