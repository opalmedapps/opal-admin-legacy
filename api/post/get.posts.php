<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$OAUSerID = strip_tags($_POST["OAUserId"]);

$post = new Post($OAUSerID); // Object
$existingPostList = $post->getPosts();
echo json_encode($existingPostList);