<?php
/**
 * User class to validate its identity and access levels
 */
class User extends OpalProject {

    /*
     * Constructor. If no user Id is given, give guest right so the login can be done. Call the parent constructor
     * */
    public function __construct($OAUserId = false) {
        $guestAccess = !$OAUserId;
        parent::__construct($OAUserId, false, $guestAccess);
    }

    /*
     * Validate the number of results of authentication. If different than one, returns an exception. If one result,
     * remove the password (if any) and return only one result.
     * @params  $result (array) results of authentication
     * @return  $result (array) cleaned up data.
     * */
    protected function _validateUserAuthentication($result) {
        if(count($result) < 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Access denied");
        else if(count($result) > 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Somethings's wrong. There is too many entries!");
        $result = $result[0];
        unset($result["password"]);
        return $result;
    }

    /*
     * Authentication with the Active Directory system. Check first if the user exists in opalDB. If not, no need to
     * make and external call and throw the exception. Then, prepare the settings for the AD by inserting username and
     * password. Wait for the answer, and if any problem, throw an exception. Otherwise, return the user info.
     * @params  $username (string) duh!
     *          $password (string) DUH!
     * @return  $result (array) details of the user info.
     * */
    protected function _userLoginActiveDirectory($username, $password) {
        $result = $this->opalDB->authenticateUserAD($username);
        $result = $this->_validateUserAuthentication($result);

        $settingsAD = json_encode(ACTIVE_DIRECTORY_SETTINGS);
        $settingsAD = str_replace("%%USERNAME%%", $username, $settingsAD);
        $settingsAD = str_replace("%%PASSWORD%%", $password, $settingsAD);
        $settingsAD = json_decode($settingsAD, true);

        $fieldString = "";
        foreach($settingsAD as $key=>$value) {
            $fieldString .= $key.'='.$value.'&';
        }
        $fieldString = substr($fieldString, 0, -1);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, ACTIVE_DIRECTORY["url"]);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fieldString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $requestResult = json_decode(curl_exec($ch),TRUE);
        curl_close($ch);

        if(!$requestResult["authenticate"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Access denied");

        return $result;
    }

    /*
     * Legacy authentication system when no AD is available. It validates the username and password directly into
     * opalDB after encrypting the password.
     * @params  $username (string) duh!
     *          $password (string) DUH!
     * @return  $result (array) details of the user info.
     * */
    protected function _userLoginLegacy($username, $password) {
        $result = $this->opalDB->authenticateUserLegacy($username, hash("sha256", $password . USER_SALT));
        $result = $this->_validateUserAuthentication($result);
        return $result;
    }

    /*
     * Validate the user and log its activity.
     * @param   $post (array) contains username, password and cypher
     * @return  $result (array) basic user informations
     * */
    public function userLogin($post) {
        $post = HelpSetup::arraySanitization($post);
        $cypher = $post["cypher"];
        $data = json_decode(Encrypt::encodeString( $post["encrypted"], $cypher), true);
        $data = HelpSetup::arraySanitization($data);
        $username = $data["username"];
        $password = $data["password"];

        if($username == "" || $password == "" || $cypher == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing login info.");

        if(AD_LOGIN_ACTIVE)
            $result = $this->_userLoginActiveDirectory($username, $password);
        else
            $result = $this->_userLoginLegacy($username, $password);

        $this->_connectAsMain($result["id"]);
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

        $result = $this->opalDB->authenticateUserLegacy($username, hash("sha256", $oldPassword . USER_SALT));
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
     * Decypher user information, validate its password before updating it, updating the language and the role. All
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

        if(!AD_LOGIN_ACTIVE) {
            if($data["password"] && $data["confirmPassword"]) {
                $result = $this->_passwordValidation($data["password"], $data["confirmPassword"]);
                if (count($result) > 0)
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));
                $this->opalDB->updateUserPassword($userDetails["serial"], hash("sha256", $data["password"] . USER_SALT));
            }
        }

        $newRole = $this->opalDB->geRoleDetails($data["roleId"]);
        if(!is_array($newRole))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid role.");

        if($data["roleId"] != $userDetails["RoleSerNum"]) {
            if($userDetails["serial"] == $this->opalDB->getOAUserId())
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An user cannot change its own role.");
            $this->opalDB->updateUserRole($data["id"], $data["roleId"]);
        }

        $this->opalDB->updateUserInfo($userDetails["serial"], $data["language"]);

        return true;
    }

    /*
     * insert a new user into the OAUser table and its role in OAUserRole table after sanitizing and validating the
     * data. Depending if the AD system is active or not, the insertion is done differently.
     * @params  $post (array) contains the username, password, confirmed password, role, language (all encrypted),
     *          cypher.
     * @returns void
     * */
    public function insertUser($post) {
        $post = HelpSetup::arraySanitization($post);
        $cypher = intval($post["cypher"]);
        if($cypher == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data to create user.");
        $data = json_decode(Encrypt::encodeString( $post["encrypted"], $cypher), true);
        $data = HelpSetup::arraySanitization($data);

        $username = $data["username"];
        $password = $data["password"];
        $confirmPassword = $data["confirmPassword"];
        $roleId = $data["roleId"];
        $language = strtoupper($data["language"]);

        if($username == "" || $roleId == "" || $language == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data to create user.");
        if($language != "FR" && $language != "EN")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Wrong language.");

        if(!AD_LOGIN_ACTIVE)
            $userId = $this->_insertUserLegacy($username, $password, $confirmPassword, $language);
        else
            $userId = $this->_insertUserAD($username, $language);

        $role = $this->opalDB->getRoleDetails($roleId);
        if(!is_array($role))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid role.");
        return $this->opalDB->insertUserRole($userId, $roleId);
    }

    /*
     * Insert an user with a password.
     * @params  $username (string) username (duh!)
     *          $password (string) password
     *          $confirmPassword (string) confirmation of the password
     *          $language (string) language of the user (EN, FR)
     * @return  userId (int) ID of the new user created
     * */
    protected function _insertUserLegacy($username, $password, $confirmPassword, $language) {
        if($password == "" || $confirmPassword == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data to create user.");

        $result = $this->_passwordValidation($password, $confirmPassword);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));

        return $this->opalDB->insertUser($username, hash("sha256", $password . USER_SALT), $language);
    }

    /*
     * Insert an user without a password. But to make sure there is somethign in the password field, the username is
     * used by default.
     * @params  $username (string) username (duh!)
     *          $language (string) language of the user (EN, FR)
     * @return  userId (int) ID of the new user created
     * */
    protected function _insertUserAD($username, $language) {
        return $this->opalDB->insertUser($username, hash("sha256", HelpSetup::generateRandomString() . USER_SALT), $language);
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
    public function usernameExists($username) {
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

    /*
     * Get the list of roles an user can have.
     * @params  void
     * @return  array with all roles found (not cronjob!)
     * */
    public function getRoles() {
        return $this->opalDB->getRoles();
    }

    /*
     * Get the activity logs of a specific user and determine if any data has being found.
     * @params  $userId (int) ID of the user
     * @return  $userLogs (array) all the logs of the specified user, with an extra field to specify if data was found
     * */
    public function getUserActivityLogs($userId) {
        $dataFound = false;
        $userLogs = array();
        $userLogs['login'] = $this->opalDB->getUserLoginDetails($userId);
        $userLogs['alias'] = $this->opalDB->getUserAliasDetails($userId);
        $userLogs['aliasExpression'] = $this->opalDB->getUserAliasExpressions($userId);
        $userLogs['diagnosisTranslation'] = $this->opalDB->getUserDiagnosisTranslations($userId);
        $userLogs['diagnosisCode'] = $this->opalDB->getUserDiagnosisCode($userId);
        $userLogs['email'] = $this->opalDB->getUserEmail($userId);
        $userLogs['trigger'] = $this->opalDB->getUserFilter($userId);
        $userLogs['hospitalMap'] = $this->opalDB->getUserHospitalMap($userId);
        $userLogs['post'] = $this->opalDB->getUserPost($userId);
        $userLogs['notification'] = $this->opalDB->getUserNotification($userId);
        $userLogs['legacyQuestionnaire'] = $this->opalDB->getUserQuestionnaire($userId);
        $userLogs['testResult'] = $this->opalDB->getUserTestResult($userId);
        $userLogs['testResultExpression'] = $this->opalDB->getUserTestResultExpression($userId);

        foreach($userLogs as $log){
            if(count($log) > 0) {
                $dataFound = true;
                break;
            }
        }
        $userLogs["isData"] = $dataFound;
        return $userLogs;
    }
}