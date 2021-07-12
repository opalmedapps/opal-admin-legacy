<?php
header('Content-Type: application/javascript');
/* To get list logs on a particular notification */
include_once('notification.inc');

$serials = json_decode($_POST['serials']);
$notification = new Notification; // Object
$notificationLogs = $notification->getNotificationListLogs($serials);

echo json_encode($notificationLogs);