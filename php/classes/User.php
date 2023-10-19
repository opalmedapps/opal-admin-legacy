<?php
/**
 * User class to validate its identity and access levels
 */
include_once("ApiCall.php");
include_once("NewOpalApiCall.php");

class User extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_USER, $guestStatus);
    }

    /*
     * Validate the number of results of authentication. If different than one, returns an exception. If one result,
     * remove the password (if any) and return only one result.
     * @params  $result (array) results of authentication
     * @return  $result (array) cleaned up data.
     * */
    protected function _validateUserAuthentication($result, $username) {
        if(count($result) < 1) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Access denied");
        }
        else if(count($result) > 1) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Somethings's VERY wrong. There is too many entries!");
        }
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
        $result = $this->_validateUserAuthentication($result, $username);

        $settingsAD = json_encode(ACTIVE_DIRECTORY_SETTINGS);
        $settingsAD = str_replace("%%USERNAME%%", $username, $settingsAD);
        $settingsAD = str_replace("%%PASSWORD%%", $password, $settingsAD);
        $settingsAD = json_decode($settingsAD, true);

        $fieldString = "";
        foreach($settingsAD as $key=>$value) {
            $fieldString .= $key.'='.urlencode($value).'&';
        }
        $fieldString = substr($fieldString, 0, -1);

        $api = new ApiCall(MSSS_ACTIVE_DIRECTORY_CONFIG);
        $api->setPostFields($fieldString);
        $api->execute();
        $requestResult = json_decode($api->getAnswer(), true);

        if(!$requestResult["authenticate"]) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Wrong username and/or password.");
        } else if (count($this->opalDB->authenticateUserAccess($username)) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Wrong username and/or password.");

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
        $result = $this->_validateUserAuthentication($result, $username);
        return $result;
    }

    /*
     * Validate the user, log its activity and build the nav menu to display.
     * @param   $post (array) contains username, password
     * @return  $result (array) basic user informations
     * */
    public function userLogin($post) {
        $userAccess = array();
        $data = HelpSetup::arraySanitization($post);

        if(!is_array($data)) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>"UNKNOWN USER"), ACCESS_DENIED, "UNKNOWN USER");
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $username = $data["username"];
        $password = $data["password"];

        if($username == "" || $password == "") {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        if(AD_LOGIN_ACTIVE)
            $result = $this->_userLoginActiveDirectory($username, $password);
        else
            $result = $this->_userLoginLegacy($username, $password);

        $_SESSION["ID"] = $result["id"];
        $_SESSION["username"] = $result["username"];
        $_SESSION["language"] = $result["language"];
        $_SESSION["role"] = $result["role"];
        $_SESSION["type"] = $result["type"];
        $_SESSION['sessionId'] = HelpSetup::makeSessionId();
        $_SESSION['lastActivity'] = time();
        $_SESSION['created'] = time();

        $this->_connectAsMain();
        $tempAccess = $this->opalDB->getUserAccess($result["role"]);
        if(count($tempAccess) <= 0) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "No access found. Please contact your administrator.");
        }
        foreach($tempAccess as $access) {
            if(!HelpSetup::validateBitOperation($access["operation"],$access["access"])) {
                HelpSetup::getModuleMethodName($moduleName, $methodeName);
                $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
                HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access violation role-module. Please contact your administrator.");
            }
            $userAccess[$access["ID"]] = array("ID"=>$access["ID"], "access"=>$access["access"]);
        }

        $newMenu = array();
        $subMenu = array();
        $menuDB = $this->opalDB->getCategoryNavMenu();

        /*
         * Builds the nav menus the user can see based on its role
         * */
        foreach ($menuDB as $category) {
            $menuList = $this->opalDB->getNavMenu($category["ID"]);
            if(count($menuList) > 0) {
                $temp = $category;
                $temp["menu"] = array();
                foreach($menuList as $menu) {
                    if(intval($menu["subModuleMenu"]) && $menu["subModule"] != "") {
                        $subMenu[$menu["ID"]] = json_decode($menu["subModule"]);
                    }
                    if(((intval($menu["operation"]) >> 0) & 1) && ((intval($userAccess[$menu["ID"]]["access"]) >> 0) & 1)) {
                        array_push($temp["menu"], array("ID"=>$menu["ID"], "operation"=>$menu["operation"], "name_EN"=>$menu["name_EN"], "name_FR"=>$menu["name_FR"], "description_EN"=>$menu["description_EN"], "description_FR"=>$menu["description_FR"], "iconClass"=>$menu["iconClass"], "url"=>$menu["url"]));
                    }
                }
                array_push($newMenu, $temp);
            }
        }

        $_SESSION["userAccess"] = $userAccess;
        $_SESSION["navMenu"] = $newMenu;
        $_SESSION["subMenu"] = $subMenu;
        $result["sessionid"] = $_SESSION['sessionId'];

        $toReturn["user"] = $result;
        $toReturn["access"] = $_SESSION["userAccess"];
        $toReturn["menu"] = $_SESSION["navMenu"];
        $toReturn["subMenu"] = $_SESSION["subMenu"];
        $this->_logActivity($result["id"], $_SESSION['sessionId'], 'Login');

        //Insert in the audit table user was granted access and return nav menu
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_GRANTED);

        return $toReturn;
    }

    /**
     * Login for a system (non-human) user. It validates the user/name password, stored in the sessions, the access
     * level and user info. It returns an array that contains user info (ID, username, language, role and sessionID)
     * @param $post : array - contains username and password
     * @return mixed : array - contains system user ID, username, language, role and sessionID
     */
    public function systemUserLogin($post) {
        $userAccess = array();
        $data = HelpSetup::arraySanitization($post);

        if(!is_array($data)) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>"UNKNOWN USER"), ACCESS_DENIED, "UNKNOWN USER");
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $username = $data["username"];
        $password = $data["password"];

        if($username == "" || $password == "") {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $result = $this->opalDB->authenticateSystemUser($username, hash("sha256", $password . USER_SALT));
        $result = $this->_validateUserAuthentication($result, $username);

        $_SESSION["ID"] = $result["id"];
        $_SESSION["username"] = $result["username"];
        $_SESSION["language"] = $result["language"];
        $_SESSION["role"] = $result["role"];
        $_SESSION['sessionId'] = HelpSetup::makeSessionId();
        $_SESSION['lastActivity'] = time();
        $_SESSION['created'] = time();

        $this->_connectAsMain();
        $tempAccess = $this->opalDB->getUserAccess($result["role"]);
        if(count($tempAccess) <= 0) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "No access found. Please contact your administrator.");
        }
        foreach($tempAccess as $access) {
            if(!HelpSetup::validateBitOperation($access["operation"],$access["access"])) {
                HelpSetup::getModuleMethodName($moduleName, $methodeName);
                $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
                HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access violation role-module. Please contact your administrator.");
            }
            $userAccess[$access["ID"]] = array("ID"=>$access["ID"], "access"=>$access["access"]);
        }

        $_SESSION["userAccess"] = $userAccess;
        $result["sessionid"] = $_SESSION['sessionId'];

        $this->_logActivity($result["id"], $_SESSION['sessionId'], 'Login');

        //Insert in the audit table user was granted access and return nav menu
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_GRANTED);

        return $result;
    }

    /*
     * Login for registration of patient. Before each inscription, the login of the user must be validated. Validation
     * is made base on the Patient module write access. If the user got a valid user/pass and correct access level
     * (read and write), then it is authorized to proceed. Otherwise, return error 401. No matter what, logs the result
     * in the audit table.
     * @param   $post (array) contains username, password
     * @return  200 (success), 401 (denied) or 500 (server error, oops!)
     * */
    public function userLoginRegistration($post) {
        $data = HelpSetup::arraySanitization($post);

        if(!is_array($data)) {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>"UNKNOWN USER"), ACCESS_DENIED, "UNKNOWN USER");
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $username = $data["username"];
        $password = $data["password"];

        if($username == "" || $password == "") {
            HelpSetup::getModuleMethodName($moduleName, $methodeName);
            $this->_insertAudit($moduleName, $methodeName, array("username"=>($data["username"] ? $data["username"] : "UNKNOWN USER")), ACCESS_DENIED, ($data["username"] ? $data["username"] : "UNKNOWN USER"));
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        if(AD_LOGIN_ACTIVE)
            $resultUser = $this->_userLoginActiveDirectory($username, $password);
        else
            $resultUser = $this->_userLoginLegacy($username, $password);

        $this->_connectAsMain($resultUser["id"]);

        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        $result = $this->opalDB->getUserAccessRegistration($resultUser["role"]);

        if(count($result) != 1) {
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "No access found. Please contact your administrator.");
        }
        $result = $result[0];

        if(!(($result["access"] >> 1) & 1) || !(($result["operation"] >> 1) & 1)) {
            $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Access denied.");
        }

        $this->_logActivity($resultUser["id"], HelpSetup::makeSessionId(), 'LoginRegistration');
        $this->_insertAudit($moduleName, $methodeName, array("username"=>$username), ACCESS_GRANTED, $username);
        return HTTP_STATUS_SUCCESS;
    }

    /*
     * Logs the user out by logging it in the logActivity.
     * @params  void
     * @return  answer from the log activity
     * */
    public function userLogout() {
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        $this->_insertAudit($moduleName, $methodeName, array(), ACCESS_GRANTED);
        $result = $this->_logActivity($_SESSION["ID"], $_SESSION["sessionId"], 'Logout');
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
        return $result;
    }

    /*
     * Log activity into the table OAActivityLog.
     * @params  $userId (int) ID of the user to enter
     *          $sessionId (string) session ID of the user
     *          $activity (string) type of activity to log in (Login or Logout)
     * */
    protected function _logActivity($userId, $sessionId, $activity) {
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
     * @param   $post (array) array of data coming from the frontend that contains username, password and confirm
     *          password.
     * @return  number of updated record
     * */
    public function updatePassword($post) {
        $this->checkWriteAccess(ENCRYPTED_DATA);
        $post = HelpSetup::arraySanitization($post);

        $username = $this->opalDB->getUserDetails($post["OAUserId"]);
        $username = $username["username"];
        $oldPassword = $post["oldPassword"];
        $password = $post["password"];
        $confirmPassword = $post["confirmPassword"];

        if($username == "" || $password == "" || $oldPassword == "" || $confirmPassword == "" || $password == $oldPassword)
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
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        $this->_insertAudit($moduleName, $methodeName, HelpSetup::arraySanitization($post), ACCESS_GRANTED);

        $post = HelpSetup::arraySanitization($post);
        $post["language"] = strtoupper($post["language"]);

        if($post["language"] != "EN" && $post["language"] != "FR")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid language");

        $this->opalDB->updateUserLanguage($this->opalDB->getOAUserId(), $post["language"]);

        return true;
    }

    /*
     * validate its password before updating it, updating the language and the role. All the updates are optionals.
     * @oarams  $post (array) informations on the user and the id.
     * @return  true (boolean) means the update was successful.
     * */
    public function updateUser($post) {
        $data = HelpSetup::arraySanitization($post);
        $this->checkWriteAccess(array("userId"=>$data["id"], "roleId"=>$data["roleId"], "language"=>$data["language"]));

        $userDetails = $this->opalDB->getUserDetails($data["id"]);

        if(!is_array($userDetails))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid user.");

        if(!AD_LOGIN_ACTIVE || intval($userDetails["type"]) == 2) {
            if($data["password"] && $data["confirmPassword"]) {
                $result = $this->_passwordValidation($data["password"], $data["confirmPassword"]);
                if (count($result) > 0)
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));
                $this->opalDB->updateUserPassword($userDetails["serial"], hash("sha256", $data["password"] . USER_SALT));
            }
        }

        $newRole = $this->opalDB->getRoleDetails($data["roleId"]);
        if(!is_array($newRole))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid role.");

        if($data["roleId"] != $userDetails["oaRoleId"] && $userDetails["serial"] == $this->opalDB->getOAUserId())
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An user cannot change its own role.");

        $this->opalDB->updateUserInfo($userDetails["serial"], $data["language"], $data["roleId"]);

        return true;
    }

    /**
     * Insert a new user into the OAUser table after sanitizing and validating the data. Depending if the AD system is
     * active or not, the insertion is done differently. Also, if the user already exists but was deleted, the record
     * will be undeleted and updated.
     * @param $post array - contains all the user info
     */
    public function insertUser($post) {
        $data = HelpSetup::arraySanitization($post);
        $this->checkWriteAccess(array("username"=>$data["username"], "roleId"=>$data["roleId"], "language"=>strtoupper($data["language"])));

        $username = $data["username"];
        $password = $data["password"];
        $confirmPassword = $data["confirmPassword"];
        $roleId = $data["roleId"];
        $language = strtoupper($data["language"]);
        $type = intval($data["type"]);

        if($username == "" || $roleId == "" || $language == "" || ($type != 1 && $type != 2))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data to create user.");
        if($language != "FR" && $language != "EN")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Wrong language.");

        $currentUser = $this->opalDB->isUserExists($username);

        if (count($currentUser) < 1)
            $isInsert = true;
        else if (count($currentUser) == 1 && $currentUser[0]["deleted"] == DELETED_RECORD)
            $isInsert = false;
        else
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicate usernames found.");

        if (!AD_LOGIN_ACTIVE || $type == 2)
            $this->_prepareUserWithPassword($type, $username, $password, $confirmPassword, $language, $roleId, $isInsert);
        else
            $this->_insertUpdateUser($type, $username, $language, HelpSetup::generateRandomString(), $roleId, $isInsert);
    }
    /**
     * Insert a new user into the new backend by calling the endpoint `/api/users/`. Use a predefined `NewOpalApiCall.php`
     * library to perform the api call. If the user in the backend is not added, it will display an error message to the
     * user.
     * @param $post array - contains all the user info
     */
    public function insertUserNewBackend($post) {
        $language = strtolower($_POST['language']);
        // check if no groups are selected by the user
        if(!empty($_POST['additionalprivileges']['groups']))
            $payload = [
                "username"=>$_POST['username'],
                "groups"=>$_POST['additionalprivileges']['groups'],
            ];
        else
            $payload = [
                "username"=>$_POST['username'],
                "groups"=>[],
            ];
        // set the payload in json format
        $json_payload= json_encode($payload);

        $backendApi = new NewOpalApiCall(
            '/api/users/',
            'POST',
            $language,
            $json_payload,
            'Content-Type: application/json',
        );

        $response = $backendApi->execute(); // response is string json

        if($backendApi->getHttpCode() != HTTP_STATUS_CREATED && $backendApi->getError())
             HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
        else if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS) {
            HelpSetup::returnErrorMessage($backendApi->getHttpCode(), "Error from New Backend: " . $response["error"]);
        }
    }
    /**
     * Insert an user with a password because the AD system is inactive or N/A, or the user is a third party system.
     * @param $type int - type of user (human/system)
     * @param $username string - username of the user
     * @param $password string - password requested for the user
     * @param $confirmPassword string - confirmation of the password to make sure there's no typo 
     * @param $language string - preferred language (en/fr)
     * @param $roleId int - role of the user
     * @param $isInsert boolean - if the process is an insert new user or update a deactivated user
     */
    protected function _prepareUserWithPassword($type, $username, $password, $confirmPassword, $language, $roleId, $isInsert = false) {
        if($password == "" || $confirmPassword == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing data to create user.");

        $result = $this->_passwordValidation($password, $confirmPassword);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));

        $this->_insertUpdateUser($type, $username, $language, $password, $roleId, $isInsert);
    }

    /**
     * Insert or update an user
     * used by default.
     * @param $type int - type of user (human/system)
     * @param $username string - username of the user
     * @param $language string - preferred language (en/fr)
     * @param $roleId int - role of the user
     * @param $isInsert boolean - if the process is an insert new user or update a deactivated user
     */
    protected function _insertUpdateUser($type, $username, $language, $password, $roleId, $isInsert = false) {
        if($isInsert)
            $this->opalDB->insertUser($type, $username, hash("sha256", $password . USER_SALT), $language, $roleId);
        else
            $this->opalDB->updateUser($type, $username, hash("sha256", $password . USER_SALT), $language, $roleId);
    }

    /*
     * Get the list of all users excluding the cronjob one
     * @params  void
     * @return  array of users
     * */
    public function getUsers() {
        $this->checkReadAccess();
        return $this->opalDB->getUsersList();
    }

    /*
     * Get users details based on its ID. Format the data in a way the front won't crash. It sends username, role info,
     * and language.
     * @params  $post (array) data receive from the front in $_POST method
     * @returns $userDetails (array) details of the user
     * */
    public function getUserDetails($post) {
        $this->checkReadAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $userDetails = $this->opalDB->getUserDetails($post["userId"]);
        $userDetails["role"] = array("serial"=>$userDetails["oaRoleId"], "name_EN"=>$userDetails["name_EN"], "name_FR"=>$userDetails["name_FR"]);
        $userDetails["logs"] = array();
        $userDetails["new_password"] = null;
        $userDetails["confirm_password"] = null;
        unset($userDetails["oaRoleId"]);
        unset($userDetails["name_EN"]);
        unset($userDetails["name_FR"]);
        return $userDetails;
    }

    /*
     * returns if the username is already in use or not
     * @params  $username (string)
     * @return  boolean if the result is greater than 0 or not
     * */
    public function usernameExists($username) {
        $this->checkReadAccess($username);
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
        $this->checkDeleteAccess($userId);
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
        $this->checkReadAccess();
        return $this->opalDB->getRoles();
    }

    /*
     * Get the activity logs of a specific user and determine if any data has being found.
     * @params  $userId (int) ID of the user
     * @return  $userLogs (array) all the logs of the specified user, with an extra field to specify if data was found
     * */
    public function getUserActivityLogs($userId) {
        $this->checkReadAccess($userId);
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