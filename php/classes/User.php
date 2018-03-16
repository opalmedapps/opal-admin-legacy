<?php
	/**
	 * User class
	 *
	 *
	 */
 class Users {
	 public $username = null;
	 public $password = null;
	 public $role = null;
	 public $language = null;
	 public $userid = null;
	 public $sessionid = null;
	 public $salt = "Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8lqu1ElzHv0Ri7EM6irpx5w";
	 
	 /* Class constructor*/ 
	 public function __construct( $data = array() ) {
		 if( isset( $data->username ) ) $this->username = stripslashes( strip_tags( $data->username ) );
		 if( isset( $data->password ) ) $this->password = stripslashes( strip_tags( $data->password ) );
		 if( isset( $data->cypher ) ) $this->cypher = stripslashes( strip_tags( $data->cypher ) );
	 }
	 
	 public function storeFormValues( $params ) {
		//store the parameters
		$this->__construct( $params ); 
	 }
	 
	 /**
     *
     * Logs in a particular user
     *
     * @return boolean $success : successful login flag
     */
	 public function userLogin() {
		 $success = false;
		 $d = new Encrypt;
		 try{
			$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "SELECT * FROM OAUser WHERE OAUser.Username = :username AND OAUser.Password = :password";
			
			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $this->username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $d->encodeString( $this->password, $this->cypher ) . $this->salt), PDO::PARAM_STR );
			$stmt->execute();
			
			$valid = $stmt->fetchColumn();
			if( $valid ) {
				$this->userid = $valid;
				$userDetails = $this->getUserDetails($valid);
				$this->role = $userDetails['role']['name'];
				$this->language = $userDetails['language'];
				$this->sessionid = $this->makeSessionId();

				$this->logActivity($this->userid, $this->sessionid, 'Login');
				$success = true;
			}
			
			$con = null;
			return $success;
		 }catch (PDOException $e) {
			 echo $e->getMessage();
			 return $success;
		 }
	 }

	  /**
	  *
	  * Logs out a user
	  *
	  * @param $user : user object
	  * @return $response : response
	  */
	public function userLogout($user) {
		$userser = $user['userser'];
		$sessionid = $user['sessionid'];
		$response = $this->logActivity($userser, $sessionid, 'Logout');
		return $response;
	}

	 /**
	  *
	  * Logs when a user logs in or logs out
	  *
	  * @return $response : response
	  */
	public function logActivity($userser, $sessionid, $activity) {
		$response = array (
	 		'value'		=> 0,
 			'message'	=> ''
 		);
 		try{
			$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
					OAActivityLog (
						Activity,
						OAUserSerNum,
						SessionId,
						DateAdded
					)
				VALUES (
					'$activity',
					'$userser',
					'$sessionid',
					NOW()
				)
			";
			$query = $con->prepare($sql);
			$query->execute();
			$response['value'] = 1; // success
			return $response;

		}catch (PDOException $e) {
			 $response['message'] = $e->getMessage();
			 return $response;
		 }
	}

	 /**
     *
     * Sets a session id
     *
     * @return string $sessionid : session id
     */
     public function makeSessionId($length = 10) {
     	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
     }

	/**
     *
     * Updates a user's password
     *
     * @param array $userDetails  : the user details
     * @return array $response : response
     */
	public function updatePassword($userDetails) {
	 	$response = array (
	 		'value'		=> 0,
	 		'error'		=> array(
	 			'code'		=> '',
	 			'message'	=> ''
	 		)
	 	);
	 	$oldPassword 	= $userDetails['oldPassword'];
	 	$userSer		= $userDetails['user']['id'];
	 	$newPassword	= $userDetails['password'];
	 	$cypher 			= $userDetails['cypher'];
		$d = new Encrypt;
	 	try {
	 		$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			if (!isset($userDetails['override'])) {
				$sql = "SELECT * FROM OAUser WHERE OAUser.OAUserSerNum = :ser AND OAUser.Password = :password";

				$stmt = $con->prepare( $sql );
				$stmt->bindValue( "ser", $userSer, PDO::PARAM_STR );
				$stmt->bindValue( "password", hash("sha256", $d->encodeString( $oldPassword, $cypher ) . $this->salt), PDO::PARAM_STR );
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
			$stmt->bindValue( "password", hash("sha256", $d->encodeString( $newPassword, $cypher ) . $this->salt), PDO::PARAM_STR );
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
     * Updates a user
     *
     * @param array $userDetails  : the user details
     * @return boolean
     */
	 public function updateUser($userDetails) {
	 	$response = array (
	 		'value'		=> 0,
	 		'error'		=> array(
	 			'code'		=> '',
	 			'message'	=> ''
	 		)
	 	);
	 	$userSer			= $userDetails['user']['id'];
	 	$newPassword 		= $userDetails['password'];
	 	$confirmPassword 	= $userDetails['confirmPassword'];
	 	$roleSer 			= $userDetails['role']['serial'];

	 	try {

	 		if ( ($newPassword && $confirmPassword) && ($newPassword == $confirmPassword) ) {
	 			$response = $this->updatePassword($userDetails);
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
	 * @param array $userDetails : the user details
	 * @return void
	 */
	public function registerUser($userDetails) {
		$username 		= $userDetails['username'];
		$password 		= $userDetails['password'];
		$roleSer 		= $userDetails['role']['serial'];
		$cypher 		= $userDetails['cypher'];
		$d = new Encrypt;
		try {
			$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "INSERT INTO OAUser(Username, Password, DateAdded) VALUES(:username, :password, NOW())";
			
			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $d->encodeString( $password, $cypher ) . $this->salt), PDO::PARAM_STR );
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

	/**
     *
     * Gets a list of existing users
     *
     * @param array $USERS : the list of existing users
     * @return boolean
     */
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

	/**
     *
     * Gets a user's details
     *
     * @param integer $userSer    : the user serial number
     * @return array $userDetails : the user details
     */
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
	 * Deletes a user from the database
	 *
	 * @param integer $userSer : the user serial number
	 * @return array $response : response
	 */
	public function deleteUser( $userSer ) {

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

    /**
     *
     * Gets a list of user activities
     *
     * @return array $userActivityList : the list of user activities
     */
    public function getUserActivities() {
        $userActivityList = array();
         try {

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
	            SELECT DISTINCT 
	            	oaa.OAUserSerNum,
	            	oa.Username,
	            	oaa.DateAdded as LoginTime, 
	            	oaa2.DateAdded as LogoutTime, 
	            	oaa.SessionId,
	            	CONCAT (
	            		IF(MOD(HOUR(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 24) > 0,
	            			CONCAT(MOD(HOUR(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 24), 'h'),
	            			''
	            		),
	            		IF(MINUTE(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)) > 0,
	            			CONCAT(MINUTE(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 'm'),
	            			''
	            		),
	            		SECOND(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 's'
	            	) as SessionDuration

	            FROM 
	            	OAUser oa,
	            	OAActivityLog oaa 
	            LEFT JOIN 
	            	OAActivityLog oaa2 
	            ON oaa.SessionId = oaa2.SessionId  
	            AND oaa2.Activity = 'Logout' 
	            WHERE 
	            	oaa.`Activity` 	= 'Login'
	            AND oa.OAUserSerNum = oaa.OAUserSerNum

                ORDER BY oaa.DateAdded DESC
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $userDetails = array(
                    'serial'                => $data[0],
                    'username'              => $data[1],
                    'login'                 => $data[2],
                    'logout'				=> $data[3],
                    'sessionid'             => $data[4],
                    'session_duration'		=> $data[5]
                );

                array_push($userActivityList, $userDetails);
            }

            return $userActivityList;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $userActivityList;
        }
    }
 }
 
?>
