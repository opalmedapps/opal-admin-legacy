<?php

header('Content-Type: application/javascript');
include_once('notification.inc');

$notification = new Notification; // Object
$existingNotificationList = $notification->getNotifications();

echo json_encode($existingNotificationList);