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
     * Updates a user's language
     *
     * @param array $userDetails  : the user details
     * @return array $response : response
     */
	public function updateLanguage($userDetails) {
	 	$response = array (
	 		'value'		=> 0,
	 		'error'		=> array(
	 			'code'		=> '',
	 			'message'	=> ''
	 		)
	 	);
	 	$language 	= $userDetails['language'];
	 	$userSer	= $userDetails['id'];

	 	try {
	 		$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "UPDATE OAUser SET OAUser.Language = :language WHERE OAUser.OAUserSerNum = :ser";

			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "ser", $userSer, PDO::PARAM_STR );
			$stmt->bindValue( "language", $language, PDO::PARAM_STR );
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
	 	$language 			= $userDetails['language'];

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

			$sql = "UPDATE OAUser SET OAUser.Language = '$language' WHERE OAUser.OAUserSerNum = $userSer";
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
		$language 		= $userDetails['language'];
		$cypher 		= $userDetails['cypher'];
		$d = new Encrypt;
		try {
			$con = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "INSERT INTO OAUser(Username, Password, Language, DateAdded) VALUES(:username, :password, :language, NOW())";
			
			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $d->encodeString( $password, $cypher ) . $this->salt), PDO::PARAM_STR );
			$stmt->bindValue( "language", $language, PDO::PARAM_STR );
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

    /**
     *
     * Gets logs of a user's activity
     *
     * @param integer $userSer : user serial 
     * @return array $userLogs : the logs of user activities
     */
    public function getUserActivityLogs($userSer) {
        $userLogs = array(
        	'isData' 				=> 0,
        	'login'					=> array(),
        	'alias'					=> array(),
        	'aliasExpression'		=> array(),
        	'diagnosisTranslation'	=> array(),
        	'diagnosisCode'			=> array(),
        	'email'					=> array(),
        	'trigger'				=> array(),
        	'hospitalMap'			=> array(),
        	'post'					=> array(),
        	'notification'			=> array(),
        	'legacyQuestionnaire'	=> array(),
        	'testResult'			=> array(),
        	'testResultExpression'	=> array()
        );	
         try {

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            /* Logins */
            $sql = "
	            SELECT DISTINCT 
	            	oaa.OAUserSerNum,
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
	            AND oa.OAUserSerNum = '$userSer'

                ORDER BY oaa.DateAdded DESC
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $loginDetails = array(
                    'serial'                => $data[0],
                    'login'                 => $data[1],
                    'logout'				=> $data[2],
                    'sessionid'             => $data[3],
                    'session_duration'		=> $data[4]
                );

                array_push($userLogs['login'], $loginDetails);
                $userLogs['isData'] = 1;
            }

            /* Alias */
            $sql = "
            	SELECT DISTINCT 
            		almh.AliasSerNum,
            		almh.AliasRevSerNum,
            		almh.SessionId,
            		almh.AliasType,
            		almh.AliasUpdate,
            		almh.AliasName_EN,
            		almh.AliasName_FR,
            		almh.AliasDescription_EN,
            		almh.AliasDescription_FR,
            		almh.EducationalMaterialControlSerNum,
            		almh.SourceDatabaseSerNum,
            		almh.ColorTag,
            		almh.ModificationAction,
            		almh.DateAdded
            	FROM
            		AliasMH almh
            	WHERE
            		almh.LastUpdatedBy = $userSer
            	ORDER BY 
            		almh.DateAdded DESC
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

            	$aliasDetails = array(
            		'serial'			=> $data[0],
            		'revision'			=> $data[1],
            		'sessionid'			=> $data[2],
            		'type'				=> $data[3],
            		'update'			=> $data[4],
            		'name_EN'			=> $data[5],
            		'name_FR'			=> $data[6],
            		'description_EN'	=> $data[7],
            		'description_FR'	=> $data[8],
            		'educational_material'	=> $data[9],
            		'source_db'				=> $data[10],
            		'color'					=> $data[11],
            		'mod_action'			=> $data[12],
            		'date_added'			=> $data[13]
            	);

                array_push($userLogs['alias'], $aliasDetails);
                $userLogs['isData'] = 1;
            }

            /* Alias Expression */
            $sql = "
            	SELECT DISTINCT 
            		aemh.AliasSerNum,
            		aemh.RevSerNum,
            		aemh.SessionId,
            		aemh.ExpressionName,
            		aemh.Description,
            		aemh.ModificationAction,
            		aemh.DateAdded
            	FROM
            		AliasExpressionMH aemh
            	WHERE
            		aemh.LastUpdatedBy = $userSer
            	ORDER BY 
            		aemh.DateAdded DESC
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

            	$aliasExpressionDetails = array(
            		'serial'			=> $data[0],
            		'revision'			=> $data[1],
            		'sessionid'			=> $data[2],
            		'expression'		=> $data[3],
            		'resource_description'	=> $data[4],
            		'mod_action'			=> $data[5],
            		'date_added'			=> $data[6]
            	);

                array_push($userLogs['aliasExpression'], $aliasExpressionDetails);
                $userLogs['isData'] = 1;
            }

            /* Diagnosis Translation*/
            $sql = "
            	SELECT DISTINCT
            		dtmh.DiagnosisTranslationSerNum,
            		dtmh.RevSerNum,
            		dtmh.SessionId,
            		dtmh.EducationalMaterialControlSerNum,
            		dtmh.Name_EN,
            		dtmh.Name_FR,
            		dtmh.Description_EN,
            		dtmh.Description_FR,
            		dtmh.ModificationAction,
            		dtmh.DateAdded
            	FROM
            		DiagnosisTranslationMH dtmh
            	WHERE
            		dtmh.LastUpdatedBy = $userSer
            	ORDER BY
            		dtmh.DateAdded DESC

            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

            	$diagnosisTranslationDetails = array (
            		'serial'				=> $data[0],
            		'revision'				=> $data[1],
            		'sessionid'				=> $data[2],
            		'educational_material'	=> $data[3],
            		'name_EN'				=> $data[4],
            		'name_FR'				=> $data[5],
            		'description_EN'		=> $data[6],
            		'description_FR'		=> $data[7],
            		'mod_action'			=> $data[8],
            		'date_added'			=> $data[9]
            	);
                array_push($userLogs['diagnosisTranslation'], $diagnosisTranslationDetails);
                $userLogs['isData'] = 1;
            }

            /* Diagnosis Code */
	        $sql = "
	        	SELECT DISTINCT 
	        		dcmh.DiagnosisTranslationSerNum,
	        		dcmh.RevSerNum,
	        		dcmh.SessionId,
	        		dcmh.SourceUID,
	        		dcmh.DiagnosisCode,
	        		dcmh.Description,
	        		dcmh.ModificationAction,
	        		dcmh.DateAdded
	        	FROM 
	        		DiagnosisCodeMH dcmh
	        	WHERE
	        		dcmh.LastUpdatedBy = $userSer
	        	ORDER BY
	        		dcmh.DateAdded DESC
	        ";

	        $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$diagnosisCodeDetails = array(
            		'serial'		=> $data[0],
            		'revision'		=> $data[1],
            		'sessionid'		=> $data[2],
            		'sourceuid'		=> $data[3],
            		'code'			=> $data[4],
            		'description'	=> $data[5],
            		'mod_action'	=> $data[6],
            		'date_added'	=> $data[7]
            	);

                array_push($userLogs['diagnosisCode'], $diagnosisCodeDetails);
                $userLogs['isData'] = 1;
            }

            /* Email */
            $sql = "
            	SELECT DISTINCT
            		ecmh.EmailControlSerNum,
            		ecmh.RevSerNum,
            		ecmh.SessionId,
            		ecmh.Subject_EN,
            		ecmh.Subject_FR,
            		ecmh.Body_EN,
            		ecmh.Body_FR,
            		ecmh.ModificationAction,
            		ecmh.DateAdded
            	FROM
            		EmailControlMH ecmh
            	WHERE
            		ecmh.LastUpdatedBy = $userSer
            	ORDER BY
            		ecmh.DateAdded DESC
           	";
           	$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$emailDetails = array (
            		'serial'		=> $data[0],
            		'revision'		=> $data[1],
            		'sessionid'		=> $data[2],
            		'subject_EN'	=> $data[3],
            		'subject_FR'	=> $data[4],
            		'body_EN'		=> $data[5],
            		'body_FR'		=> $data[6],
            		'mod_action'	=> $data[7],
            		'date_added'	=> $data[8]
            	);
                array_push($userLogs['email'], $emailDetails);
                $userLogs['isData'] = 1;
            }

            /* Trigger */
            $sql = "
            	SELECT DISTINCT
            		fmh.ControlTableSerNum,
            		fmh.ControlTable,
            		fmh.SessionId,
            		fmh.FilterType,
            		fmh.FilterId,
            		fmh.ModificationAction,
            		fmh.DateAdded
            	FROM
            		FiltersMH fmh
            	WHERE
            		fmh.LastUpdatedBy = $userSer
            	ORDER BY 
            		fmh.DateAdded DESC
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$triggerDetails = array(
            		'control_serial'		=> $data[0],
            		'control_table'			=> $data[1],
            		'sessionid'				=> $data[2],
            		'type'					=> $data[3],
            		'filterid'				=> $data[4],
            		'mod_action'			=> $data[5],
            		'date_added'			=> $data[6]
            	);

                array_push($userLogs['trigger'], $triggerDetails);
                $userLogs['isData'] = 1;
            }

            /* Hospital Map */
            $sql = "
            	SELECT DISTINCT
            		hmmh.HospitalMapSerNum,
            		hmmh.RevSerNum,
            		hmmh.SessionId,
            		hmmh.MapUrl,
            		hmmh.QRMapAlias,
            		hmmh.MapName_EN,
            		hmmh.MapName_FR,
            		hmmh.MapDescription_EN,
            		hmmh.MapDescription_FR,
            		hmmh.ModificationAction,
            		hmmh.DateAdded
            	FROM
            		HospitalMapMH hmmh
            	WHERE
            		hmmh.LastUpdatedBy = $userSer
            	ORDER BY
            		hmmh.DateAdded DESC
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$hospitalMapDetails = array(
            		'serial'			=> $data[0],
            		'revision'			=> $data[1],
            		'sessionid'			=> $data[2],
            		'url'				=> $data[3],
            		'qrcode'			=> $data[4],
            		'name_EN'			=> $data[5],
            		'name_FR'			=> $data[6],
            		'description_EN'	=> $data[7],
            		'description_FR'	=> $data[8],
            		'mod_action'		=> $data[9],
            		'date_added'		=> $data[10]
            	);
                array_push($userLogs['hospitalMap'], $hospitalMapDetails);
                $userLogs['isData'] = 1;
            }

            /* Posts */
            $sql = "
            	SELECT DISTINCT
            		pcmh.PostControlSerNum,
            		pcmh.RevSerNum,
            		pcmh.SessionId,
            		pcmh.PostType,
            		pcmh.PublishFlag,
            		pcmh.Disabled,
            		pcmh.PublishDate,
            		pcmh.PostName_EN,
            		pcmh.PostName_FR,
            		pcmh.Body_EN,
            		pcmh.Body_FR,
            		pcmh.ModificationAction,
            		pcmh.DateAdded
            	FROM
            		PostControlMH pcmh
            	WHERE
            		pcmh.LastUpdatedBy = $userSer
            	ORDER BY
            		pcmh.DateAdded DESC
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$postDetails = array(
            		'control_serial'		=> $data[0],
            		'revision'				=> $data[1],
            		'sessionid'				=> $data[2],
            		'type'					=> $data[3],
            		'publish'				=> $data[4],
            		'disabled'				=> $data[5],
            		'publish_date'			=> $data[6],
            		'name_EN'				=> $data[7],
            		'name_FR'				=> $data[8],
            		'body_EN'				=> $data[9],
            		'body_FR'				=> $data[10],
            		'mod_action'			=> $data[11],
            		'date_added'			=> $data[12]
            	);
                array_push($userLogs['post'], $postDetails);
                $userLogs['isData'] = 1;

            }

            /* Notification */
            $sql = "
            	SELECT DISTINCT
            		ncmh.NotificationControlSerNum,
            		ncmh.RevSerNum,
            		ncmh.SessionId,
            		ncmh.NotificationTypeSerNum,
            		ncmh.Name_EN,
            		ncmh.Name_FR,
            		ncmh.Description_EN,
            		ncmh.Description_FR,
            		ncmh.ModificationAction,
            		ncmh.DateAdded
            	FROM
            		NotificationControlMH ncmh
            	WHERE
            		ncmh.LastUpdatedBy = $userSer
            	ORDER BY
            		ncmh.DateAdded DESC
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$notificationDetails = array(
            		'control_serial'		=> $data[0],
            		'revision'				=> $data[1],
            		'sessionid'				=> $data[2],
            		'type'					=> $data[3],
            		'name_EN'				=> $data[4],
            		'name_FR'				=> $data[5],
            		'description_EN'		=> $data[6],
            		'description_FR'		=> $data[7],
            		'mod_action'			=> $data[8],
            		'date_added'			=> $data[9]
            	);
                array_push($userLogs['notification'], $notificationDetails);
                $userLogs['isData'] = 1;

            }

            /* Legacy Questionnaires */
            $sql = "
            	SELECT DISTINCT
            		qcmh.QuestionnaireControlSerNum,
            		qcmh.RevSerNum,
            		qcmh.SessionId,
            		qcmh.QuestionnaireDBSerNum,
            		qcmh.QuestionnaireName_EN,
            		qcmh.QuestionnaireName_FR,
            		qcmh.Intro_EN,
            		qcmh.Intro_FR,
            		qcmh.PublishFlag,
            		qcmh.ModificationAction,
            		qcmh.DateAdded
            	FROM
            		QuestionnaireControlMH qcmh
            	WHERE
            		qcmh.LastUpdatedBy = $userSer
            	ORDER BY 
            		qcmh.DateAdded DESC
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$legacyQuestionnaireDetails = array (
            		'control_serial'	=> $data[0],
            		'revision'			=> $data[1],
            		'sessionid'			=> $data[2],
            		'db_serial'			=> $data[3],
            		'name_EN'			=> $data[4],
            		'name_FR'			=> $data[5],
            		'intro_EN'			=> $data[6],
            		'intro_FR'			=> $data[7],
            		'publish'			=> $data[8],
            		'mod_action'		=> $data[9],
            		'date_added'		=> $data[10]
            	);
                array_push($userLogs['legacyQuestionnaire'], $legacyQuestionnaireDetails);
                $userLogs['isData'] = 1;
            }

            /* Test Result */
            $sql = "
            	SELECT DISTINCT
            		trcmh.TestResultControlSerNum,
            		trcmh.RevSerNum,
            		trcmh.SessionId,
            		trcmh.SourceDatabaseSerNum,
            		trcmh.EducationalMaterialControlSerNum,
            		trcmh.Name_EN,
            		trcmh.Name_FR,
            		trcmh.Description_EN,
            		trcmh.Description_FR,
            		trcmh.Group_EN,
            		trcmh.Group_FR,
            		trcmh.PublishFlag,
            		trcmh.ModificationAction,
            		trcmh.DateAdded
            	FROM
            		TestResultControlMH trcmh 
            	WHERE
            		trcmh.LastUpdatedBy = $userSer
            	ORDER BY 
            		trcmh.DateAdded DESC
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$testResultDetails = array(
            		'control_serial'			=> $data[0],
            		'revision'					=> $data[1],
            		'sessionid'					=> $data[2],
            		'source_db'					=> $data[3],
            		'educational_material'		=> $data[4],
            		'name_EN'					=> $data[5],
            		'name_FR'					=> $data[6],
            		'description_EN'			=> $data[7],
            		'description_FR'			=> $data[8],
            		'group_EN'					=> $data[9],
            		'group_FR'					=> $data[10],
            		'publish'					=> $data[11],
            		'mod_action'				=> $data[12],
            		'date_added'				=> $data[13]
            	);
                array_push($userLogs['testResult'], $testResultDetails);
                $userLogs['isData'] = 1;
            }

            /* Test Result Expressions */
            $sql = "
            	SELECT DISTINCT
            		tremh.TestResultControlSerNum,
            		tremh.RevSerNum,
            		tremh.SessionId,
            		tremh.ExpressionName,
            		tremh.ModificationAction,
            		tremh.DateAdded
            	FROM
            		TestResultExpressionMH tremh
            	WHERE
            		tremh.LastUpdatedBy = $userSer
            	ORDER BY
            		tremh.DateAdded DESC
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
            	$testResultExpressionDetails = array (
            		'control_serial'	=> $data[0],
            		'revision'			=> $data[1],
            		'sessionid'			=> $data[2],
            		'expression'		=> $data[3],
            		'mod_action'		=> $data[4],
            		'date_added'		=> $data[5]
            	);
                array_push($userLogs['testResultExpression'], $testResultExpressionDetails);
                $userLogs['isData'] = 1;
            }

            return $userLogs;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $userLogs;
        }


    }
 }
 
?>
