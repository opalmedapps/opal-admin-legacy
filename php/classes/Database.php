<?php

/**
 * Database API class
 *
 */
class Database {

	/**
	 *
	 * Connects to a source database
	 * 
	 * @param int $sourceDBSer : the serial number of the source database
	 * @return $db_link : either null or the database link
	 */
	public function connectToSourceDatabase ($sourceDBSer) {
		$db_link = null;

		if ($this->sourceDatabaseIsEnabled($sourceDBSer)) {

			$creds = $this->getSourceCredentials($sourceDBSer);

			$db_link = new PDO( $creds['dsn'], $creds['username'], $creds['password'] );
 			$db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		}

		return $db_link;
	}

	/**
	 *
	 * Checks whether a source database is enabled for use
	 *
	 * @param int $sourceDBSer : the serial number of the source database
	 * @return bool $enabled : either on or off
	 */
	public function sourceDatabaseIsEnabled ($sourceDBSer) {
		$enabled = false;

		$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
        $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		$sql = "SELECT sdb.Enabled FROM SourceDatabase sdb WHERE sdb.SourceDatabaseSerNum = $sourceDBSer";

		$query = $host_db_link->prepare( $sql );
		$query->execute();

		while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

			if ($data[0] == 1) {
				$enabled = true;
			}
		}

		return $enabled;
	}

	/**
	 *
	 * Fetches/Sets the source database credentials
	 *
	 * @param int $sourceDBSer : the serial number of the source database
	 * @return array $credentials : source database credentials
	 */
	public function getSourceCredentials($sourceDBSer) {
		$credentials = array(
			'dsn' 		=> null,
			'username'	=> null,
			'password' 	=> null
		);

		if (!$sourceDBSer) {return $credentials;}

		# ARIA
		if ($sourceDBSer == 1) {

			$credentials['dsn']			= ARIA_DB_DSN;
			$credentials['username'] 	= ARIA_DB_USERNAME;
			$credentials['password'] 	= ARIA_DB_PASSWORD;

		}

		# WaitRoomManagement
		if ($sourceDBSer == 2) {

			$credentials['dsn']			= WRM_DB_DSN;
			$credentials['username'] 	= WRM_DB_USERNAME;
			$credentials['password'] 	= WRM_DB_PASSWORD;

		}

		# Mosaiq
		if ($sourceDBSer == 3) {

			$credentials['dsn']			= MOSAIQ_DB_DSN;
			$credentials['username'] 	= MOSAIQ_DB_USERNAME;
			$credentials['password'] 	= MOSAIQ_DB_PASSWORD;

		}

		# Others
		# ...

		return $credentials;
	}
}