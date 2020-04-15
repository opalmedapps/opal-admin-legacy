<?php
/**
 * User class to validate its identity and access levels
 */
class User extends OpalProject {
    public $username = null;
    public $password = null;
    public $role = null;
    public $language = null;
    public $userid = null;
    public $sessionid = null;

    /*
     * Constructor. If no user Id is given, give guest right so the login can be done. Call the parent constructor
     * */
    public function __construct($OAUserId = false) {
        $guestAccess = !$OAUserId;
        parent::__construct($OAUserId, false, $guestAccess);
    }

    /*
     * Validate the user and log its activity.
     * @param   $post (array) contains username, password and cypher
     * @return  $result (array) basic user informations
     * */
    public function userLogin($post) {
        $post = HelpSetup::arraySanitization($post);
        $data = json_decode(Encrypt::encodeString( $post["encrypted"], $post["cypher"]), true);
        $data = HelpSetup::arraySanitization($data);
        $username = $data["username"];
        $password = $data["password"];
        $cypher = $post["cypher"];

        if($username == "" || $password == "" || $cypher == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing login info.");

        $result = $this->opalDB->validateOpalUserLogin($username, hash("sha256", $password . USER_SALT));

        if(count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Access denied");
        else if(count($result) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Somethings's wrong. There is too many entries!");

        $result = $result[0];
        $this->_connectAsMain($result["id"]);
        unset($result["password"]);
        $result["sessionid"] = HelpSetup::makeSessionId();
        $this->logActivity($result["id"], $result["sessionid"], 'Login');

        return $result;
    }

    /*
     * Logs the user out by logging it in the logActivity.
     * @params  $post (array) info of the user
     * @return  answer from the log activity
     * */
    public function userLogout($post) {
        $post = HelpSetup::arraySanitization($post);
        if($post["OAUserId"] == "" || $post["sessionId"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing logout info.");

        return $this->logActivity($post["OAUserId"], $post["sessionId"], 'Logout');
    }

    /*
     * Log activity into the table OAActivityLog.
     * @params  $userId (int) ID of the user to enter
     *          $sessionId (string) session ID of the user
     *          $activity (string) type of activity to log in (Login or Logout)
     * */
    public function logActivity($userId, $sessionId, $activity) {
        return $this->opalDB->insertUserActivity(array("Activity"=>$activity, "OAUserSerNum"=>$userId, "SessionId"=>$sessionId));
    }

    /*
     * Function to validate a password. It confirms the password and confirmation are the same, check the length (min
     * 8 char), check if there is ate least one special character, at least one number, at least one capital letter,
     * at least one lower case number.
     * @param   $password (string) new password to validate
     *          $confirmPassword (string) confirmation of the password
     * @return  $errMsg (array) array of error messages if any.
     * */
    protected function _passwordValidation($password, $confimPassword) {
        $errMsgs = array();
        if($confimPassword != $password)
            array_push($errMsgs, "Password and password confirmation do not match.");

        $length = (strlen($password) >= 8);
        $special = preg_match('#[\W]+#', $password);
        $number = preg_match("#[0-9]+#",$password);
        $upper = preg_match("#[A-Z]+#",$password);
        $lower = preg_match("#[a-z]+#",$password);

        if(!$length || !$special || !$number || !$upper || !$lower)
            array_push($errMsgs, "Invalid password format.");

        return $errMsgs;
    }

    /*
     * Updates the password of a specific user after validating it.
     * @param   $post (array) array of data coming from the frontend that contains encrypted data and the cypher.
     * @return  number of updated record
     * */
    public function updatePassword($post) {
        $post = HelpSetup::arraySanitization($post);
        $cypher = intval($post["cypher"]);
        $data = json_decode(Encrypt::encodeString( $post["encrypted"], $cypher), true);
        $data = HelpSetup::arraySanitization($data);

        $username = $this->opalDB->getUserDetails($post["OAUserId"]);
        $username = $username["username"];
        $oldPassword = $data["oldPassword"];
        $password = $data["password"];
        $confirmPassword = $data["confirmPassword"];

        if($username == "" || $password == "" || $oldPassword == "" || $confirmPassword == "" || $cypher == "" || $password == $oldPassword)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing update information.");

        $result = $this->_passwordValidation($password, $confirmPassword);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));

        $result = $this->opalDB->validateOpalUserLogin($username, hash("sha256", $oldPassword . USER_SALT));
        if(count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid username/password.");
        else if(count($result) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Somethings's wrong. There is too many entries!");

        $result = $result[0];

        $updated = $this->opalDB->updateUserPassword($result["id"], hash("sha256", $password . USER_SALT));
        return $updated;
    }

    /*
     * Update the preferred language on how to display the opalAdmin to the user. Can only accept FR or EN
     * @params  $post (array) contains language requested and userId
     * @returns number of records modified
     * */
    public function updateLanguage($post) {
        $post = HelpSetup::arraySanitization($post);
        $post["language"] = strtoupper($post["language"]);

        if($post["language"] != "EN" && $post["language"] != "FR")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid language");

        return $this->opalDB->updateUserLanguage($this->opalDB->getOAUserId(), $post["language"]);
    }

    /*
     * Decypher user informations, validate its password before updating it, updating the language and the role. All
     * the updates are optionals.
     * @oarams  $post (array) informations on the user encrypted with the cypher and the id.
     * @return  true (boolean) means the update was successful.
     * */
    public function updateUser($post) {
        $post = HelpSetup::arraySanitization($post);
        $cypher = intval($post["cypher"]);
        $data = json_decode(Encrypt::encodeString( $post["encrypted"], $cypher), true);
        $data = HelpSetup::arraySanitization($data);

        $userDetails = $this->opalDB->getUserDetails($data["id"]);

        if(!is_array($userDetails))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid user.");

        if($data["password"] && $data["confirmPassword"]) {
            $result = $this->_passwordValidation($data["password"], $data["confirmPassword"]);
            if (count($result) > 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));
            $this->opalDB->updateUserPassword($userDetails["serial"], hash("sha256", $data["password"] . USER_SALT));
        }

        $newRole = $this->opalDB->geRoleDetails($data["roleId"]);
        if(!is_array($newRole))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid role.");

        if($data["roleId"] != $userDetails["RoleSerNum"])
            $this->opalDB->updateUserRole($data["id"], $data["roleId"]);

        $this->opalDB->updateUserInfo($userDetails["serial"], $data["language"]);

        return true;
    }

    /*
     * insert a new user into the OAUser table and its role in OAUserRole table after sanitizing and validating the
     * data.
     * @params  $post (array) contains the username, password, confirmed password, role, language (all encrypted),
     *          cypher.
     * @returns void
     * */
    public function insertUser($post) {
        $post = HelpSetup::arraySanitization($post);
        $cypher = intval($post["cypher"]);
        $data = json_decode(Encrypt::encodeString( $post["encrypted"], $cypher), true);
        $data = HelpSetup::arraySanitization($data);

        $username = $data["username"];
        $password = $data["password"];
        $confirmPassword = $data["confirmPassword"];
        $roleId = $data["roleId"];
        $language = strtoupper($data["language"]);

        if($username == "" || $password == "" || $confirmPassword == "" || $cypher == "" || $roleId == "" || $language == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data to create user.");

        if($language != "FR" && $language != "EN")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Wrong language.");

        $result = $this->_passwordValidation($password, $confirmPassword);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));

        $role = $this->opalDB->geRoleDetails($data["roleId"]);
        if(!is_array($role))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid role.");

        $userId = $this->opalDB->insertUser($username, hash("sha256", $data["password"] . USER_SALT), $language);
        $result = $this->opalDB->insertUserRole($userId, $roleId);
    }


    /*
     * Get the list of all users excluding the cronjob one
     * @params  void
     * @return  array of users
     * */
    public function getUsers() {
        return $this->opalDB->getUsersList();
    }

    /*
     * Get users details based on its ID. Format the data in a way the front won't crash. It sends username, role info,
     * and language.
     * @params  $post (array) data receive from the front in $_POST method
     * @returns $userDetails (array) details of the user
     * */
    public function getUserDetails($post) {
        $post = HelpSetup::arraySanitization($post);
        $userDetails = $this->opalDB->getUserDetails($post["userId"]);
        $userDetails["role"] = array("serial"=>$userDetails["RoleSerNum"], "name"=>$userDetails["RoleName"]);
        $userDetails["logs"] = array();
        $userDetails["new_password"] = null;
        $userDetails["confirm_password"] = null;
        unset($userDetails["RoleSerNum"]);
        unset($userDetails["RoleName"]);
        return $userDetails;
    }

    /*
     * returns if the username is already in use or not
     * @params  $username (string)
     * @return  boolean if the result is greater than 0 or not
     * */
    public function usernameAlreadyInUse($username) {
        $results = $this->opalDB->countUsername($username);
        $results = intval($results["total"]);
        return $results > 0;
    }

    /**
     * Mark a user as deleted. An user cannot delete its own record
     *
     * WARNING!!! No record should be EVER be removed from the opalDB database!
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @params $userId (int) ID of the user
     * @return void
     */
    public function deleteUser($userId) {
        $userId = strip_tags($userId);
        if($userId == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid user.");
        elseif ($userId == $this->opalDB->getOAUserId())
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An user cannot delete itself.");

        $this->opalDB->markUserAsDeleted($userId);
    }


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
			 	WHERE Role.RoleSerNum != ".ROLE_CRONJOB."

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
