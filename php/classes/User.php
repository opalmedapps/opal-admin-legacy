<?php

 class Users {
	 public $username = null;
	 public $password = null;
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
			$con = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "SELECT * FROM ATOUser WHERE ATOUser.Username = :username AND ATOUser.Password = :password";
			
			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $this->username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $this->password . $this->salt), PDO::PARAM_STR );
			$stmt->execute();
			
			$valid = $stmt->fetchColumn();
			if( $valid ) {
				$this->userid = $valid;
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
	 		$con = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD ); 
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			if (!isset($userArray['override'])) {
				$sql = "SELECT * FROM ATOUser WHERE ATOUser.UserSerNum = :ser AND ATOUser.Password = :password";

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

			$sql = "UPDATE ATOUser SET ATOUser.Password = :password WHERE ATOUser.UserSerNum = :ser";

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
		try {
			$con = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD );
			$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "INSERT INTO ATOUser(Username, Password, DateAdded) VALUES(:username, :password, NOW())";
			
			$stmt = $con->prepare( $sql );
			$stmt->bindValue( "username", $username, PDO::PARAM_STR );
			$stmt->bindValue( "password", hash("sha256", $password . $this->salt), PDO::PARAM_STR );
			$stmt->execute();
			return;

		}catch( PDOException $e ) {
			return $e->getMessage();
		}
	}

	 public function getUsers() {
	 	$users = array();
	 	try {
	 		$connect = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD );
	 		$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	 		$sql = "
	 		SELECT DISTINCT
	 		ATOUser.UserSerNum,
	 		ATOUser.Username
	 		FROM
	 		ATOUser
	 		";           
	 		$query = $connect->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	 		$query->execute();

	 		while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

	 			$serial = $data[0];
	 			$name   = $data[1];

	 			$userArray = array(
	 				'serial'    	=> $serial,
	 				'username'      => $name
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
	 		$connect = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD );
	 		$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	 		$sql = "
	 		SELECT DISTINCT
	 		ATOUser.Username
	 		FROM   
	 		ATOUser
	 		WHERE
	 		ATOUser.UserSerNum = $userSer
	 		";
	 		$query = $connect->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	 		$query->execute();

	 		$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

	 		$username   = $data[0];

	 		$userDetails = array(
	 			'serial'            => $userSer,
	 			'username'          => $username,
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
            $host_db_link = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
            	SELECT DISTINCT
            		ato.Username
            	FROM
            		ATOUser ato
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
	 * @return array : response
	 */
	public function removeUser( $userSer ) {

		// Initialize a response array
		$response = array(
			'value'		=> 0,
			'message'	=> ''
		);
		try {
			$host_db_link = new PDO( HOST_DB_DSN, HOST_DB_USERNAME, HOST_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				DELETE FROM
					ATOUser
				WHERE
					ATOUser.UserSerNum = $userSer
			";

			$query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}
 }
 
?>
