<?php

/**
 * Diagnosis class
 */
class Diagnosis extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_DIAGNOSIS_TRANSLATION, $guestStatus);
    }

    /*
     * Get the details of a specific diagnosis translation, including diagnosis codes and educational material if
     * needed.
     * @params  $diagnosisId : int - ID of the diagnosis translation to get the details
     * @return  $result : array - all the details of the diagnosis translation
     * */
    public function getDiagnosisTranslationDetails($post) {
        $post = HelpSetup::arraySanitization($post);
        $this->checkReadAccess($post);
        $diagnosisId = $post["serial"];
        $result = $this->opalDB->getDiagnosisDetails($diagnosisId);
        $result["diagnoses"] = $this->opalDB->getDiagnosisCodes($diagnosisId);
        $result["count"] = count($result["diagnoses"]);

        if ($result["eduMatSer"] != 0) {
            $result["eduMat"] = $this->_getEducationalMaterialDetails($result["eduMatSer"]);
        }

        return $result;
    }

    /*
     * Get the list of current diagnosis with the codes already assigned.
     * @params  void
     * @return  $resuts : array - list of current diagnostics with already assigned codes
     * */
    public function getDiagnoses() {
        $this->checkReadAccess();

        $assignedDB = $this->_getActiveSourceDatabase();
        $ad = $this->opalDB->getAssignedDiagnoses();
        $assignedDiagnoses = array();
        foreach($ad as $item) {
            $assignedDiagnoses[$item["sourceuid"]] = $item;
        }
        $results = $this->opalDB->getDiagnoses($assignedDB);
        foreach ($results as &$item) {
            $item["added"] = 0;
            if ($assignedDiagnoses[$item["sourceuid"]])
                $item['assigned'] = $assignedDiagnoses[$item["sourceuid"]];
        }

        return $results;
    }

    /*
     * Validate and sanitze the new diagnosis translation received from the user
     * @params  $post : array - details of the diagnosis translation
     * @return  $validatedDiagnosis : array - validated and sanitized diagnosis translation
     * */
    protected function _validateAndSanitizeDiagnosis($post) {
        $post = HelpSetup::arraySanitization($post);
        $validatedDiagnosis = array();
        if(!$post["name_EN"] || !$post["name_FR"] || !$post["description_EN"] || !$post["description_FR"])
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Missing informations.");

        $validatedDiagnosis["name_EN"] = strip_tags($post["name_EN"]);
        $validatedDiagnosis["name_FR"] = strip_tags($post["name_FR"]);
        $validatedDiagnosis["description_EN"] = $post["description_EN"];
        $validatedDiagnosis["description_FR"] = $post["description_FR"];
        $validatedDiagnosis["eduMat"] = null;
        $validatedDiagnosis["diagnoses"] = array();

        if($post['eduMat'] && isset($post["eduMat"]["serial"])) {
            $tempEdu = $this->opalDB->validateEduMaterialId($post["eduMat"]["serial"]);
            if($tempEdu["total"] != "1")
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid educational material.");
            $validatedDiagnosis["eduMat"] = $post["eduMat"]["serial"];
        }

        if(!$post["diagnoses"] || !is_array($post["diagnoses"]))
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Diagnosis codes are missing.");

        foreach ($post["diagnoses"] as $item) {
            array_push($validatedDiagnosis["diagnoses"], array(
                "sourceuid"=>$item['sourceuid'],
                "code"=>$item['code'],
                "description"=>$item['description'],
            ));
        }

        return $validatedDiagnosis;
    }

    /*
     * Insert a new diagnosis translation after its sanitization and validation. It inserts (or replace) diagnosis
     * codes.
     * @params  $post : array - details of the diagnosis translation submitted by the user
     * @return  int - last Id inserted
     * */
    public function insertDiagnosisTranslation($post) {
        $this->checkWriteAccess($post);
        $post = HelpSetup::arraySanitization($post);
        $validatedPost = $this->_validateAndSanitizeDiagnosis($post);

        $toInsert = array(
            "Name_EN"=>$validatedPost["name_EN"],
            "Name_FR"=>$validatedPost["name_FR"],
            "Description_EN"=>$validatedPost["description_EN"],
            "Description_FR"=>$validatedPost["description_FR"],
            "EducationalMaterialControlSerNum"=>$validatedPost['eduMat'],
        );

        $diagnosisId = $this->opalDB->insertDiagnosisTranslation($toInsert);

        $diagnoses = $validatedPost["diagnoses"];
        $toInsert = array();

        foreach($diagnoses as $item) {
            array_push($toInsert, array(
                "DiagnosisTranslationSerNum"=>$diagnosisId,
                "SourceUID"=>$item['sourceuid'],
                "DiagnosisCode"=>$item['code'],
                "Description"=>$item['description'],
            ));
        }

        return $this->opalDB->insertMultipleDiagnosisCodes($toInsert);
    }

    /*
     * get the list of all the diagnosis translations.
     * @params  void
     * @return  array - list of diagnosis translations.
     * */
    public function getDiagnosisTranslations() {
        $this->checkReadAccess();
        return $this->opalDB->getDiagnosisTranslations();
    }

    /**
     *
     * Updates diagnosis translation details in the database
     *
     * @param array $diagnosisTranslationDetails : the diagnosis translation details
     * @return array : response
     */

    public function updateDiagnosisTranslation ($diagnosisTranslationDetails) {
        $this->checkWriteAccess($diagnosisTranslationDetails);

        $serial 			= $diagnosisTranslationDetails['serial'];
        $name_EN 			= $diagnosisTranslationDetails['name_EN'];
        $name_FR 			= $diagnosisTranslationDetails['name_FR'];
        $description_EN		= $diagnosisTranslationDetails['description_EN'];
        $description_FR		= $diagnosisTranslationDetails['description_FR'];
        $diagnoses 			= $diagnosisTranslationDetails['diagnoses'];
        $eduMatSer 			= $diagnosisTranslationDetails['edumatser'] ? $diagnosisTranslationDetails['edumatser'] : 'NULL';
        $userSer			= $diagnosisTranslationDetails['user']['id'];
        $sessionId 			= $diagnosisTranslationDetails['user']['sessionid'];

        $existingDiagnoses = array();

        $detailsUpdated 	= $diagnosisTranslationDetails['details_updated'];
        $codesUpdated 		= $diagnosisTranslationDetails['codes_updated'];

        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            if ($detailsUpdated) {
                $sql = "
					UPDATE
						DiagnosisTranslation
					SET
						DiagnosisTranslation.Name_EN 		= \"$name_EN\",
						DiagnosisTranslation.Name_FR 	 	= \"$name_FR\",
						DiagnosisTranslation.Description_EN = \"$description_EN\",
						DiagnosisTranslation.Description_FR = \"$description_FR\",
						DiagnosisTranslation.EducationalMaterialControlSerNum = $eduMatSer,
						DiagnosisTranslation.LastUpdatedBy 	= '$userSer',
						DiagnosisTranslation.SessionId 		= '$sessionId'
					WHERE
						DiagnosisTranslation.DiagnosisTranslationSerNum = $serial 
				";

                $query = $host_db_link->prepare( $sql );
                $query->execute();
            }

            if ($codesUpdated) {

                $sql = "
					SELECT DISTINCT
						dxc.SourceUID
					FROM
						DiagnosisCode dxc
					WHERE 
						dxc.DiagnosisTranslationSerNum = $serial 
				";
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    array_push($existingDiagnoses, $data[0]);
                }

                // If old diagnosis codes not in new diagnosis codes, delete from database
                foreach ($existingDiagnoses as $existingDiagnosis) {
                    if (!$this->nestedSearch($existingDiagnosis, $diagnoses)) {
                        $sql = "
	                        DELETE FROM
	                            DiagnosisCode
	                        WHERE
	                            DiagnosisCode.SourceUID = \"$existingDiagnosis\"
	                        AND DiagnosisCode.DiagnosisTranslationSerNum = $serial
	                    ";

                        $query = $host_db_link->prepare( $sql );
                        $query->execute();

                        $sql = "
                            UPDATE DiagnosisCodeMH
                            SET 
                                DiagnosisCodeMH.LastUpdatedBy = '$userSer',
                                DiagnosisCodeMH.SessionId = '$sessionId'
                            WHERE
                                DiagnosisCodeMH.SourceUID = \"$existingDiagnosis\"
                            ORDER BY DiagnosisCodeMH.RevSerNum DESC 
                            LIMIT 1
                        ";
                        $query = $host_db_link->prepare( $sql );
                        $query->execute();
                    }
                }

                // If new diagnosis codes, insert into database
                foreach ($diagnoses as $diagnosis) {
                    $sourceuid 		= $diagnosis['sourceuid'];
                    $code 			= $diagnosis['code'];
                    $description 	= $diagnosis['description'];
                    if(!in_array($sourceuid, $existingDiagnoses)) {
                        $sql = "
	                        INSERT INTO
	                            DiagnosisCode (
	                                DiagnosisTranslationSerNum,
	                                SourceUID,
	                                DiagnosisCode,
	                                Description,
	                                DateAdded,
	                                LastUpdatedBy,
	                                SessionId
	                            )
	                        VALUES (
	                            '$serial',
	                            '$sourceuid',
	                            \"$code\",
	                            \"$description\",
	                            NOW(),
	                            '$userSer',
	                            '$sessionId'
	                        )
	                        ON DUPLICATE KEY UPDATE
	                            DiagnosisTranslationSerNum = '$serial',
	                            LastUpdatedBy = '$userSer',
	                            SessionId = '$sessionId'
	                    ";

                        $query = $host_db_link->prepare( $sql );
                        $query->execute();
                    }
                }
            }
            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for diagnosis. " . $e->getMessage());
        }
    }

    /**
     *
     * Removes a diagnosis translation from the database
     *
     * @param integer $diagnosisTranslationSer : the serial number of the diagnosis translation
     * @param object $user : the session user
     * @return array $response : response
     */
    public function deleteDiagnosisTranslation ($diagnosisTranslationSer, $user) {
        $this->checkDeleteAccess(array($diagnosisTranslationSer, $user));

        $response = array(
            'value'     => 0,
            'message'   => ''
        );
        $userSer    = $user['id'];
        $sessionId  = $user['sessionid'];
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    DiagnosisCode
                WHERE
                    DiagnosisCode.DiagnosisTranslationSerNum = $diagnosisTranslationSer
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                DELETE FROM
                    DiagnosisTranslation
                WHERE
                    DiagnosisTranslation.DiagnosisTranslationSerNum = $diagnosisTranslationSer
            ";

            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                UPDATE DiagnosisTranslationMH
                SET 
                    DiagnosisTranslationMH.LastUpdatedBy = '$userSer',
                    DiagnosisTranslationMH.SessionId = '$sessionId'
                WHERE
                    DiagnosisTranslationMH.DiagnosisTranslationSerNum = $diagnosisTranslationSer
                ORDER BY DiagnosisTranslationMH.RevSerNum DESC 
                LIMIT 1
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for diagnosis. " . $e->getMessage());
        }
    }

    /**
     *
     * Does a nested search for match
     *
     * @param string $id    : the needle id
     * @param array $array  : the key-value haystack
     * @return boolean
     */
    public function nestedSearch($id, $array) {
        if(empty($array) || !$id){
            return 0;
        }
        foreach ($array as $key => $val) {
            if ($val['sourceuid'] === $id) {
                return 1;
            }
        }
        return 0;
    }

    /*
     * Get the list of educational materials available
     * @params  void
     * @return  array - List of educational materials
     * */
    public function getEducationalMaterials() {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
    }
}