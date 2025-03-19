<?php

/*
 * Rolse class objects and method
 * */

class Role extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_ROLE, $guestStatus);
    }

    /*
     * This function returns the list of available roles for opalAdmin.
     * TODO add lazy loading with pagination
     * @params  void
     * @return  array of studies
     * */
    public function getRoles() {
        $this->checkReadAccess();

        return $this->opalDB->getRoles();
    }

    /*
     * Return the details of a specific role and its list of operations.
     * @params  $post : array - contains the roleId
     * @returns $roleDetails : array - contains the role details (names and list of operations)
     * */
    public function getRoleDetails($roleId) {
        $this->checkReadAccess($roleId);

        $roleDetails = $this->opalDB->getRoleDetails($roleId);
        if(count($roleDetails) != 1)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot get role details.");
        $roleDetails = $roleDetails[0];

        $roleDetails["operations"] = $this->opalDB->getRoleOperations($roleId);
        if(count($roleDetails["operations"]) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot get role operations.");

        return $roleDetails;
    }

    /*
     * This function returns the list of available modules when creating a role. It returns the name (EN and FR), the
     * operation available (int of 0 to 8, coded on 3 bits, right column read access, middle column write access, left
     * column delete access) and the ID of the module.
     * @params  void
     * @return  array of available module
     * */
    public function getAvailableModules() {
        $this->checkReadAccess();

        return $this->opalDB->getAvailableRolesModules();
    }

    /*
     * Inserts a new role. First, it sanitize the data, then it validate each operation and make sure the operation is
     * legal. Then it inserts the role in oaRole and add the operation to oaRoleModule.
     * @params  $post : array (details of the new role: name_EN, name_FR, and operations (ID and operation))
     * @return  void
     * */
    public function insertRole($post) {
        $this->checkWriteAccess($post);

        $recordsToInsert = array();
        $role = HelpSetup::arraySanitization($post);
        $result = $this->_validateRole($role);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Role validation failed. " . implode(" ", $result));

        $toInsert = array(
            "name_EN"=>$role["name"]["name_EN"],
            "name_FR"=>$role["name"]["name_FR"],
        );
        $roleId = $this->opalDB->insertRole($toInsert);

        foreach ($role["operations"] as $item) {
            array_push($recordsToInsert, array(
                "moduleId"=>$item["moduleId"],
                "oaRoleId"=>$roleId,
                "access"=>$item["access"],
            ));
        }

        $this->opalDB->insertRoleModule($recordsToInsert);
    }

    /*
     * function to sort module per ID for usort
     * */
    protected static function _sort_moduleId($a, $b){
        if (intval($a["moduleId"]) == intval($b["moduleId"])) return 0;
        return (intval($a["moduleId"]) < intval($b["moduleId"])) ? -1 : 1;
    }

    /*
     * Validates a role to make sure it respect the proper structure.
     * 1) French and english name are mandatory.
     * 2) At least one operation is necessary.
     * 3) Operation 1 (read), 3 (read and write) and 7 (read, write and delete) are the only authorized ones
     * 4) operations are authorized in accordance with data stored in the module table.
     * In case of errors, store the error in an array and return it.
     * @params  $role : array - new role informations to validate
     * @return  $errMsgs : array - empty if no error found, other error messages.
     * */
    protected function _validateRole(&$role) {
        $errMsgs = array();

        if(!$role["name"] || $role["name"]["name_EN"] == "" || $role["name"]["name_EN"] == "")
            array_push($errMsgs, "Name is missing.");

        if(!$role["operations"] || count($role["operations"]) <= 0)
            array_push($errMsgs, "Operations are missing.");

        if(count($errMsgs) > 0) return $errMsgs;

        $modulesId = array();
        usort($role["operations"], 'self::_sort_moduleId');
        foreach($role["operations"] as $module)
            array_push($modulesId, intval($module["moduleId"]));

        $modulesList = $this->opalDB->getModulesOperations($modulesId);

        if(count($modulesList) != count($role["operations"])) {
            array_push($errMsgs, "Modules are missing.");
            return $errMsgs;
        }

        for($cpt = 0;$cpt < count($role["operations"]); $cpt++) {
            if(!HelpSetup::validateBitOperation($modulesList[$cpt]["operation"], $role["operations"][$cpt]["access"]) || ($role["operations"][$cpt]["access"] != 1 && $role["operations"][$cpt]["access"] != 3 && $role["operations"][$cpt]["access"] != 7)) {
                array_push($errMsgs, "Unauthorized role.");
                break;
            }
        }
        return $errMsgs;
    }

    /*
     * Update a role with new informations. First it insures the role to update exists, then it sanitize and validate
     * the data. It loads the current role details and split the details of the operations in three list: one of IDs
     * to keep (because the others do not exists anymore, thus they need to be deleted), one of IDs and access to
     * update, and one of operations to add. Lastly, even if oaRole was not updated because the names were not changed,
     * if there is any changes of operations, the oaRole table is forced to be updated to track down the changes.
     * @params  $post : array - requested changes from the user
     * @return  void
     * */
    public function updateRole($post) {
        $this->checkWriteAccess($post);

        $totalUpdated = 0;
        $optionsToKeep = array();
        $optionsToAdd = array();
        $optionsToUpdate = array();

        $roleToUpdate = HelpSetup::arraySanitization($post);
        if(!$roleToUpdate["roleId"] || $roleToUpdate["roleId"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing role ID.");
        $result = $this->_validateRole($roleToUpdate);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Role validation failed. " . implode(" ", $result));

        /*
         * If an user remove access to the role module for his own role, it may lock everybody with the same role out
         * of the role module. The next section of the code prevent that part.
         * */
        if($roleToUpdate["roleId"] == $_SESSION["role"]) {
            $accessRoleModule = $this->opalDB->getUserRoleModuleAccess($roleToUpdate["roleId"]);
            if (count($accessRoleModule) != 1)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot validate role access update.");
            $accessRoleModule = $accessRoleModule[0]["access"];

            $roleModuleFound = false;
            foreach($roleToUpdate["operations"] as $role) {
                if ($role["moduleId"] == MODULE_ROLE) {
                    $roleModuleFound = true;
                    if ($role["access"] != $accessRoleModule)
                        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "You cannot change access to the role module.");
                    break;
                }
            }
            if(!$roleModuleFound)
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "You cannot change access to the role module.");
        }

        $toUpdate = array(
            "ID"=>$roleToUpdate["roleId"],
            "name_EN"=>$roleToUpdate["name"]["name_EN"],
            "name_FR"=>$roleToUpdate["name"]["name_FR"],
        );

        $updatedRole = $this->opalDB->updateRole($toUpdate);

        $currentOperations = array();
        $tempCurr = $this->opalDB->getRoleOperations($roleToUpdate["roleId"]);
        if(count($tempCurr) <= 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Cannot get role operations.");

        foreach($tempCurr as $item) {
            $currentOperations[$item["moduleId"]] = $item;
        }

        foreach($roleToUpdate["operations"] as $sub) {
            if($currentOperations[$sub["moduleId"]] && $currentOperations[$sub["moduleId"]]["ID"] != "") {

                // operations to keep and not delete when the purge will occur
                array_push($optionsToKeep, $sub["moduleId"]);

                // operations to update
                if($currentOperations[$sub["moduleId"]]["access"] !== $sub["access"]) {
                    array_push($optionsToUpdate, array(
                        "ID" => $currentOperations[$sub["moduleId"]]["ID"],
                        "access" => $sub["access"],
                    ));
                }
            }
            else
                // operations to add
                array_push($optionsToAdd, array(
                    "oaRoleId"=>$roleToUpdate["roleId"],
                    "moduleId"=>$sub["moduleId"],
                    "access"=>$sub["access"],
                ));
        }

        if (!empty($optionsToKeep))
            $totalUpdated += $this->opalDB->deleteOARoleModuleOptions($roleToUpdate["roleId"], $optionsToKeep);

        if(!empty($optionsToUpdate))
            foreach($optionsToUpdate as $option)
                $totalUpdated += $this->opalDB->updateOARoleModule($option);

        if(!empty($optionsToAdd))
            $totalUpdated += $this->opalDB->insertOARoleModule($optionsToAdd);

        if(intval($updatedRole) <= 0 && intval($totalUpdated) >= 1)
            $this->opalDB->forceUpdateOaRoleTable($roleToUpdate["roleId"]);
    }

    /**
     * Update a user groups in the new backend by calling the endpoint `/api/users/username/(un)set-manager-user/`.
     * Use a predefined `NewOpalApiCall.php` library to perform the api call `PUT`. If the user in the backend
     * is not modified, it will display an error message to the user.
     * @param $post array - contains all the user info
     */
    public function updateUserGroupNewBackend($post) {
        $language = strtolower($_POST['language']);
        $roleToUpdate = HelpSetup::arraySanitization($post);

        // get involved users from DB
        $users_related_to_role = $this->opalDB->getUsersForRole($roleToUpdate["roleId"]);

        // check if patient module is in the updated operations
        $newbackend_action_name = 'unset-manager-user';
        foreach($roleToUpdate["operations"] as $sub) {
            // if users module added and access is READ/WRITE/DELETE
            if(isset($sub['moduleId']) && $sub['moduleId'] ===  json_encode(MODULE_USER)  && $sub['access'] >= (int) ACCESS_READ ){
                $newbackend_action_name = 'set-manager-user';
                // break if users module read/write/delete access right granted otherwise continue
                break;
            }
        }
        // TODO: consider making api call as an `async` function to avoid having to wait when there are many users.
        // iterate over updated users, make api request for each user to add/remove from managers group in new backend.
        foreach($users_related_to_role as $user) {
            $backendApi = new NewOpalApiCall(
                '/api/users/' . $user['username'] . '/' . $newbackend_action_name . '/',
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
    }

    /**
     * Mark a role as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the role table! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked and the user has the proper authorization.
     * Not following the proper procedure will have some serious impact on the integrity of the database and its
     * records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @params  $roleId : int - ID of the role
     * @return  int - number of record marked or error 500 if an error occurred.
     */
    public function deleteRole($roleId) {
        $this->checkDeleteAccess($roleId);

        $currentRole = $this->getRoleDetails($roleId);
        if(!$currentRole["ID"] || $currentRole["ID"] == "")
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Role not found.");
        if(intval($currentRole["total"]) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Role is still assigned and cannot be deleted.");

        return $this->opalDB->markRoleAsDeleted($roleId);
    }
}
