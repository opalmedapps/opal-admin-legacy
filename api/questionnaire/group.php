<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];
	$userid = $_GET['userid'];
	
    $group = new Group();

    $groupList = $group->getGroups($userid);

    // Callback to http request
    print $callback.'('.json_encode($groupList).')';
?>