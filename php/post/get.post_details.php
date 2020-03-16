<?php

include_once('post.inc');

$postId = strip_tags($_POST['postId']);
$OAUserId = strip_tags($_POST['OAUserId']);
$post = new Post($OAUserId); // Object
$response = $post->getPostDetails($postId);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);