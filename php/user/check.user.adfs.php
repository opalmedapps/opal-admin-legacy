<?php

include_once("../config.php");

$userObj = new User();

$is_ad_user_exist = $userObj->isADUserExist($_POST);
header('Content-Type: application/javascript');

// true if user exists in AD system, false otherwise.
echo json_encode(array("is_exist"=>$is_ad_user_exist));
