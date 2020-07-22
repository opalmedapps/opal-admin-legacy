<?php
include_once("../config.php");

$postId = strip_tags($_POST['postId']);

$post = new Post(); // Object
$response = $post->getPostDetails($postId);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);