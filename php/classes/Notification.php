<?php

/**
 * Notification class
 *
 */
class Notification extends Module {
    private $host_db_link;

    public function __construct($guestStatus = false) {
        // Setup class-wide database connection with or without SSL
        if(USE_SSL == 1){
            $this->$host_db_link = new PDO(
                OPAL_DB_DSN,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::MYSQL_ATTR_SSL_CA => SSL_CA,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                )
            );
        }else{
            $this->$host_db_link = new PDO(
                OPAL_DB_DSN,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
        }

        parent::__construct(MODULE_NOTIFICATION, $guestStatus);
    }

    /**
     *
     * Gets a list of existing notifications
     *
     * @return array $notificationList : the list of existing notifications
     */
    public function getNotifications() {
        $this->checkReadAccess();
        $notificationList = array();
        try {
            $this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    nt.NotificationControlSerNum,
                    nt.Name_EN,
                    nt.Name_FR,
                    nt.Description_EN,
                    nt.Description_FR,
                    ntt.NotificationTypeId
                FROM
                    NotificationControl nt,
                    NotificationTypes ntt
                WHERE
                    nt.NotificationTypeSerNum   = ntt.NotificationTypeSerNum
            ";
		    $query = $this->$host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
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
        $this->checkReadAccess($serial);
        $notificationDetails = array();
        try {
            $this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    nt.Name_EN,
                    nt.Name_FR,
                    nt.Description_EN,
                    nt.Description_FR,
                    ntt.NotificationTypeId
                FROM
                    NotificationControl nt,
                    NotificationTypes ntt
                WHERE
                    nt.NotificationControlSerNum    = $serial
                AND ntt.NotificationTypeSerNum      = nt.NotificationTypeSerNum
            ";

	        $query = $this->$host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
		}
	}

    /**
     *
     * Gets the types of notifications from the database
     *
     * @return array $types : the notification types
     */
    public function getNotificationTypes () {
        $this->checkReadAccess();
        $types = array();
	    try {
            $this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    ntt.NotificationTypeName,
                    ntt.NotificationTypeId,
                    ntt.NotificationTypeSerNum
                FROM
                    NotificationTypes ntt
                LEFT JOIN NotificationControl nt
                ON nt.NotificationTypeSerNum = ntt.NotificationTypeSerNum
                WHERE
                    nt.NotificationTypeSerNum IS NULL
            ";
		    $query = $this->$host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $typeArray = array(
                    'name'      => $data[0],
                    'id'        => $data[1],
                    'serial'    => $data[2]
                );

                array_push($types, $typeArray);
            }

            return $types;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
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
        $this->checkWriteAccess($notification);

        $name_EN            = $notification['name_EN'];
        $name_FR            = $notification['name_FR'];
        $description_EN     = $notification['description_EN'];
        $description_FR     = $notification['description_FR'];
        $typeSer            = $notification['type']['serial'];
        $userSer            = $notification['user']['id'];
        $sessionId          = $notification['user']['sessionid'];

		try {
            $this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                INSERT INTO
                    NotificationControl (
                        Name_EN,
                        Name_FR,
                        Description_EN,
                        Description_FR,
                        NotificationTypeSerNum,
                        DateAdded,
                        LastUpdatedBy,
                        SessionId
                    )
                VALUES (
                    \"$name_EN\",
                    \"$name_FR\",
                    \"$description_EN\",
                    \"$description_FR\",
                    '$typeSer',
                    NOW(),
                    '$userSer',
                    '$sessionId'
                )
            ";
            $query = $this->$host_db_link->prepare( $sql );
			$query->execute();
        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
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
        $this->checkWriteAccess($notification);

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
            $this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
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

	        $query = $this->$host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

	    } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
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
        $this->checkDeleteAccess(array($serial, $user));

        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        $userSer    = $user['id'];
        $sessionId  = $user['sessionid'];

        try {
			$this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    NotificationControl
                WHERE
                    NotificationControl.NotificationControlSerNum = $serial
            ";
            $query = $this->$host_db_link->prepare( $sql );
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
            $query = $this->$host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
		}
	}

    /**
     *
     * Gets chart logs of a notification or notifications
     *
     * @param integer $serial : the notification serial number
     * @return array $notificationLogs : the notification logs for highcharts
     */
    public function getNotificationChartLogs ($serial) {
        $this->checkReadAccess($serial);
        $notificationLogs = array();
        try {
            $this->$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = null;
            if (!$serial) {
                $sql = "
                    SELECT DISTINCT 
                        ntmh.CronLogSerNum,
                        COUNT(ntmh.CronLogSerNum),
                        cl.CronDateTime,
                        ntt.NotificationTypeName
                    FROM
                        NotificationMH ntmh,
                        CronLog cl,
                        NotificationControl ntc,
                        NotificationTypes ntt
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = ntmh.CronLogSerNum
                    AND ntmh.CronLogSerNum IS NOT NULL
                    AND ntmh.NotificationControlSerNum = ntc.NotificationControlSerNum
                    AND ntc.NotificationTypeSerNum = ntt.NotificationTypeSerNum
                    GROUP BY 
                        ntmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";
            }
            else {
                $sql = "
                    SELECT DISTINCT 
                        ntmh.CronLogSerNum,
                        COUNT(ntmh.CronLogSerNum),
                        cl.CronDateTime,
                        ntt.NotificationTypeName
                    FROM
                        NotificationMH ntmh,
                        CronLog cl,
                        NotificationControl ntc,
                        NotificationTypes ntt
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = ntmh.CronLogSerNum
                    AND ntmh.CronLogSerNum IS NOT NULL
                    AND ntmh.NotificationControlSerNum = '$serial'
                    AND ntmh.NotificationControlSerNum = ntc.NotificationControlSerNum
                    AND ntc.NotificationTypeSerNum = ntt.NotificationTypeSerNum
                    GROUP BY 
                        ntmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";
            }

            $query = $this->$host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $notificationSeries = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $seriesName = $data[3];
                $notificationDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                if(!isset($notificationSeries[$seriesName])) {
                    $notificationSeries[$seriesName] = array(
                        'name'  => $seriesName,
                        'data'  => array()
                    );
                }
                array_push($notificationSeries[$seriesName]['data'], $notificationDetail);
            }

            foreach ($notificationSeries as $seriesName => $series) {
                array_push($notificationLogs, $series);
            }

            return $notificationLogs;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for notification. " . $e->getMessage());
        }
    }

    /**
     * Gets list logs of notifications during one or many cron sessions
     */
    public function getNotificationListLogs ($notificationIds) {
        $this->checkReadAccess($notificationIds);
        foreach ($notificationIds as &$id) {
            $id = intval($id);
        }
        return $this->opalDB->getNotificationsLogs($notificationIds);
    }
}

?>
