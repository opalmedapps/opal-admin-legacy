<?php
// Find delayed lab results and trigger sending off push notifications.
// Only send one notification irregardless of the total number of lab results

require_once('delayedLabPushNotification.php');
require_once('customPushNotification.php');


// Create notifications for the delayed lab results that are available <= now and on the same day
// The lab results that became available on previous days shouldn't be sent again
$delayedLabs = DelayedLabPushNotification::fetchDelayedLabResults();

if ($delayedLabs) {
    DelayedLabPushNotification::createNotificationsForDelayedLabs($delayedLabs);

    // Send notifications in loop; ensure notification is not read
    foreach ($delayedLabs as $lab) {
        $messages = array(
            "title_EN" => $lab["RefTableRowTitle_EN"],
            "message_text_EN" => "",
            "title_FR" => $lab["RefTableRowTitle_FR"],
            "message_text_FR" => "",
        );

        // Call API to send push notification
        $response = customPushNotification::sendNotificationByPatientSerNum($patientSerNum, $language, $messages);
        echo json_encode($response) . PHP_EOL;
    }
}
