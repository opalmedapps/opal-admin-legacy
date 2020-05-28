<?php

/*
 * Rolse class objects and method
 * */

class Role extends OpalProject {

    /*
     * This function returns the list of available studies for opalAdmin.
     * TODO add lazy loading with pagination
     * @params  void
     * @return  array of studies
     * */
    public function getRoles() {
        return $this->opalDB->getRoles();
    }

    /*
     * This function returns the list of available modules when creating a role. It returns the name (EN and FR), the
     * operation available (int of 0 to 8, coded on 3 bits, right column read access, middle column write access, left
     * column delete access) and the ID of the module.
     * @params  void
     * @return  array of available module
     * */
    public function getAvailableModules() {
        return $this->opalDB->getAvailableRolesModules();
    }

    /*
     * Inserts a new role. First, it sanitize the data, then it validate each operation and make sure the operation is
     * legal. Then it inserts the role in oaRole and add the operation to oaRoleModule.
     * @params  $post : array (details of the new role: name_EN, name_FR, and operations (ID and operation))
     * @return  void
     * */
    public function insertRole($post) {
        $recordsToInsert = array();
        $role = HelpSetup::arraySanitization($post);
        $result = $this->_validateRole($role);
        if(is_array($result) && count($result) > 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Study validation failed. " . implode(" ", $result));

        $toInsert = array(
            "name_EN"=>$role["name"]["name_EN"],
            "name_FR"=>$role["name"]["name_FR"],
        );
        $roleId = $this->opalDB->insertRole($toInsert);

        foreach ($role["operations"] as $item) {
            array_push($recordsToInsert, array(
                "moduleId"=>$item["ID"],
                "oaRoleId"=>$roleId,
                "access"=>$item["operation"],
            ));
        }
        $this->opalDB->insertRoleModule($recordsToInsert);
    }

    /*
     * function to sort module per ID for usort
     * */
    protected static function _sort_ID($a, $b){
        if (intval($a["ID"]) == intval($b["ID"])) return 0;
        return (intval($a["ID"]) < intval($b["ID"])) ? -1 : 1;
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
        usort($role["operations"], 'self::_sort_ID');
        foreach($role["operations"] as $module)
            array_push($modulesId, intval($module["ID"]));

        $modulesList = $this->opalDB->getModulesOperations($modulesId);
        if(count($modulesList) != count($role["operations"])) {
            array_push($errMsgs, "Modules are missing.");
            return $errMsgs;
        }

        for($cpt = 0;$cpt < count($role["operations"]); $cpt++) {
            if(!HelpSetup::validateBitOperation($modulesList[$cpt]["operation"], $role["operations"][$cpt]["operation"]) || ($role["operations"][$cpt]["operation"] != 1 && $role["operations"][$cpt]["operation"] != 3 && $role["operations"][$cpt]["operation"] != 7)) {
                array_push($errMsgs, "Unauthorized role.");
                break;
            }
        }
        return $errMsgs;
    }
}