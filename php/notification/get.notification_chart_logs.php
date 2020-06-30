<?php
header('Content-Type: application/javascript');
/* To get logs on a particular notification */
include_once('notification.inc');

$serial = ( strip_tags($_POST['serial']) === 'undefined' ) ? null : strip_tags($_POST['serial']);
$notification = new Notification; // Object
$notificationLogs = $notification->getNotificationChartLogs($serial);

echo json_encode($notificationLogs);