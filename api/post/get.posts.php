<?php

header('Content-Type: application/javascript');
include_once('post.inc');

$post = new Post; // Object
$existingPostList = $post->getPosts();
echo json_encode($existingPostList);