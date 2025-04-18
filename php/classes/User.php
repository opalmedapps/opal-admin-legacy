<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

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
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Access denied");
        }
        else if(count($result) > 1) {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Somethings's VERY wrong. There is too many entries!");
        }
        $result = $result[0];
        unset($result["password"]);
        return $result;
    }

    /**
     * Log user in on backend.
     *
     * @param $username the username
     * @param $password the password
     */
    protected function _loginBackend($username, $password) {
        $backendApi = new NewOpalApiCall(
            '/api/auth/login/',
            'POST',
            'en',
            json_encode([
                "username" => $username,
                "password" => $password,
            ]),
            'Content-Type: application/json',
        );

        $response = $backendApi->execute();

        // login failed
        if ($backendApi->getHttpCode() == HTTP_STATUS_BAD_REQUEST_ERROR && $backendApi->getError()) {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $backendApi->getError());
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Wrong username and/or password.");
        }
        // other errors
        else if ($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS && $backendApi->getError())
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
        else if ($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS) {
            HelpSetup::returnErrorMessage($backendApi->getHttpCode(), "Error from New Backend: " . $response["error"]);
        }

        return $backendApi;
    }

    /*
     * Authentication with the Active Directory system. Check first if the user exists in opalDB. If not, no need to
     * make and external call and throw the exception. Then, prepare the settings for the AD by inserting username and
     * password. Wait for the answer, and if any problem, throw an exception. Otherwise, return the user info.
     * @params  $username (string) duh!
     *          $password (string) DUH!
     * @return  $result (array) details of the user info.
     * */
    protected function _userLogin($username, $password) {
        // Ensure that the username exists
        $users = $this->opalDB->authenticateUserAD($username);
        if (count($users) != 1) {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Wrong username and/or password.");
        }

        $backendApi = $this->_loginBackend($username, $password);

        // pass the Set-Cookie headers to the user
        $headers = $backendApi->getHeaders()['set-cookie'];

        // append cookies from the backend response
        foreach ($headers as $cookie) {
            // Do not replace existing headers
            header("Set-Cookie: " . $cookie, false);
        }

        return $users[0];
    }

    /*
     * Call Active Directory system to check if the user exists prior to insertion when AD_ENABLED is `1`.
     * The API is supposed to respond an error, based on the error this function return .
     * @params  $username (string)
     *          $password (string)
     * @return  (boolean) true if user exists in AD system. False otherwise.
     * */
    protected function _checkUserActiveDirectory($username, $password) {

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

        $error_msg = $requestResult["error"];

        if ($error_msg == "Username not found")
            return false;
        else
            return true;
        }

   /*
    * Validate if user exists when `AD_ENABLED` is `1`.
    * @param $post (array) contains username
    * $result (boolean) if user exists it returns `true`, otherwise it returns `false`.
    * */
    public function isADUserExist($post) {
        $userAccess = array();
        $data = HelpSetup::arraySanitization($post);

        if(!is_array($data)) {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>"UNKNOWN USER"), ACCESS_DENIED, "UNKNOWN USER");
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $username = $data["username"];
        $password = $data["password"];

        // if username is empty log an error, no need to call external system.
        if($username == "") {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $result = $this->_checkUserActiveDirectory($username, $password);


        return $result;
    }

    /*
     * Validate the user, log its activity and build the nav menu to display.
     * @param   $post (array) contains username, password
     * @return  $result (array) basic user information
     * */
    public function userLogin($post) {
        $userAccess = array();
        $data = HelpSetup::arraySanitization($post);

        if(!is_array($data)) {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>"UNKNOWN USER"), ACCESS_DENIED, "UNKNOWN USER");
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $username = $data["username"];
        $password = $data["password"];

        if($username == "" || $password == "") {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $result = $this->_userLogin($username, $password);

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
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "No access found. Please contact your administrator.");
        }
        foreach($tempAccess as $access) {
            if(!HelpSetup::validateBitOperation($access["operation"],$access["access"])) {
                HelpSetup::getModuleMethodName($moduleName, $methodName);
                $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
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
        HelpSetup::getModuleMethodName($moduleName, $methodName);
        $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_GRANTED);

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
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>"UNKNOWN USER"), ACCESS_DENIED, "UNKNOWN USER");
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $username = $data["username"];
        $password = $data["password"];

        if($username == "" || $password == "") {
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Missing login info.");
        }

        $result = $this->opalDB->authenticateSystemUser($username);
        $result = $this->_validateUserAuthentication($result, $username);
        $backendApi = $this->_loginBackend($username, $password);
        // don't pass the set-cookie headers to the response since the system user only needs a session on opaladmin

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
            HelpSetup::getModuleMethodName($moduleName, $methodName);
            $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "No access found. Please contact your administrator.");
        }
        foreach($tempAccess as $access) {
            if(!HelpSetup::validateBitOperation($access["operation"],$access["access"])) {
                HelpSetup::getModuleMethodName($moduleName, $methodName);
                $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_DENIED, $username);
                HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access violation role-module. Please contact your administrator.");
            }
            $userAccess[$access["ID"]] = array("ID"=>$access["ID"], "access"=>$access["access"]);
        }

        $_SESSION["userAccess"] = $userAccess;
        $result["sessionid"] = $_SESSION['sessionId'];

        $this->_logActivity($result["id"], $_SESSION['sessionId'], 'Login');

        //Insert in the audit table user was granted access and return nav menu
        HelpSetup::getModuleMethodName($moduleName, $methodName);
        $this->_insertAudit($moduleName, $methodName, array("username"=>$username), ACCESS_GRANTED);

        return $result;
    }

    /*
     * Logs the user out by logging it in the logActivity.
     * @params  void
     * @return  answer from the log activity
     * */
    public function userLogout() {
        HelpSetup::getModuleMethodName($moduleName, $methodName);
        $this->_insertAudit($moduleName, $methodName, array(), ACCESS_GRANTED);
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
    protected function _passwordValidation($password, $confirmPassword) {
        $errMsgs = array();
        if($confirmPassword != $password)
            array_push($errMsgs, "Password and password confirmation do not match.");

        $length = (strlen($password) >= 12);
        $special = preg_match('#[\W]+#', $password);
        $number = preg_match("#[0-9]+#",$password);
        $upper = preg_match("#[A-Z]+#",$password);
        $lower = preg_match("#[a-z]+#",$password);

        if(!$length || !$special || !$number || !$upper || !$lower)
            array_push($errMsgs, "Invalid password format.");

        return $errMsgs;
    }

    /*
     * Updates the password of the current user after validating it.
     * @param   $post (array) array of data coming from the frontend that contains username, password and confirm
     *          password.
     * @return  number of updated record
     * */
    public function updatePassword($post) {
        $post = HelpSetup::arraySanitization($post);
        HelpSetup::getModuleMethodName($moduleName, $methodName);
        $this->_insertAudit($moduleName, $methodName, $post, ACCESS_GRANTED);

        $username = $_SESSION["username"];
        $oldPassword = $post["oldPassword"];
        $password = $post["password"];
        $confirmPassword = $post["confirmPassword"];

        if($username == "" || $password == "" || $oldPassword == "" || $confirmPassword == "" || $password == $oldPassword)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing update information.");

        $result = $this->_passwordValidation($password, $confirmPassword);
        if(count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));

        $this->_loginBackend($username, $oldPassword);

        $backendApi = new NewOpalApiCall(
            '/api/auth/password/change/',
            'POST',
            'en',
            json_encode([
                "new_password1" => $password,
                "new_password2" => $confirmPassword,
            ]),
            'Content-Type: application/json',
        );

        $response = $backendApi->execute();

        // login failed
        if ($backendApi->getHttpCode() == HTTP_STATUS_BAD_REQUEST_ERROR && $backendApi->getError()) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_NOT_AUTHENTICATED_ERROR, "Update failed.");
        }
        // other errors
        else if ($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS && $backendApi->getError())
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
        else if ($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS) {
            HelpSetup::returnErrorMessage($backendApi->getHttpCode(), "Error from New Backend: " . $response["error"]);
        }

        return 1;
    }

    /*
     * Update the preferred language on how to display the opalAdmin to the user. Can only accept FR or EN
     * @params  $post (array) contains language requested and userId
     * @returns number of records modified
     * */
    public function updateLanguage($post) {
        $post = HelpSetup::arraySanitization($post);
        HelpSetup::getModuleMethodName($moduleName, $methodName);
        $this->_insertAudit($moduleName, $methodName, $post, ACCESS_GRANTED);

        $post["language"] = strtoupper($post["language"]);

        if($post["language"] != "EN" && $post["language"] != "FR")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid language");

        $this->opalDB->updateUserLanguage($this->opalDB->getOAUserId(), $post["language"]);

        return true;
    }

    /*
     * validate its password before updating it, updating the language and the role. All the updates are optionals.
     * @oarams  $post (array) information on the user and the id.
     * @return  true (boolean) means the update was successful.
     * */
    public function updateUser($post) {
        $data = HelpSetup::arraySanitization($post);
        $this->checkWriteAccess(array("userId"=>$data["id"], "roleId"=>$data["roleId"], "language"=>$data["language"]));

        $userDetails = $this->opalDB->getUserDetails($data["id"]);

        if(!is_array($userDetails))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid user.");

        if($data["password"] && $data["confirmPassword"]) {
            $result = $this->_passwordValidation($data["password"], $data["confirmPassword"]);
            if (count($result) > 0)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Password validation failed. " . implode(" ", $result));
        }

        $newRole = $this->opalDB->getRoleDetails($data["roleId"]);
        if(!is_array($newRole))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid role.");

        if($data["roleId"] != $userDetails["oaRoleId"] && $userDetails["serial"] == $this->opalDB->getOAUserId())
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "An user cannot change its own role.");

        $this->opalDB->updateUserInfo($userDetails["serial"], $data["language"], $data["roleId"]);

        // call new backend api function to update the users and their groups
        $this->_updateUserNewBackend($post);

        // call new backend api function to update user manager group according to the role assigned to them
        $this->_checkUpdateUserPrivilege($post);

        return true;
    }

    /**
     * Update a user into the new backend by calling the endpoint `/api/users/username`. Use a predefined `NewOpalApiCall.php`
     * library to perform the api call `PUT`. If the user in the backend is not added, it will display an error message to the
     * user.
     * @param $post array - contains all the user info
     */
    protected function _updateUserNewBackend($post) {
        $language = strtolower($post['language']);

        $payload = [
            "username" => $post['edited_username'],
        ];

        if (!empty($post["password"]) && !empty($post["confirmPassword"])) {
            $payload["password"] = $post['password'];
            $payload["password2"] = $post['confirmPassword'];
        }

        // check if no groups are selected by the user
        if(!empty($post['selected_additionalprivileges']['groups'])) {
            $payload["groups"] = $post['selected_additionalprivileges']['groups'];
        } else {
            $payload["groups"] = [];
        }

        // set the payload in json format
        $json_payload= json_encode($payload);
        $backendApi = new NewOpalApiCall(
            '/api/users/' . $payload['username'] . '/',
            'PUT',
            $language,
            $json_payload,
            'Content-Type: application/json',
        );

        $response = $backendApi->execute(); // response is string json

        if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS && $backendApi->getError())
             HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
        else if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS) {
            HelpSetup::returnErrorMessage($backendApi->getHttpCode(), "Error from New Backend: " . $response["error"]);
        }
    }

    /**
     * Update a user privilege in the new backend by calling the endpoint `/api/users/username/(un)set-manager-user/`.
     * if the user being updated has a role that has users `Read/Write/Delete` privilege set the user as
     * manager user in new backend. Otherwise, remove them from manager users group.
     * @param $post array - contains all the user info
     */
    protected function _checkUpdateUserPrivilege($post) {
        $language = strtolower($post['language']);
        $user_to_update = HelpSetup::arraySanitization($post);
        $username = "";

        // when it comes from updating user the key is `edited_username` if it is from insert user key is `username`
        if(isset($user_to_update['edited_username'])){
            $username = $user_to_update['edited_username'];
        }else{
            $username = $user_to_update['username'];
        }

        // get operations related to the role
        $role_operations = $this->opalDB->getRoleOperations($user_to_update["roleId"]);

        // check if users module is in the updated operations
        $newbackend_action_name = 'unset-manager-user';
        foreach($role_operations as $sub) {
            // if user module added and access is READ/WRITE/DELETE
            if(isset($sub['moduleId']) && $sub['moduleId'] ==  json_encode(MODULE_USER)  && $sub['access'] >= (int) ACCESS_READ ){
                $newbackend_action_name = 'set-manager-user';
                // break if users module read/write/delete access right granted otherwise continue
                break;
            }
        }

        // make api request for the edited user to add/remove from managers group in new backend.
        $backendApi = new NewOpalApiCall(
            '/api/users/' . $username . '/' . $newbackend_action_name . '/',
            'PUT',
            $language,
            '',
            'Content-Type: application/json',
        );

        $response = $backendApi->execute(); // response is string json

        if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS && $backendApi->getError())
             HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
        else if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS) {
            HelpSetup::returnErrorMessage($backendApi->getHttpCode(), "Error from New Backend: " . $response["error"]);
        }
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

        $this->_prepareUserWithPassword($type, $username, $password, $confirmPassword, $language, $roleId, $isInsert);
        // call new backend api function to insert the new users and their groups
        $this->_insertUserNewBackend($post);
        // call new backend api function to update user manager group according to the role assigned to them
        $this->_checkUpdateUserPrivilege($post);
    }
    /**
     * Insert a new user into the new backend by calling the endpoint `/api/users/`. Use a predefined `NewOpalApiCall.php`
     * library to perform the api call. If the user in the backend is not added, it will display an error message to the
     * user.
     * @param $post array - contains all the user info
     */
    protected function _insertUserNewBackend($post) {
        $language = strtolower($post['language']);
        $payload = [
            "username" => $post['username'],
            "password" => $post['password'],
            "password2" => $post['confirmPassword'],
        ];

        // check if no groups are selected by the user
        if(!empty($post['additionalprivileges']['groups'])) {
            $payload["groups"] = $post['additionalprivileges']['groups'];
        } else {
            $payload["groups"] = [];
        }
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

        if ($backendApi->getHttpCode() != HTTP_STATUS_CREATED && $backendApi->getError()){
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
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

        $this->_insertUpdateUser($type, $username, $language, $roleId, $isInsert);
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
    protected function _insertUpdateUser($type, $username, $language, $roleId, $isInsert = false) {
        if($isInsert)
            // use a random password instead of a blank password
            $this->opalDB->insertUser($type, $username, hash("sha256", base64_encode(random_bytes(20)) . base64_encode(random_bytes(20))), $language, $roleId);
        else
            $this->opalDB->updateUser($type, $username, $language, $roleId);
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

        // when user is marked as deleted successfully in the legacy opaladmin, call new backend api to deactivate user
        $this->_deleteUserNewBackend($_POST);
    }

    /**
     * Call new backend endpoint to deactivate the user when deleted, if exists.
     *
     * The endpoint that will be called is `api/users/username/deactivate-user/`
     *
     * @params  $post (array) data receive from the front in $_POST method
     * @return void or api response if fails to accomplish the deactivation.
     */
    protected function _deleteUserNewBackend($post) {
        $post = HelpSetup::arraySanitization($post);

        $username = strip_tags($post["username"]);

         $backendApi = new NewOpalApiCall(
                '/api/users/' . $username . '/' . 'deactivate-user/',
                'PUT',
                'en',
                '',
                'Content-Type: application/json',
            );

            $response = $backendApi->execute(); // response is string json

            if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS && $backendApi->getError())
                 HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_GATEWAY,"Unable to connect to New Backend " . $backendApi->getError());
            else if($backendApi->getHttpCode() != HTTP_STATUS_SUCCESS) {
                HelpSetup::returnErrorMessage($backendApi->getHttpCode(), "Error from New Backend: " . $response["error"]);
            }
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
