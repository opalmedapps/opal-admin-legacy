<?php

/**
 *   Application class
 *
 */
class Application {

	/**
	 *
	 * Gets the application version and build type
	 *
	 * @return array $build : the application build
	 */
	public function getApplicationBuild () {
		$build = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					bt.Name
				FROM
					BuildType bt
				LIMIT 1
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$buildName = $data[0];

			// $versionFile = fopen("../../VERSION", "r")
			// 	or die("Unable to open VERSION file!");

			// $version = fgets($versionFile);
			// fclose($versionFile);

			$version = shell_exec('git describe');
			$branch = shell_exec('git rev-parse --abbrev-ref HEAD');

			$build = array(
				'version'		=> $version,
				'environment'	=> $buildName,
				'branch'		=> $branch
			);

			return $build;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $build;
		}

	}
	/**
	 *
	 * Gets the source databases used for enabled flags
	 *
	 * @return array sourceDatabases : the source databases
	 */
	public function getSourceDatabases () {
		$sourceDatabases = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
      $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					sd.SourceDatabaseSerNum,
					sd.SourceDatabaseName,
					sd.Enabled
				FROM
					SourceDatabase sd
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	    $query->execute();

      while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$serial = $data[0];
				$name 	= $data[1];
				$enabled	= $data[2];

				if ($name == 'Aria') {
					$name = 'aria';
				} else if ($name == 'MediVisit') {
					$name = 'wrm';
				} else if ($name == 'Mosaiq') {
					$name = 'mosaiq';
				}

				$sourceDatabases[$name] = array(
					'serial' 	=> $serial,
					'enabled'	=> $enabled,
					'update'	=> 0
				);
			}

			return $sourceDatabases;

		} catch (PDOException $e) {
			echo $e->getMessage();
			return $sourceDatabases;
		}
	}

		/**
	     *
	     * Updates source datbase enabled flag details in the database
	     *
	     * @param array $sourceDatabaseDetails : the source database details
	     * @return array : response
	     */

		public function updateSourceDatabases ($sourceDatabaseDetails) {

			$response = array(
							'value'     => 0,
							'message'   => ''
					);
			try {
				$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
				$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

				foreach ($sourceDatabaseDetails as $sourceDatabase => $details) {
					$enabledFlag = $details['enabled'];
					$serial = $details['serial'];
					$sql = "
						UPDATE
							SourceDatabase
						SET
							SourceDatabase.Enabled = '$enabledFlag'
						WHERE
							SourceDatabase.SourceDatabaseSerNum = $serial
					";
					print "$sql";
					$query = $host_db_link->prepare( $sql );
					$query->execute();
				}
				$response['value'] = 1; // Success

				return $response;
		} catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}
}
