<?php

	/* To insert a newly created notification */
    include_once('notification.inc');

    // Construct array from FORM params
    $notification = array(
        'name_EN'               => $_POST['name_EN'],
        'name_FR'               => $_POST['name_FR'],
        'description_EN'        => $_POST['description_EN'],
        'description_FR'        => $_POST['description_FR'],
        'type'                  => $_POST['type'],
        'user'                  => $_POST['user']
    );

    $notificationObj = new Notification; // Object

    // Call function
    print $notificationObj->insertNotification($notification);
?>
