<?php
include(FRONTEND_ABS_PATH.'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'phpqrcode' . DIRECTORY_SEPARATOR . 'qrlib.php');

/**
 * HospitalMap class
 *
 */
class HospitalMap extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_HOSPITAL_MAP, $guestStatus);
    }

    /**
     *
     * Generates a QRCode
     *
     * @param string $qrid : the string to QR-ify
     * @param string $oldqrid : the previous string that was QR'ed
     * @return array : qrcode with path
     */
    public function generateQRCode($qrid, $oldqrid) {
        $this->checkWriteAccess();

        if($oldqrid) {
            $oldQRPath = FRONTEND_ABS_PATH.'images' . DIRECTORY_SEPARATOR . 'hospital-maps' . DIRECTORY_SEPARATOR . 'qrCodes' . DIRECTORY_SEPARATOR .$oldqrid.'.png';
            if(file_exists($oldQRPath)) {
                unlink($oldQRPath);
            }
        }
        $qrPath = FRONTEND_ABS_PATH.'images' . DIRECTORY_SEPARATOR . 'hospital-maps' . DIRECTORY_SEPARATOR . 'qrCodes' . DIRECTORY_SEPARATOR .$qrid.'.png';
        $qrCode = '';

        if(!file_exists($qrPath)) {
            QRcode::png($qrid,$qrPath);
        }
        $type = pathinfo($qrPath, PATHINFO_EXTENSION);
        $data = file_get_contents($qrPath);
        $qrCode = 'data:image/'.$type.';base64,'.base64_encode($data);

        $qrArray = array(
            'qrcode'    => $qrCode,
            'qrpath'    => $qrPath
        );
        return $qrArray;
    }

    /**
     *
     * Inserts hospital map info
     *
     * @param array $hosMapDetails : the hospital map details
	 * @return void
     */
    public function insertHospitalMap ($hosMapDetails) {
        $this->checkWriteAccess();

        $name_EN            = $hosMapDetails['name_EN'];
        $name_FR            = $hosMapDetails['name_FR'];
        $description_EN     = $hosMapDetails['description_EN'];
        $description_FR     = $hosMapDetails['description_FR'];
        $url_EN             = $hosMapDetails['url_EN'];
        $url_FR             = $hosMapDetails['url_FR'];
        $qrid               = $hosMapDetails['qrid'];
        $userSer            = $hosMapDetails['user']['id'];
        $sessionId          = $hosMapDetails['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $qrPath = 'qrCodes/'.$qrid.'.png';
            $sql = "
                INSERT INTO
                    HospitalMap (
                        MapUrl,
                        MapURL_EN,
                        MapURL_FR,
                        QRMapAlias,
                        QRImageFileName,
                        MapName_EN,
                        MapDescription_EN,
                        MapName_FR,
                        MapDescription_FR,
                        DateAdded,
                        LastUpdatedBy,
                        SessionId
                    )
                VALUES (
                    \"$url_EN\",
                    \"$url_EN\",
                    \"$url_FR\",
                    \"$qrid\",
                    \"$qrPath\",
                    \"$name_EN\",
                    \"$description_EN\",
                    \"$name_FR\",
                    \"$description_FR\",
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
     * Gets a list of existing hospital maps
     *
     * @return array $hosMapList : the list of existing hospital maps
     */
    public function getHospitalMaps() {
        $this->checkReadAccess();
        $hosMapList = array();
 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    hm.HospitalMapSerNum,
                    hm.MapURL_EN,
                    hm.MapURL_FR,
                    hm.QRMapAlias,
                    hm.MapName_EN,
                    hm.MapDescription_EN,
                    hm.MapName_FR,
                    hm.MapDescription_FR
                FROM
                    HospitalMap hm
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $serial             = $data[0];
                $url_EN             = $data[1];
                $url_FR             = $data[2];
                $qrid               = $data[3];
                $name_EN            = $data[4];
                $description_EN     = $data[5];
                $name_FR            = $data[6];
                $description_FR     = $data[7];
                $qr = $this->generateQRCode($qrid, null);
                $qrcode = $qr['qrcode'];
                $qrpath = $qr['qrpath'];

                $hosMapArray = array(
                    'name_EN'           => $name_EN,
                    'name_FR'           => $name_FR,
                    'description_EN'    => $description_EN,
                    'description_FR'    => $description_FR,
                    'url_EN'            => $url_EN,
                    'url_FR'            => $url_FR,
                    'qrid'              => $qrid,
                    'qrcode'            => $qrcode,
                    'qrpath'            => $qrpath,
                    'serial'            => $serial
                );

                array_push($hosMapList, $hosMapArray);
            }

            return $hosMapList;
	    } catch (PDOException $e) {
			echo $e->getMessage();
			return $hosMapList;
		}
	}

    /*
     * Gets details on a particular hospital map
     */
    public function getHospitalMapDetails($serial) {
        $this->checkReadAccess();
        $hosMapDetails = $this->opalDB->getHospitalMapDetails(intval($serial));
        $qr = $this->generateQRCode($hosMapDetails['qrid'], null);
        $hosMapDetails['qrcode'] = $qr['qrcode'];
        $hosMapDetails['qrpath'] = $qr['qrpath'];

        return $hosMapDetails;
	}

    /**
     *
     * Updates hospital map's details
     *
     * @param array $hosMapDetails : the hospital map details
	 * @return void
     */
    public function updateHospitalMap ($hosMapDetails) {
        $this->checkWriteAccess();

        $name_EN            = $hosMapDetails['name_EN'];
        $name_FR            = $hosMapDetails['name_FR'];
        $description_EN     = $hosMapDetails['description_EN'];
        $description_FR     = $hosMapDetails['description_FR'];
        $url_EN             = $hosMapDetails['url_EN'];
        $url_FR             = $hosMapDetails['url_FR'];
        $qrid               = $hosMapDetails['qrid'];
        $serial             = $hosMapDetails['serial'];
        $userSer            = $hosMapDetails['user']['id'];
        $sessionId          = $hosMapDetails['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $qrPath = 'qrCodes/'.$qrid.'.png';
            $sql = "
                UPDATE
                    HospitalMap
                SET
                    HospitalMap.MapURL_EN           = \"$url_EN\",
                    HospitalMap.MapURL_FR           = \"$url_FR\",
                    HospitalMap.QRMapAlias          = \"$qrid\",
                    HospitalMap.QRImageFileName     = \"$qrPath\",
                    HospitalMap.MapName_EN          = \"$name_EN\",
                    HospitalMap.MapDescription_EN   = \"$description_EN\",
                    HospitalMap.MapName_FR          = \"$name_FR\",
                    HospitalMap.MapDescription_FR   = \"$description_FR\",
                    HospitalMap.LastUpdatedBy       = '$userSer',
                    HospitalMap.SessionId           = '$sessionId'
                WHERE
                    HospitalMap.HospitalMapSerNum   = $serial
            ";

	        $query = $host_db_link->prepare( $sql );
            $query->execute();

	    } catch( PDOException $e) {
			return $e->getMessage();
		}
	}

    /**
     *
     * Deletes a hospital map from the database
     *
     * @param integer $serial : the hospital map serial number
     * @param object $user : the current user in session
	 * @return void
     */
    public function deleteHospitalMap ($serial, $user) {
        $this->checkDeleteAccess();
        $userSer = $user['id'];
        $sessionId = $user['sessionid'];
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    HospitalMap
                WHERE
                    HospitalMap.HospitalMapSerNum = $serial
            ";

	        $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                UPDATE HospitalMapMH
                SET
                    HospitalMapMH.LastUpdatedBy = '$userSer',
                    HospitalMapMH.SessionId = '$sessionId'
                WHERE
                    HospitalMapMH.HospitalMapSerNum = $serial
                ORDER BY HospitalMapMH.RevSerNum DESC
                LIMIT 1
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

        } catch( PDOException $e) {
			return $e->getMessage();
		}
	}
}

?>
