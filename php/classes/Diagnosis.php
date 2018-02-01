<?php

/**
 * Diagnosis class
 *
 */
class Diagnosis {

	/**
     *
     * Gets details on a particular diagnosis translation
     *
     * @param integer $serial : the serial number of the diagnosis translation
     * @return array $diagnosisTranslationDetails : the diagnosis translation details
     */
    public function getDiagnosisTranslationDetails ($serial) {
        $diagnosisTranslationDetails = array();
 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					dxt.Name_EN,
					dxt.Name_FR,
					dxt.Description_EN,
					dxt.Description_FR,
					dxt.EducationalMaterialControlSerNum
				FROM
					DiagnosisTranslation dxt
				WHERE
					dxt.DiagnosisTranslationSerNum = $serial
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$name_EN        = $data[0];
            $name_FR        = $data[1];
			$description_EN = $data[2];
			$description_FR = $data[3];
			$eduMatSer 		= $data[4];
			$diagnoses 		= array();

			$eduMat 		= "";

			$sql = "
				SELECT DISTINCT
					dxc.SourceUID,
					dxc.DiagnosisCode,
					dxc.Description
				FROM
					DiagnosisCode dxc
				WHERE
					dxc.DiagnosisTranslationSerNum = $serial 
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	    	$query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$diagnosisCodeDetails = array(
					'sourceuid'		=> $data[0],
					'code'			=> $data[1],
					'description'	=> $data[2],
					'name' 			=> "$data[1] ($data[2])",
                    'added' 		=> 1
                );
				array_push($diagnoses, $diagnosisCodeDetails);
			}

			if ($eduMatSer != 0) {
                $eduMatObj = new EduMaterial();
                $eduMat = $eduMatObj->getEducationalMaterialDetails($eduMatSer);
			}
			
