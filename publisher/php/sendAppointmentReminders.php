<?php
require_once('NextDayAppointmentNotification.php');
require_once('CustomPushNotification.php');

// Send push notification reminders for the patients who have an appointment the following day
NextDayAppointmentNotification::createAndSendNotifications();
