<?php

/**
 * Install API Class
 *
 */
class Install {

	public $salt = "Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8lqu1ElzHv0Ri7EM6irpx5w";

	/**
	 *
	 * Verifies requirements for the installation process
	 *
	 * @param string $abspath : the absolute path of this project
	 * @return array $response : various return logic
	 */
	public function verifyRequirements ($abspath) {
		$response = array (
			'config_file' 	=> array (
				'php' 	=> 0,
				'js' 	=> 0,
				'perl'	=> 0
			)
		);

		// PHP
		if (file_exists($abspath . 'php/config.php')) {
			$response['config_file']['php'] = 1;
		}
		// JS
		if (file_exists($abspath . 'js/config.js')) {
			$response['config_file']['js'] = 1;
		}
		// Perl
		if (file_exists($abspath . 'publisher/modules/Configs.pm')) {
			$response['config_file']['perl'] = 1;
		}

		return $response;
	}

	/**
	 *
	 * Checks opal database connection. If success, create database
	 *
	 * @param array $opalCreds : database credentials for configuration
	 * @return array $response : response
	 */
	public function checkOpalConnection($opalCreds) {

		$response = array (
			'value' 	=> 0,
			'error'		=> ''
		);

		$host 			= $opalCreds['host'];
		$port			= $opalCreds['port'];
		$name 			= $opalCreds['name'];
		$username		= $opalCreds['username'];
		$password 		= $opalCreds['password'];

		try {
			$opal_link = new PDO( "mysql:host=$host;port=$port", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
			// If we're here, connection's good
			$sql = "CREATE DATABASE IF NOT EXISTS $name";

			$query = $opal_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$pathname 	= getcwd();
			$abspath 	= str_replace('php/install', '', $pathname);

			$importFilename = $abspath . 'migration/0001-migration.sql';

			//$command='/usr/local/mysql/bin/mysql -h' .$host .' -u' .$username .' -p' .$password .' ' .$name .' < ' .$importFilename;
			$command='mysql -h' .$host .' -u' .$username .' -p' .$password .' ' .$name .' < ' .$importFilename;

			exec($command,$output=array(),$worked);
			switch($worked){
			    case 0:
			    	$response['value'] = 1;
			        break;
			    case 1:
				    $response['error'] = "There was an error during import.";
				    $sql = "DROP DATABASE IF EXISTS $name";
				    $query = $opal_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$query->execute();
				    break;
			    case 2:
			    	$response['error'] = "There was an error during import.";
			    	$sql = "DROP DATABASE IF EXISTS $name";
				    $query = $opal_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$query->execute();
			    	break;
			 }

			return $response;

		} catch (PDOException $e) {
			$response['error'] = $e->getMessage();
			return $response;
		}
	}

	/**
	 *
	 * Checks ARIA database connection.
	 *
	 * @param array $ariaCreds : database credentials for configuration
	 * @return array $response : response
	 */
	public function checkAriaConnection($ariaCreds) {

		$response = array (
			'value' 	=> 0,
			'error'		=> ''
		);

		$host 			= $ariaCreds['host'];
		$port 			= $ariaCreds['port'];
		$document_path 	= $ariaCreds['document_path'];
		$username		= $ariaCreds['username'];
		$password 		= $ariaCreds['password'];

		try {
			$aria_link = new PDO( "dblib:host=$host:$port\\database", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
			
			// If we're here, connection's good
			if ($document_path != '') {
				if (!file_exists($document_path)) {
					$response['error'] = "The document path '$document_path' does not exist.";
					return $response;
				}
			}
			$response['value'] = 1;
			return $response;

		} catch (PDOException $e) {
			$response['error'] = $e->getMessage();
			return $response;
		}
	}

	/**
	 *
	 * Checks MediVisit database connection.
	 *
	 * @param array $mediVisitCreds : database credentials for configuration
	 * @return array $response : response
	 */
	public function checkMediVisitConnection($mediVisitCreds) {

		$response = array (
			'value' 	=> 0,
			'error'		=> ''
		);

		$host 			= $mediVisitCreds['host'];
		$port			= $mediVisitCreds['port'];
		$name 			= $mediVisitCreds['name'];
		$username		= $mediVisitCreds['username'];
		$password 		= $mediVisitCreds['password'];

		try {
			$medivisit_link = new PDO( "mysql:host=$host;port=$port;dbname=$name", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
			// If we're here, connection's good
			$response['value'] = 1;
			return $response;

		} catch (PDOException $e) {
			$response['error'] = $e->getMessage();
			return $response;
		}
	}

	/**
	 *
	 * Checks MOSAIQ database connection.
	 *
	 * @param array $mosaiqCreds : database credentials for configuration
	 * @return array $response : response
	 */
	public function checkMosaiqConnection($mosaiqCreds) {

		$response = array (
			'value' 	=> 0,
			'error'		=> ''
		);

		$host 			= $ariaCreds['host'];
		$port 			= $ariaCreds['port'];
		$document_path 	= $ariaCreds['document_path'];
		$username		= $ariaCreds['username'];
		$password 		= $ariaCreds['password'];

		try {
			//$mosaiq_link = new PDO( "dblib:host=$host:$port\\database", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );

			// If we're here, connection's good
			if ($document_path != '') {
				if (!file_exists($document_path)) {
					$response['error'] = "The document path '$document_path' does not exist.";
					return $response;
				}
			}
			$response['value'] = 1;
			return $response;

		} catch (PDOException $e) {
			$response['error'] = $e->getMessage();
			return $response;
		}
	}

	/**
	 *
	 * Write configurations to all config files.
	 *
	 * @param array $configs : host and clinical database configurations
	 * @return array $response : response
	 */
	public function writeConfigurations($configs) {
		$response = array (
			'value' 	=> 0,
			'error'		=> ''
		);

		$pathname 	= getcwd();
		$abspath 	= str_replace('php/install', '', $pathname);
		$urlpath 	= $configs['urlpath'];

		$opalCreds = $configs['opal'];

		$response = $this->checkOpalConnection($opalCreds);
		if ( $response['value'] == 0 ) {

			return $response;
		}
		else {
			// PHP
			$path_to_file = $abspath . 'php/config.php';
			$file_contents = file_get_contents($path_to_file);
			$file_contents = str_replace('OPAL_DB_HOST_HERE', $opalCreds['host'], $file_contents);
			$file_contents = str_replace('OPAL_DB_PORT_HERE', $opalCreds['port'], $file_contents);
			$file_contents = str_replace('OPAL_DB_NAME_HERE', $opalCreds['name'], $file_contents);
			$file_contents = str_replace('OPAL_DB_USERNAME_HERE', $opalCreds['username'], $file_contents);
			$file_contents = str_replace('OPAL_DB_PASSWORD_HERE', $opalCreds['password'], $file_contents);
			file_put_contents($path_to_file, $file_contents);

			// Perl
			$path_to_file = $abspath . 'publisher/modules/Configs.pm';
			$file_contents = file_get_contents($path_to_file);
			$file_contents = str_replace('OPAL_DB_HOST_HERE', $opalCreds['host'], $file_contents);
			$file_contents = str_replace('OPAL_DB_PORT_HERE', $opalCreds['port'], $file_contents);
			$file_contents = str_replace('OPAL_DB_NAME_HERE', $opalCreds['name'], $file_contents);
			$file_contents = str_replace('OPAL_DB_USERNAME_HERE', $opalCreds['username'], $file_contents);
			$file_contents = str_replace('OPAL_DB_PASSWORD_HERE', $opalCreds['password'], $file_contents);
			file_put_contents($path_to_file, $file_contents);

			// Create local clinical documents directory
			if (!is_dir($abspath . 'publisher/clinical/documents')) {
				if(!mkdir($abspath . 'publisher/clinical/documents/', 0755, true))
					die('Failed to create folder ' . $abspath . 'publisher/clinical/documents/');
			}

		}

		$ariaCreds = $configs['clinical']['aria'];
		if ($ariaCreds['status'] == 'true') {
			$response = $this->checkAriaConnection($ariaCreds);
			if ($response['value'] == 0) {
				return $response;
			}
			else {
				// PHP
				$path_to_file = $abspath . 'php/config.php';
				$file_contents = file_get_contents($path_to_file);
				$file_contents = str_replace('ARIA_DB_HOST_HERE', $ariaCreds['host'], $file_contents);
				$file_contents = str_replace('ARIA_DB_PORT_HERE', $ariaCreds['port'], $file_contents);
				$file_contents = str_replace('ARIA_DB_USERNAME_HERE', $ariaCreds['username'], $file_contents);
				$file_contents = str_replace('ARIA_DB_PASSWORD_HERE', $ariaCreds['password'], $file_contents);
				file_put_contents($path_to_file, $file_contents);

				// Perl
				$path_to_file = $abspath . 'publisher/modules/Configs.pm';
				$file_contents = file_get_contents($path_to_file);
				$file_contents = str_replace('ARIA_DB_HOST_HERE', $ariaCreds['host'], $file_contents);
				$file_contents = str_replace('ARIA_DB_PORT_HERE', $ariaCreds['port'], $file_contents);
				$file_contents = str_replace('ARIA_DB_USERNAME_HERE', $ariaCreds['username'], $file_contents);
				$file_contents = str_replace('ARIA_DB_PASSWORD_HERE', $ariaCreds['password'], $file_contents);
				$file_contents = str_replace('ARIA_FTP_DIR_HERE', $ariaCreds['document_path'], $file_contents);
				file_put_contents($path_to_file, $file_contents);

				// Enable database
				$opal_link = new PDO( "mysql:host=".$opalCreds['host'].";port=".$opalCreds['port'].";dbname=".$opalCreds['name'], $opalCreds['username'], $opalCreds['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );

				$sql = "UPDATE SourceDatabase SET Enabled = 1 WHERE SourceDatabaseSerNum = 1";

				$query = $opal_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$query->execute();


			}
		}

		$mediVisitCreds = $configs['clinical']['medivisit'];
		if ($mediVisitCreds['status'] == 'true') {
			$response = $this->checkMediVisitConnection($mediVisitCreds);
			if ($response['value'] == 0) {
				return $response;
			}
			else {
				// PHP
				$path_to_file = $abspath . 'php/config.php';
				$file_contents = file_get_contents($path_to_file);
				$file_contents = str_replace('WRM_DB_HOST_HERE', $mediVisitCreds['host'], $file_contents);
				$file_contents = str_replace('WRM_DB_PORT_HERE', $mediVisitCreds['port'], $file_contents);
				$file_contents = str_replace('WRM_DB_NAME_HERE', $mediVisitCreds['name'], $file_contents);
				$file_contents = str_replace('WRM_DB_USERNAME_HERE', $mediVisitCreds['username'], $file_contents);
				$file_contents = str_replace('WRM_DB_PASSWORD_HERE', $mediVisitCreds['password'], $file_contents);
				file_put_contents($path_to_file, $file_contents);

				// Perl
				$path_to_file = $abspath . 'publisher/modules/Configs.pm';
				$file_contents = file_get_contents($path_to_file);
				$file_contents = str_replace('WRM_DB_HOST_HERE', $mediVisitCreds['host'], $file_contents);
				$file_contents = str_replace('WRM_DB_PORT_HERE', $mediVisitCreds['port'], $file_contents);
				$file_contents = str_replace('WRM_DB_NAME_HERE', $mediVisitCreds['name'], $file_contents);
				$file_contents = str_replace('WRM_DB_USERNAME_HERE', $mediVisitCreds['username'], $file_contents);
				$file_contents = str_replace('WRM_DB_PASSWORD_HERE', $mediVisitCreds['password'], $file_contents);
				file_put_contents($path_to_file, $file_contents);

				// Enable database
				$opal_link = new PDO( "mysql:host=".$opalCreds['host'].";port=".$opalCreds['port'].";dbname=".$opalCreds['name'], $opalCreds['username'], $opalCreds['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );

				$sql = "UPDATE SourceDatabase SET Enabled = 1 WHERE SourceDatabaseSerNum = 2";

				$query = $opal_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$query->execute();

			}
		}

		$mosaiqCreds = $configs['clinical']['mosaiq'];
		if ($mosaiqCreds['status'] == 'true') {
			$response = $this->checkMosaiqConnection($mosaiqCreds);
			if ($response['value'] == 0) {
				return $response;
			}

			else {

				// PHP
				$path_to_file = $abspath . 'php/config.php';
				$file_contents = file_get_contents($path_to_file);
				$file_contents = str_replace('MOSAIQ_DB_HOST_HERE', $mosaiqCreds['host'], $file_contents);
				$file_contents = str_replace('MOSAIQ_DB_PORT_HERE', $mosaiqCreds['port'], $file_contents);
				$file_contents = str_replace('MOSAIQ_DB_USERNAME_HERE', $mosaiqCreds['username'], $file_contents);
				$file_contents = str_replace('MOSAIQ_DB_PASSWORD_HERE', $mosaiqCreds['password'], $file_contents);
				file_put_contents($path_to_file, $file_contents);

				// Perl
				$path_to_file = $abspath . 'publisher/modules/Configs.pm';
				$file_contents = file_get_contents($path_to_file);
				$file_contents = str_replace('MOSAIQ_DB_HOST_HERE', $mosaiqCreds['host'], $file_contents);
				$file_contents = str_replace('MOSAIQ_DB_PORT_HERE', $mosaiqCreds['port'], $file_contents);
				$file_contents = str_replace('MOSAIQ_DB_USERNAME_HERE', $mosaiqCreds['username'], $file_contents);
				$file_contents = str_replace('MOSAIQ_DB_PASSWORD_HERE', $mosaiqCreds['password'], $file_contents);
				$file_contents = str_replace('MOSAIQ_FTP_DIR_HERE', $mosaiqCreds['document_path'], $file_contents);

				file_put_contents($path_to_file, $file_contents);

				// Enable database
				$opal_link = new PDO( "mysql:host=".$opalCreds['host'].";port=".$opalCreds['port'].";dbname=".$opalCreds['name'], $opalCreds['username'], $opalCreds['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );

				$sql = "UPDATE SourceDatabase SET Enabled = 1 WHERE SourceDatabaseSerNum = 3";

				$query = $opal_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$query->execute();

			}
		}

		// Javascript
		$path_to_file = $abspath . 'js/config.js';
		$file_contents = file_get_contents($path_to_file);
		$file_contents = str_replace('ABSPATH_HERE', $abspath, $file_contents);
		$file_contents = str_replace('URLPATH_HERE', $urlpath, $file_contents);
		file_put_contents($path_to_file, $file_contents);

		// PHP
		$path_to_file = $abspath . 'php/config.php';
		$file_contents = file_get_contents($path_to_file);
		$file_contents = str_replace('FRONTEND_ABS_PATH_HERE', $abspath, $file_contents);
		$file_contents = str_replace('FRONTEND_REL_URL_HERE', $urlpath, $file_contents);
		file_put_contents($path_to_file, $file_contents);

		// Perl
		$path_to_file = $abspath . 'publisher/modules/Configs.pm';
		$file_contents = file_get_contents($path_to_file);
		$file_contents = str_replace('FRONTEND_ABS_PATH_HERE', $abspath, $file_contents);
		$file_contents = str_replace('FRONTEND_REL_URL_HERE', $urlpath, $file_contents);
		file_put_contents($path_to_file, $file_contents);



		return $response;

	}

	/**
	 *
	 * Registers an admin user into the database
	 *
	 * @param array $adminDetails : the admin details
	 * @return array $response : response
	 */
	public function registerAdminUser($adminDetails) {

		$response = array (
			'value' 	=> 0,
			'error'		=> ''
		);

		$username 		= $adminDetails['username'];
		$password 		= $adminDetails['password'];
		$roleSer 		= 1; // admin
		try {
			$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "INSERT INTO OAUser(Username, Password, DateAdded) VALUES(:username, :password, NOW())";

			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $password . $this->salt), PDO::PARAM_STR );
			$stmt->execute();

			$userSer = $con->lastInsertId();

			$sql = "INSERT INTO OAUserRole(OAUserSerNum, RoleSerNum) VALUES('$userSer','$roleSer')";
			$query = $con->prepare($sql);
			$query->execute();

			$response['value'] = 1;
			return $response;

		}catch( PDOException $e ) {
			$response['error'] = $e->getMessage();
			return $response;
		}
	}

	/**
	 *
	 * String intersection of two URLs
	 *
	 * @param string $str1 : First string
	 * @param string $str2 : Second string
	 * @param bool $trailing_slash : Whether or not to add trailing slash
	 * @return string $result : Intersection
	 */
	private function stringIntersectURLs ( $str1, $str2, $trailing_slash = false ) {

		$result = '';
		$strArray1 = explode('/', $str1);
		$strArray2 = explode('/', $str2);
		$result = array_intersect($strArray1, $strArray2);

		$result = implode('/', $result);

		if ($trailing_slash) {
			$result .= '/';
		}

		return $result;
	}
}

?>
