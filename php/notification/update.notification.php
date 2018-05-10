<?php

	/* To update a notification */
    include_once('notification.inc');

    // Construct array from FROM params
    $notification = array(
        'name_EN'               => $_POST['name_EN'],
        'name_FR'               => $_POST['name_FR'],
        'description_EN'        => $_POST['description_EN'],
        'description_FR'        => $_POST['description_FR'],
        'type'                  => $_POST['type'],
        'serial'                => $_POST['serial'],
        'user'                  => $_POST['user']
    );

    $notificationObj = new Notification; // Object

    // Call function
    $response = $notificationObj->updateNotification($notification);
    print json_encode($response); // Return response
?>
