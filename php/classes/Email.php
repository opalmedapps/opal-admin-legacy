<?php

/**
 * Email API class
 * 
 */
 class Email {

	/**
	*
	* Gets a list of existing email templates
	*
	* @return array $emailList : list of existing email templates
	*/
	public function getExistingEmails() {
		$emailList = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT
					ec.EmailControlSerNum,
					ec.Name_EN,
					ec.Name_FR,
					ec.Body_EN,
					ec.Body_FR,
					et.EmailTypeId
				FROM
					EmailControl ec,
					EmailType et
				WHERE
					ec.EmailTypeSerNum = et.EmailTypeSerNum
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$serial 		= $data[0];
				$name_EN 		= $data[1];
				$name_FR 		= $data[2];
				$body_EN 		= $data[3];
				$body_FR 		= $data[4];
				$type 			= $data[5];

				$emailArray = array(
					'serial'		=> $serial,
					'name_EN'		=> $name_EN,
					'name_FR' 		=> $name_FR,
					'body_EN'		=> $body_EN,
					'body_FR'		=> $body_FR,
					'type'			=> $type
				);

				array_push($emailList, $emailArray);
			}

			return $emailList;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $notificationList;
		}
	}

	/**
	*
	* Get details of a particular email template
	*
	* @param integer $serial : the email control serial number
	* @return array $emailDetails : details of an email
	*/
	public function getEmailDetails ($serial) {
		$emailDetails = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					ec.Name_EN,
					ec.Name_FR,
					ec.Body_EN,
					ec.Body_FR,
					ec.EmailTypeSerNum
				FROM
					EmailControl ec
				WHERE
					ec.EmailControlSerNum = $serial
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$name_EN 		= $data[0];
			$name_FR 		= $data[1];
			$body_EN 		= $data[2];
			$body_FR 		= $data[3];
			$type 			= $data[4];

			$emailDetails = array(
				'serial' 		=> $serial,
				'name_EN'		=> $name_EN,
				'name_FR'		=> $name_FR,
				'body_EN'		=> $body_EN,
				'body_FR'		=> $body_FR,
				'type'			=> $type
			);

			return $emailDetails;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $notificationDetails;
		}
	}

	/**
	*
	* Gets the types of email templates from the database
	*
	* @return array $types : the types of email
	*/ 
	public function getEmailTypes () {
		$types = array();
		try {
		$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
		$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$sql = "
			SELECT DISTINCT 
				et.EmailTypeSerNum,
				et.EmailTypeName,
				et.EmailTypeId
			FROM
				EmailType et
			LEFT JOIN EmailControl ec
			ON ec.EmailTypeSerNum = et.EmailTypeSerNum
			WHERE
				ec.EmailTypeSerNum IS NULL
		";
		$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$query->execute();

		while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
			$typeArray = array(
				'serial'	=> $data[0],
				'name'		=> $data[1],
				'id'		=> $data[2]
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
	* Inserts an email template into the database
	*
	* @param array $emailDetails : the email details
	*/
	public function insertEmail($emailDetails){

		$name_EN 			= $emailDetails['name_EN'];
		$name_FR 			= $emailDetails['name_FR'];
		$body_EN 			= $emailDetails['body_EN'];
		$body_FR 			= $emailDetails['body_FR'];
		$type 			= $emailDetails['type'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
					EmailControl (
						Name_EN,
						Name_FR,
						Body_EN,
						Body_FR,
						EmailTypeSerNum,
						DateAdded
					)
				VALUES (
					\"$name_EN\",
					\"$name_FR\",
					\"$body_EN\",
					\"$body_FR\",
					'$type',
					NOW()
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
	* Updates the email template in the database
	*
	* @param array $emailDetails : the email template details
	* @return array $response : response
	*/
	public function updateEmail ($emailDetails) {

		$name_EN 		= $emailDetails['name_EN'];
		$name_FR 		= $emailDetails['name_FR'];
		$body_EN 		= $emailDetails['body_EN'];
		$body_FR 		= $emailDetails['body_FR'];
		$serial 		= $emailDetails['serial'];

		$response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				UPDATE
					EmailControl
				SET
					EmailControl.Name_EN 			= \"$name_EN\",
					EmailControl.Name_FR 			= \"$name_FR\",
					EmailControl.Body_EN 			= \"$body_EN\",
					EmailControl.Body_FR 		 	= \"$body_FR\"
				WHERE
					EmailControl.EmailControlSerNum = $serial
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
	* Removes an email template from the database 
	*
	* @param integer $serial : the email control serial number
	* @return array $response : response
	*/
	public function removeEmail ($serial) {

		$response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				DELETE FROM
					EmailControl
				WHERE
					EmailControl.EmailControlSerNum = $serial 
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