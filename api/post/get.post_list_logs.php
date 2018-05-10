<?php
	/* To get list logs on a particular post */
	include_once('post.inc');

	// Retrieve FORM params
	$callback = $_GET['callback'];
	$serials = json_decode($_GET['serials']);
	$type = ( $_GET['type'] === 'undefined' ) ? null : $_GET['type'];

	$post = new Post; // Object

	// Call function
	$postLogs = $post->getPostListLogs($serials, $type);

	// // Callback to http request
	print $callback.'('.json_encode($postLogs).')';

?>
