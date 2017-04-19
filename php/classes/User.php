<?php

 class Users {
	 public $username = null;
	 public $password = null;
	 public $role = null;
	 public $language = null;
	 public $userid = null;
	 public $salt = "Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8lqu1ElzHv0Ri7EM6irpx5w";
	 
	 public function __construct( $data = array() ) {
		 if( isset( $data->username ) ) $this->username = stripslashes( strip_tags( $data->username ) );
		 if( isset( $data->password ) ) $this->password = stripslashes( strip_tags( $data->password ) );
	 }
	 
	 public function storeFormValues( $params ) {
		//store the parameters
		$this->__construct( $params ); 
	 }
	 
	 public function userLogin() {
		 $success = false;
		 try{
			$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "SELECT * FROM OAUser WHERE OAUser.Username = :username AND OAUser.Password = :password";
			
			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $this->username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $this->password . $this->salt), PDO::PARAM_STR );
			$stmt->execute();
			
			$valid = $stmt->fetchColumn();
			if( $valid ) {
				$this->userid = $valid;
				$userDetails = $this->getUserDetails($valid);
				$this->role = $userDetails['role']['name'];
				$this->language = $userDetails['language'];
				$success = true;
			}
			
			$con = null;
			return $success;
		 }catch (PDOException $e) {
			 echo $e->getMessage();
			 return $success;
		 }
	 }

	public function updatePassword($userArray) {
	 	$response = array (
	 		'value'		=> 0,
	 		'error'		=> array(
	 			'code'		=> '',
	 			'message'	=> ''
	 		)
	 	);
	 	$oldPassword 	= $userArray['oldPassword'];
	 	$userSer		= $userArray['user']['id'];
	 	$newPassword	= $userArray['password'];
	 	try {
	 		$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			if (!isset($userArray['override'])) {
				$sql = "SELECT * FROM OAUser WHERE OAUser.OAUserSerNum = :ser AND OAUser.Password = :password";

				$stmt = $con->prepare( $sql );
				$stmt->bindValue( "ser", $userSer, PDO::PARAM_STR );
				$stmt->bindValue( "password", hash("sha256", $oldPassword . $this->salt), PDO::PARAM_STR );
				$stmt->execute();
				
				$valid = $stmt->fetchColumn();
				if( !$valid ) {
					$response['error']['code'] = 'old-password-incorrect';
					$response['error']['message'] = 'Your old password is incorrect.';
					return $response;
				}
			}

			$sql = "UPDATE OAUser SET OAUser.Password = :password WHERE OAUser.OAUserSerNum = :ser";

			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "ser", $userSer, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $newPassword . $this->salt), PDO::PARAM_STR );
			$stmt->execute();

			$response['value'] = 1; // Success
			return $response;

	 	} catch (PDOException $e) {
			 $response['error']['code'] = 'db-catch';
			 $response['error']['message'] = $e->getMessage();
			 return $response;
		}
	 }
	 
	 public function updateUser($userArray) {
	 	$response = array (
	 		'value'		=> 0,
	 		'error'		=> array(
	 			'code'		=> '',
	 			'message'	=> ''
	 		)
	 	);
	 	$userSer			= $userArray['user']['id'];
	 	$newPassword 		= $userArray['password'];
	 	$confirmPassword 	= $userArray['confirmPassword'];
	 	$roleSer 			= $userArray['role']['serial'];

	 	try {

	 		if ( ($newPassword && $confirmPassword) && ($newPassword == $confirmPassword) ) {
	 			$response = $this->updatePassword($userArray);
	 			if ($response['value'] == 0) {
	 				return $response;
	 			}
	 		}

	 		$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "UPDATE OAUserRole SET OAUserRole.RoleSerNum = $roleSer WHERE OAUserRole.OAUserSerNum = $userSer";

			$stmt = $con->prepare( $sql );
			$stmt->execute();

			$response['value'] = 1; // Success
			return $response;

	 	} catch (PDOException $e) {
			 $response['error']['code'] = 'db-catch';
			 $response['error']['message'] = $e->getMessage();
			 return $response;
		}
	 }
	/**
	 *
	 * Registers a user into the database
	 *
	 * @param array $userArray : the user details
	 * @return void
	 */
	public function registerUser($userArray) {
		$username 		= $userArray['username'];
		$password 		= $userArray['password'];
		$roleSer 		= $userArray['role']['serial'];
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
			return;

		}catch( PDOException $e ) {
			return $e->getMessage();
		}
	}

	public function getUsers() {
	 	$users = array();
	 	try {
	 		$connect = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
	 		$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	 		$sql = "
		 		SELECT DISTINCT
			 		OAUser.OAUserSerNum,
			 		OAUser.Username,
			 		Role.RoleName,
			 		OAUser.Language
		 		FROM
			 		OAUser,
			 		OAUserRole,
			 		Role
		 		WHERE
		 			OAUser.OAUserSerNum 	= OAUserRole.OAUserSerNum
		 		AND OAUserRole.RoleSerNum	= Role.RoleSerNum
	 		";           
	 		$query = $connect->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	 		$query->execute();

	 		while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

	 			$serial 	= $data[0];
	 			$name   	= $data[1];
	 			$role 		= $data[2];
	 			$language 	= $data[3];

	 			$userArray = array(
	 				'serial'    	=> $serial,
	 				'username'      => $name,
	 				'role'			=> $role,
	 				'language' 		=> $language
	 				);
	 			array_push($users, $userArray);
	 		}
	 		return $users;
	 	} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $users;
	 	}
	 }

	 public function getUserDetails($userSer) { 
	 	$userDetails = array();
	 	try {
	 		$connect = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
	 		$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	 		$sql = "
		 		SELECT DISTINCT
			 		OAUser.Username,
			 		Role.RoleSerNum,
			 		Role.RoleName,
			 		OAUser.Language
		 		FROM   
			 		OAUser,
			 		OAUserRole,
			 		Role
		 		WHERE
			 		OAUser.OAUserSerNum 	= $userSer
			 	AND OAUserRole.OAUserSerNum	= OAUser.OAUserSerNum
			 	AND Role.RoleSerNum 		= OAUserRole.RoleSerNum
	 		";
	 		$query = $connect->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	 		$query->execute();

	 		$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

	 		$username   = $data[0];
	 		$roleSer 	= $data[1];
	 		$roleName 	= $data[2];
	 		$language 	= $data[3];

	 		$userDetails = array(
	 			'serial'            => $userSer,
	 			'username'          => $username,
	 			'language' 			=> $language,
	 			'role' 				=> array('serial'=>$roleSer,'name'=>$roleName),
	 			'logs'              => array(),
	 			'new_password'      => null,
	 			'confirm_password'  => null
	 			);

	 		return $userDetails;
	 	} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $userDetails;
	 	}
	 }


	/**
	 *
	 * Determines the existence of a username
	 *
	 * @param string $username : username to check
	 *
	 * @return array $Response : response
	 */
	public function usernameAlreadyInUse($username) {
		$Response = null;
		try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
            	SELECT DISTINCT
            		ato.Username
            	FROM
            		OAUser ato
            	WHERE
            		ato.Username = \"$username\"
            	LIMIT 1
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $Response = 'FALSE';
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                if ($data[0]) {
                    $Response = 'TRUE';
                }
            }

            return $Response;

        } catch (PDOException $e) {
            return $Response;
        }
	}

	/**
	 *
	 * Removes a user from the database
	 *
	 * @param integer $userSer : the user serial number
	 * @return array $response : response
	 */
	public function removeUser( $userSer ) {

		// Initialize a response array
		$response = array(
			'value'		=> 0,
			'message'	=> ''
		);
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "DELETE FROM OAUserRole WHERE OAUserRole.OAUserSerNum = $userSer";
			$query = $host_db_link->prepare( $sql );
            $query->execute();

			$sql = "DELETE FROM OAUser WHERE OAUser.OAUserSerNum = $userSer";
			$query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}

	/**
	 *
	 * Gets a list of possible roles from the database
	 *
	 * @return array $roles : roles
	 */

	public function getRoles() {
	 	$roles = array();
	 	try {
	 		$connect = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
	 		$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	 		$sql = "
		 		SELECT DISTINCT
		 			Role.RoleSerNum,
		 			Role.RoleName
		 		FROM
			 		Role
			 	ORDER BY 
			 		Role.RoleName
	 		";           
	 		$query = $connect->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	 		$query->execute();

	 		while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

	 			$serial = $data[0];
	 			$name   = $data[1];

	 			$roleArray = array(
	 				'serial'    	=> $serial,
	 				'name'      	=> $name,
	 			);
	 			array_push($roles, $roleArray);
	 		}
	 		return $roles;
	 	} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $roles;
	 	}
	 }
 }
 
?>
