<?php

	/* To insert a newly created notification */

    // Construct array
    $notification = array(
        'name_EN'               => $_POST['name_EN'],
        'name_FR'               => $_POST['name_FR'],
        'description_EN'        => $_POST['description_EN'],
        'description_FR'        => $_POST['description_FR'],
        'type'                  => $_POST['type']
    );

    $notificationObj = new Notification; // Object

    // Call function
    $notificationObj->insertNotification($notification);
?>
