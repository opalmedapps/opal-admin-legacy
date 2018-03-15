<?php

/**
 * Notification class
 *
 */
class Notification {

    /**
     *
     * Gets a list of existing notifications
     *
     * @return array $notificationList : the list of existing notifications
     */
    public function getNotifications() {
        $notificationList = array();
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    nt.NotificationControlSerNum,
                    nt.Name_EN,
                    nt.Name_FR,
                    nt.Description_EN,
                    nt.Description_FR,
                    nt.NotificationType
                FROM
                    NotificationControl nt
            ";
		    $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $serial             = $data[0];
                $name_EN            = $data[1];
                $name_FR            = $data[2];
                $description_EN     = $data[3];
                $description_FR     = $data[4];
                $type               = $data[5];

                $notificationArray = array(
                    'serial'            => $serial,
                    'name_EN'           => $name_EN,
                    'name_FR'           => $name_FR,
                    'description_EN'    => $description_EN,
                    'description_FR'    => $description_FR,
                    'type'              => $type
                );

                array_push($notificationList, $notificationArray);
            }

            return $notificationList;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $notificationList;
		}
	}

    /**
     *
     * Gets details of a particular notification
     *
     * @param integer $serial : the notification serial number
     * @return array $notificationDetails : the notification details
     */
    public function getNotificationDetails ($serial) {
        $notificationDetails = array();
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    nt.Name_EN,
                    nt.Name_FR,
                    nt.Description_EN,
                    nt.Description_FR,
                    nt.NotificationType
                FROM
                    NotificationControl nt
                WHERE
                    nt.NotificationControlSerNum = $serial
            ";

	        $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $name_EN            = $data[0];
            $name_FR            = $data[1];
            $description_EN     = $data[2];
            $description_FR     = $data[3];
            $type               = $data[4];

            $notificationDetails = array(
                'serial'            => $serial,
                'name_EN'           => $name_EN,
                'name_FR'           => $name_FR,
                'description_EN'    => $description_EN,
                'description_FR'    => $description_FR,
                'type'              => $type
            );

            return $notificationDetails;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $notificationDetails;
		}
	}

    /**
     *
     * Gets the types of notifications from the database
     *
     * @return array $types : the notification types
     */
    public function getNotificationTypes () {
        $types = array();
	    try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    ntt.NotificationTypeName,
                    ntt.NotificationTypeId
                FROM
                    NotificationTypes ntt
                LEFT JOIN NotificationControl nt
                ON nt.NotificationType = ntt.NotificationTypeId
                WHERE
                    nt.NotificationType IS NULL
            ";
		    $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $typeArray = array(
                    'name'  => $data[0],
                    'id'    => $data[1]
                );

                array_push($types, $typeArray);
            }

            return $types;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $types;
		}
	}

     /**
     *
     * Inserts a notification into the database
     *
     * @param array $notification : the notification details
	 * @return void
     */
    public function insertNotification($notification) {

        $name_EN            = $notification['name_EN'];
        $name_FR            = $notification['name_FR'];
        $description_EN     = $notification['description_EN'];
        $description_FR     = $notification['description_FR'];
        $type               = $notification['type'];
        $userSer            = $notification['user']['id'];
        $sessionId          = $notification['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                INSERT INTO
                    NotificationControl (
                        Name_EN,
                        Name_FR,
                        Description_EN,
                        Description_FR,
                        NotificationType,
                        DateAdded,
                        LastUpdatedBy,
                        SessionId
                    )
                VALUES (
                    \"$name_EN\",
                    \"$name_FR\",
                    \"$description_EN\",
                    \"$description_FR\",
                    '$type',
                    NOW(),
                    '$userSer',
                    '$sessionId'
                )
            ";
            $query = $host_db_link->prepare( $sql );
			$query->execute();
        } catch( PDOException $e) {
			return $e->getMessage();
		}

    }

    /**
     *
     * Updates the notification in the database
     *
     * @param array $notification : the notification details
     * @return array : response
     */
    public function updateNotification($notification) {

        $name_EN            = $notification['name_EN'];
        $name_FR            = $notification['name_FR'];
        $description_EN     = $notification['description_EN'];
        $description_FR     = $notification['description_FR'];
        $serial             = $notification['serial'];
        $userSer            = $notification['user']['id'];
        $sessionId          = $notification['user']['sessionid'];

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                UPDATE
                    NotificationControl
                SET
                    NotificationControl.Name_EN            = \"$name_EN\",
                    NotificationControl.Name_FR            = \"$name_FR\",
                    NotificationControl.Description_EN     = \"$description_EN\",
                    NotificationControl.Description_FR     = \"$description_FR\",
                    NotificationControl.LastUpdatedBy      = '$userSer',
                    NotificationControl.SessionId          = '$sessionId'
                WHERE
                    NotificationControl.NotificationControlSerNum = $serial
            ";

	        $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

	    } catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response;
		}
	}

    /**
     *
     * Deletes a notification from the database
     *
     * @param integer $serial : the notification serial number
     * @param object $user : the current user in session
     * @return array : response
     */
    public function deleteNotification($serial, $user) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        $userSer    = $user['id'];
        $sessionId  = $user['sessionid'];

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    NotificationControl
                WHERE
                    NotificationControl.NotificationControlSerNum = $serial
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                UPDATE NotificationControlMH
                SET 
                    NotificationControlMH.LastUpdatedBy = '$userSer',
                    NotificationControlMH.SessionId = '$sessionId'
                WHERE
                    NotificationControlMH.NotificationControlSerNum = $serial
                ORDER BY NotificationControlMH.RevSerNum DESC 
                LIMIT 1
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

        } catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response;
		}
	}

}

?>