			$diagnosisTranslationDetails = array(
				'name_EN'           => $name_EN,
                'name_FR'           => $name_FR,
                'description_EN'    => $description_EN,
				'description_FR'    => $description_FR,
				'serial'            => $serial,
                'eduMatSer'         => $eduMatSer,
                'eduMat'			=> $eduMat,
                'count'             => count($diagnoses),
                'diagnoses'         => $diagnoses
			);
			return $diagnosisTranslationDetails;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $diagnosisTranslationDetails;
		}
	}

	/**
     *
     * Gets a list of diagnosis codes from a source database
     *
     * @return array $diagnoses : the list of diagnoses
     */
    public function getDiagnoses() {
		$diagnoses = array();
		$databaseObj = new Database();

        try {

			// ***********************************
            // ARIA
            // ***********************************
            $sourceDBSer = 1;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

            	// get already assigned diagnoses from our database
            	$assignedDiagnoses = $this->getAssignedDiagnoses();

				$sql = "
					SELECT DISTINCT
						-- get min because for some reason there are multiple diagnosis ser for many codes
						-- min will never change so its good enough as a unique id
						MIN(dx.DiagnosisSer),
						dx.DiagnosisId,
						RTRIM(REPLACE(REPLACE(dx.Description,'Malignant neoplasm','malignant neoplasm'),'malignant neoplasm','Ca'))
					FROM
						variansystem.dbo.Diagnosis dx
					WHERE
						dx.ObjectStatus = 'Active'
					GROUP BY 
						dx.DiagnosisId,
						RTRIM(REPLACE(REPLACE(dx.Description,'Malignant neoplasm','malignant neoplasm'),'malignant neoplasm','Ca'))
					ORDER BY
						dx.DiagnosisId
				";
				$query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();

                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                	$sourceUID 		= $data[0];
                	$diagnosisCode 	= $data[1];
                	$description 	= $data[2];

                    $diagnosisDetails = array(
                    	'sourceuid'		=> $sourceUID,
                    	'code'			=> $diagnosisCode,
                    	'description' 	=> $description,
                        'name'      	=> "$diagnosisCode ($description)",
                        'added'     	=> 0,
                        'assigned'		=> null
                    );

                    $assignedDiagnosis = $this->assignedSearch($sourceUID, $assignedDiagnoses);
                    if ($assignedDiagnosis) {
                    	$diagnosisDetails['added'] = 1;
                    	$diagnosisDetails['assigned'] = $assignedDiagnosis;
                    }
                    array_push($diagnoses, $diagnosisDetails);
                }


			}
			
			// ***********************************
            // WaitRoomManagement
            // ***********************************
            $sourceDBSer = 2;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "SELECT 'QUERY_HERE'";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    // Set appropriate diagnosis data here from query

                    //array_push($diagnoses, $diagnosisDetails); // Uncomment for use
                }

            }

            // ***********************************
            // Mosaiq
            // ***********************************
            $sourceDBSer = 3;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "SELECT 'QUERY_HERE'";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();
                
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    // Set appropriate diagnosis data here from query

                    //array_push($diagnoses, $diagnosisDetails); // Uncomment for use
                }

            }

            return $diagnoses;

  	  	} catch (PDOException $e) {
            echo $e->getMessage();
            return $diagnoses;
		}
	}

	/**
     *
     * Gets a list of already assigned diagnoses in our database
     *
     * @return array $diagnoses : the list of diagnoses
     */
	public function getAssignedDiagnoses () {
		$diagnoses = array();
 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT 
					dxc.SourceUID,
					dxt.Name_EN
				FROM 
					DiagnosisCode dxc,
					DiagnosisTranslation dxt
				WHERE
					dxt.DiagnosisTranslationSerNum = dxc.DiagnosisTranslationSerNum
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	    	$query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$diagnosisDetails = array(
					'sourceuid'		=> $data[0],
					'name_EN' 		=> "$data[1]"
                );
				array_push($diagnoses, $diagnosisDetails);
			}

			return $diagnoses;

		} catch (PDOException $e) {
            echo $e->getMessage();
            return $diagnoses;
		}
	}
	
	/**
     *
     * Inserts a diagnosis translation into the database
     *
     * @param array $diagnosisTranslationDetails : the diagnosis translation details
	 * @return void
     */
    public function insertDiagnosisTranslation ($diagnosisTranslationDetails) {

		$name_EN 			= $diagnosisTranslationDetails['name_EN'];
		$name_FR 			= $diagnosisTranslationDetails['name_FR'];
		$description_EN		= $diagnosisTranslationDetails['description_EN'];
		$description_FR		= $diagnosisTranslationDetails['description_FR'];
		$diagnoses 			= $diagnosisTranslationDetails['diagnoses'];
		$eduMatSer 			= 0;
		if ( is_array($diagnosisTranslationDetails['edumat']) && isset($diagnosisTranslationDetails['edumat']['serial']) ) {
            $eduMatSer = $diagnosisTranslationDetails['edumat']['serial'];
        }
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
					DiagnosisTranslation (
						Name_EN,
						Name_FR,
						Description_EN,
						Description_FR,
						EducationalMaterialControlSerNum,
						DateAdded
					)
				VALUES (
					\"$name_EN\",
					\"$name_FR\",
					\"$description_EN\",
					\"$description_FR\",
					'$eduMatSer',
					NOW()
				)
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$diagnosisTranslationSer = $host_db_link->lastInsertId();
			
			foreach ($diagnoses as $diagnosis) {

				$sourceuid 	= $diagnosis['sourceuid'];
				$code 		= $diagnosis['code'];
				$description= $diagnosis['description'];

				$sql = "
					INSERT INTO 
						DiagnosisCode (
							DiagnosisTranslationSerNum,
							SourceUID,
							DiagnosisCode,
							Description,
							DateAdded
						)
					VALUES (
						'$diagnosisTranslationSer',
						'$sourceuid',
						\"$code\",
						\"$description\",
						NOW()
					)
					ON DUPLICATE KEY UPDATE
						DiagnosisTranslationSerNum = '$diagnosisTranslationSer'
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();
            }

        } catch( PDOException $e) {
			return $e->getMessage();
		}

	}
	
	/**
     *
     * Gets a list of existing diagnosis translations in the database
     *
     * @return array $diagnosisTranslationList : the list of existing diagnosis translations
     */
    public function getExistingDiagnosisTranslations () {

		$diagnosisTranslationList = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					dxt.DiagnosisTranslationSerNum,
					dxt.Name_EN,
					dxt.Name_FR,
					dxt.Description_EN,
					dxt.Description_FR,
					dxt.EducationalMaterialControlSerNum
				FROM
					DiagnosisTranslation dxt
				WHERE
					dxt.Name_EN != ''
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$diagnosisTranslationSer 			= $data[0];
				$name_EN 							= $data[1];
				$name_FR 							= $data[2];
				$description_EN 					= $data[3];
				$description_FR						= $data[4];
				$eduMatSer 						 	= $data[5];
				$eduMat 							= "";
				$diagnoses 							= array();

				$sql = "
					SELECT DISTINCT
						dxc.SourceUID,
						dxc.DiagnosisCode,
						dxc.Description
					FROM
						DiagnosisCode dxc
					WHERE
						dxc.DiagnosisTranslationSerNum = $diagnosisTranslationSer
				";

				$secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$secondQuery->execute();

				while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

					$diagnosisDetail = array(
						'sourceuid'		=> $secondData[0],
						'code' 			=> $secondData[1],
						'description'	=> $secondData[2],
						'name' 			=> "$secondData[1] ($secondData[2])",
						'added' 		=> 1
					);

					array_push($diagnoses, $diagnosisDetail);
				}

				if ($eduMatSer != 0) {
                    $eduMatObj = new EduMaterial();
                    $eduMat = $eduMatObj->getEducationalMaterialDetails($eduMatSer);
				}
				
				$diagnosisTranslationDetails = array(
					'name_EN'           => $name_EN,
					'name_FR'           => $name_FR,
					'serial' 			=> $diagnosisTranslationSer,
					'description_EN'    => $description_EN,
					'description_FR'    => $description_FR,
					'eduMatSer'         => $eduMatSer,
					'eduMat'			=> $eduMat,
                    'diagnoses'         => $diagnoses,
                    'count'             => count($diagnoses)
				);

				array_push($diagnosisTranslationList, $diagnosisTranslationDetails);
            }
            return $diagnosisTranslationList;
        } catch (PDOException $e) {
			echo $e->getMessage();
			return $diagnosisTranslationList;
		}
	}

	/**
     *
     * Updates diagnosis translation details in the database
     *
     * @param array $diagnosisTranslationDetails : the diagnosis translation details
     * @return array : response
     */

	public function updateDiagnosisTranslation ($diagnosisTranslationDetails) {

		$serial 			= $diagnosisTranslationDetails['serial'];
		$name_EN 			= $diagnosisTranslationDetails['name_EN'];
		$name_FR 			= $diagnosisTranslationDetails['name_FR'];
		$description_EN		= $diagnosisTranslationDetails['description_EN'];
		$description_FR		= $diagnosisTranslationDetails['description_FR'];
		$diagnoses 			= $diagnosisTranslationDetails['diagnoses'];
		$eduMatSer 			= $diagnosisTranslationDetails['edumatser'];
		
		$existingDiagnoses = array();

		$response = array(
            'value'     => 0,
            'message'   => ''
        );
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				UPDATE
					DiagnosisTranslation
				SET
					DiagnosisTranslation.Name_EN 		= \"$name_EN\",
					DiagnosisTranslation.Name_FR 	 	= \"$name_FR\",
					DiagnosisTranslation.Description_EN = \"$description_EN\",
					DiagnosisTranslation.Description_FR = \"$description_FR\",
					DiagnosisTranslation.EducationalMaterialControlSerNum = '$eduMatSer'
				WHERE
					DiagnosisTranslation.DiagnosisTranslationSerNum = $serial 
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

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
				}
			}

			// If new diagnosis codes, insert into database
            foreach ($diagnoses as $diagnosis) {
            	$sourceuid 		= $diagnosis['sourceuid'];
            	$code 			= $diagnosis['code'];
            	$description 	= $diagnosis['description'];
                if(!in_array($diagnosis, $existingDiagnoses)) {
                    $sql = "
                        INSERT INTO
                            DiagnosisCode (
                                DiagnosisTranslationSerNum,
                                SourceUID,
                                DiagnosisCode,
                                Description
                            )
                        VALUES (
                            '$serial',
                            '$sourceuid',
                            \"$code\",
                            \"$description\"
                        )
                        ON DUPLICATE KEY UPDATE
                            DiagnosisTranslationSerNum = '$serial'
                    ";

	                $query = $host_db_link->prepare( $sql );
					$query->execute();
				}
			}
            $response['value'] = 1; // Success
            return $response;

		} catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}

	/**
     *
     * Removes a diagnosis translation from the database
     *
     * @param integer $diagnosisTranslationSer : the serial number of the diagnosis translation
     * @return array $response : response
     */
    public function deleteDiagnosisTranslation ($diagnosisTranslationSer) {

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

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

            $response['value'] = 1;
            return $response;

	    } catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response;
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
            if ($val['id'] === $id) {
                return 1;
            }
        }
        return 0;
    }

     /**
     *
     * Checks if a diagnosis has been assigned to a translation
     *
     * @param string $id    : the needle id
     * @param array $array  : the key-value haystack
     * @return $assignedDiagnosis
     */
    public function assignedSearch($id, $array) {
    	$assignedDiagnosis = null;
        if(empty($array) || !$id){
            return $assignedDiagnosis;
        }
        foreach ($array as $key => $val) {
            if ($val['sourceuid'] === $id) {
            	$assignedDiagnosis = $val;
                return $assignedDiagnosis;
            }
        }
        return $assignedDiagnosis;
    }
		
}



?>