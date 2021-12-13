<?php
/**
 *
 * EduMaterial class
 */
class EduMaterial extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_EDU_MAT, $guestStatus);
    }

    /**
     *
     * Updates the publish flags in the database
     *
     * @param array $eduMatList : the list of educational materials
	 * @param object $user : the current user in session
     * @return array $response : response
     */    
    public function updatePublishFlags( $eduMatList, $user ) {
        $this->checkWriteAccess(array($eduMatList, $user));

        // Initialize response array
        $response = array(
            'value'     => 0,
            'message'   => ''
		);
		$userSer = $user['id'];
		$sessionId = $user['sessionid'];
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            foreach ($eduMatList as $edumat) {

				$eduMatPublish  = $edumat['publish'];
                $eduMatSer      = $edumat['serial'];

				$sql = "
					UPDATE 
						EducationalMaterialControl 	
					SET 
						EducationalMaterialControl.PublishFlag = $eduMatPublish,
						EducationalMaterialControl.LastUpdatedBy = $userSer,
						EducationalMaterialControl.SessionId = '$sessionId'
					WHERE 
						EducationalMaterialControl.EducationalMaterialControlSerNum = $eduMatSer
				";

				$query = $host_db_link->prepare( $sql );
				$query->execute();

            }

            $response['value'] = 1; // Success
            return $response;

		} catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
		}
	}

    /**
     *
     * Gets a list of distinct phases in treatment defined in the database
     *
     * @return array $phases : the phases in treatment
     */
    public function getPhasesInTreatment() {
        $this->checkReadAccess();

        $phases = array();
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                SELECT DISTINCT 
                    p.PhaseInTreatmentSerNum,
                    p.Name_EN,
                    p.Name_FR
                FROM
                    PhaseInTreatment p
            ";
		    $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $phaseArray = array(
                    'serial'    => $data[0],
                    'name_EN'   => $data[1],
                    'name_FR'   => $data[2]
                );

                array_push($phases, $phaseArray); 
            }

            return $phases;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
		}
    }

    /**
     *
     * Gets a list of educational material types
     *
     * @return array $types : the educational material types
     */
    public function getEducationalMaterialTypes() {
        $this->checkReadAccess();

        // Initialize list of types, separate languages
        $types = array();
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    em.EducationalMaterialType_EN,
                    em.EducationalMaterialType_FR
                FROM
                    EducationalMaterialControl em
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $typeDetails = array(
                    'EN'    => $data[0],
                    'FR'    => $data[1]
                );
                array_push($types, $typeDetails);
            }

            return $types;

	    } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
		}
    }

    /**
     *
     * Gets educational material details
     *
     */
    public function getEducationalMaterialDetails($eduId) {
        $this->checkReadAccess($eduId);
        return $this->_getEducationalMaterialDetails($eduId);
	}

     /*
      * Gets a list of existing educational materials
      * @return array $eduMatList : the list of existing educational materials
      */
    public function getEducationalMaterials() {
        $this->checkReadAccess();
        return $this->_getListEduMaterial();
	}

    /**
     *
     * Inserts educational material into the database
     *
     * @param array $eduMatDetails : the educational material details
	 * @return array $response : response
     */
    public function insertEducationalMaterial ( $eduMatDetails ) {
        $this->checkWriteAccess($eduMatDetails);

        $name_EN        = $eduMatDetails['name_EN'];
        $name_FR        = $eduMatDetails['name_FR'];
        $url_EN         = $eduMatDetails['url_EN'];
        $url_FR         = $eduMatDetails['url_FR'];
        $shareURL_EN    = $eduMatDetails['share_url_EN'];
        $shareURL_FR    = $eduMatDetails['share_url_FR'];
        $type_EN        = $eduMatDetails['type_EN'];
        $type_FR        = $eduMatDetails['type_FR'];
        $phaseSer       = $eduMatDetails['phase_in_tx']['serial'];
        $tocs           = $eduMatDetails['tocs'];
		$triggers       = $eduMatDetails['triggers'];
		$userSer 		= $eduMatDetails['user']['id'];
		$sessionId 		= $eduMatDetails['user']['sessionid'];

        $urlExt_EN          = '';
        $urlExt_FR          = '';


        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        // Validate share url extension
        // Comment out for now because unsure if share url can be anything 
        /*if ($shareURL_EN || $shareURL_FR) {

            $shareEXT_EN = $this->extensionSearch($shareURL_EN);
            $shareEXT_FR = $this->extensionSearch($shareURL_FR);

            if ($shareEXT_EN != 'pdf' || $shareEXT_FR != 'pdf') {
                $response['message'] = 'Supporting PDF fields must be pdfs!';
                return $response; // return error
            }
        }*/

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            // Validate each table of content or URL
            $extensions = array();
            $sql = "
                SELECT DISTINCT 
                    ae.Name 
                FROM
                    AllowableExtension ae
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                array_push($extensions, $data[0]);
            }

            if ($url_EN || $url_FR) {

                $url_EN = $this->urlCheck($url_EN);
                $url_FR = $this->urlCheck($url_FR);

                $urlExt_EN = $this->extensionSearch($url_EN);
                $urlExt_FR = $this->extensionSearch($url_FR);

                if (!in_array($urlExt_EN, $extensions) || !in_array($urlExt_FR, $extensions) ) {
                    $response['message'] = "Allowable extensions for URLs are: " . implode(',', $extensions);
                    return $response; // return error
                }
            }

            if ($tocs) {
                foreach ($tocs as $toc) {

                    $toc['url_EN'] = $this->urlCheck($toc['url_EN']);
                    $toc['url_FR'] = $this->urlCheck($toc['url_FR']);

                    $tocExt_EN      = $this->extensionSearch($toc['url_EN']); 
                    $tocExt_FR      = $this->extensionSearch($toc['url_FR']); 

                    if (!in_array($tocExt_FR, $extensions) || !in_array($tocExt_EN, $extensions)) {
                        $response['message'] = "Allowable extensions for URLs are: " . implode(',', $extensions);
                        return $response; // return error
                    }
                }
            }

            $sql = "
                INSERT INTO 
                    EducationalMaterialControl (
                        Name_EN,
                        Name_FR,
                        URL_EN,
                        URL_FR, 
                        URLType_EN,
                        URLType_FR,
                        ShareURL_EN,
                        ShareURL_FR,
                        EducationalMaterialType_EN,
                        EducationalMaterialType_FR,
                        PhaseInTreatmentSerNum,
                        DateAdded,
						LastPublished,
						LastUpdatedBy,
						SessionId
                    )
                SELECT DISTINCT
                    \"$name_EN\",
                    \"$name_FR\",
                    \"$url_EN\",
                    \"$url_FR\",
                    IFNULL(ae_en.Type, NULL),
                    IFNULL(ae_fr.Type, NULL),
                    \"$shareURL_EN\",
                    \"$shareURL_FR\",
                    \"$type_EN\",
                    \"$type_FR\",
                    '$phaseSer',
                    NOW(),
					NOW(),
					'$userSer',
					'$sessionId'
                FROM 
                    AllowableExtension dummy 
                LEFT JOIN AllowableExtension ae_en
                ON ae_en.Name = '$urlExt_EN'
                LEFT JOIN AllowableExtension ae_fr
                ON ae_fr.Name = '$urlExt_FR'
                
            ";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$eduMatSer = $host_db_link->lastInsertId();

            if($tocs) {
                foreach ($tocs as $toc) {

                    $tocOrder       = $toc['order'];
                    $tocName_EN     = $toc['name_EN'];
                    $tocName_FR     = $toc['name_FR'];
                    $tocURL_EN      = $toc['url_EN'];
                    $tocURL_FR      = $toc['url_FR'];
                    $tocType_EN     = $toc['type_EN'];
                    $tocType_FR     = $toc['type_FR'];

                    $toc['url_EN'] = $this->urlCheck($toc['url_EN']);
                    $toc['url_FR'] = $this->urlCheck($toc['url_FR']);

                    $tocExt_EN      = $this->extensionSearch($toc['url_EN']); 
                    $tocExt_FR      = $this->extensionSearch($toc['url_FR']); 
    
                    $sql = "
                        INSERT INTO
                            EducationalMaterialControl (
                                EducationalMaterialType_EN,
                                EducationalMaterialType_FR,
                                Name_EN,
                                Name_FR,
                                URL_EN,
                                URL_FR,
                                URLType_EN,
                                URLType_FR,
                                PhaseInTreatmentSerNum,
                                ParentFlag,
                                DateAdded,
								LastPublished,
								LastUpdatedBy,
								SessionId
                            )
                        SELECT 
                            \"$tocType_EN\",
                            \"$tocType_FR\",
                            \"$tocName_EN\",
                            \"$tocName_FR\",
                            \"$tocURL_EN\",
                            \"$tocURL_FR\",
                            ae_en.Type,
                            ae_fr.Type,
                            '$phaseSer',
                            0,
                            NOW(),
							NOW(),
							'$userSer',
							'$sessionId'
                        FROM 
                            AllowableExtension ae_en,
                            AllowableExtension ae_fr
                        WHERE
                            ae_en.Name = '$tocExt_EN'
                        AND ae_fr.Name = '$tocExt_FR'
                    ";
                    $query = $host_db_link->prepare( $sql );
	    			$query->execute();
    
	    		    $tocSer = $host_db_link->lastInsertId();

                    $sql = "
                        INSERT INTO
                            EducationalMaterialTOC (
                                EducationalMaterialControlSerNum,
                                OrderNum,
                                ParentSerNum,
                                DateAdded
                            )
                        VALUES (
                            '$tocSer',
                            '$tocOrder',
                            '$eduMatSer',
                            NOW()
                        )
                    ";
                    $query = $host_db_link->prepare( $sql );
			    	$query->execute();
                }
            }

            $response['value'] = 1; // Success
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
        }
    }

    /**
     *
     * Updates educational material details in the database
     *
     * @param array $eduMatDetails : the educational material details
     * @return array $response : response
     */
    public function updateEducationalMaterial ($eduMatDetails) {
        $this->checkWriteAccess($eduMatDetails);

        $name_EN            = $eduMatDetails['name_EN'];
        $name_FR            = $eduMatDetails['name_FR'];
        $url_EN             = $eduMatDetails['url_EN'];
        $url_FR             = $eduMatDetails['url_FR'];
        $shareURL_EN        = $eduMatDetails['share_url_EN'];
        $shareURL_FR        = $eduMatDetails['share_url_FR'];
        $eduMatSer          = $eduMatDetails['serial'];
        $triggers           = $eduMatDetails['triggers'];
        $tocs               = $eduMatDetails['tocs'];
		$phaseSer           = $eduMatDetails['phase_serial'];
		$userSer 			= $eduMatDetails['user']['id'];
		$sessionId 			= $eduMatDetails['user']['sessionid'];

        $urlExt_EN          = null;
        $urlExt_FR          = null;
		$existingTriggers	= array();
        $existingTOCs       = array();

        $detailsUpdated     = $eduMatDetails['details_updated'];
        $tocsUpdated        = $eduMatDetails['tocs_updated'];
//        $triggersUpdated    = $eduMatDetails['triggers_updated'];

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        // Validate share url extension
        // Comment out for now because unsure if share url can be anything 
        /*if ($shareURL_EN || $shareURL_FR) {

            $shareEXT_EN = $this->extensionSearch($shareURL_EN);
            $shareEXT_FR = $this->extensionSearch($shareURL_FR);

            if ($shareEXT_EN != 'pdf' || $shareEXT_FR != 'pdf') {
                $response['message'] = 'Supporting PDF fields must be pdfs!';
                return $response; // return error
            }
        }*/

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            // Validate each table of content or URL
            $extensions = array();
            $sql = "
                SELECT DISTINCT 
                    ae.Name 
                FROM
                    AllowableExtension ae
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                array_push($extensions, $data[0]);
            }

            if ($url_EN || $url_FR) {

                $url_EN = $this->urlCheck($url_EN);
                $url_FR = $this->urlCheck($url_FR);

                $urlExt_EN = $this->extensionSearch($url_EN);
                $urlExt_FR = $this->extensionSearch($url_FR);

                if (!in_array($urlExt_EN, $extensions) || !in_array($urlExt_FR, $extensions) ) {
                    $response['message'] = "Allowable extensions for URLs are: " . implode(',', $extensions);
                    return $response; // return error
                }
            }

            if ($tocs) {
                foreach ($tocs as $toc) {

                    $toc['url_EN'] = $this->urlCheck($toc['url_EN']);
                    $toc['url_FR'] = $this->urlCheck($toc['url_FR']);

                    $tocExt_EN      = $this->extensionSearch($toc['url_EN']); 
                    $tocExt_FR      = $this->extensionSearch($toc['url_FR']); 

                    if (!in_array($tocExt_FR, $extensions) || !in_array($tocExt_EN, $extensions)) {
                        $response['message'] = "Allowable extensions for URLs are: " . implode(',', $extensions);
                        return $response; // return error
                    }
                }
            }

            if ($detailsUpdated) {

                if (!$tocs) {
                    // Update
        			$sql = "
                        UPDATE
                            EducationalMaterialControl,
                            AllowableExtension ae_en,
                            AllowableExtension ae_fr
                        SET
                            EducationalMaterialControl.Name_EN     		= \"$name_EN\",
                            EducationalMaterialControl.Name_FR     		= \"$name_FR\",
                            EducationalMaterialControl.URL_EN      		= \"$url_EN\",
                            EducationalMaterialControl.URL_FR      		= \"$url_FR\",
                            EducationalMaterialControl.URLType_EN  		= ae_en.Type,
                            EducationalMaterialControl.URLType_FR  		= ae_fr.Type,
                            EducationalMaterialControl.ShareURL_EN 		= \"$shareURL_EN\",
        					EducationalMaterialControl.ShareURL_FR 		= \"$shareURL_FR\",
        					EducationalMaterialControl.LastUpdatedBy	= '$userSer',
        					EducationalMaterialControl.SessionId		= '$sessionId'
                        WHERE
                            EducationalMaterialControl.EducationalMaterialControlSerNum = $eduMatSer
                        AND ae_en.Name = '$urlExt_EN'
                        AND ae_fr.Name = '$urlExt_FR'
                    ";
                
        			$query = $host_db_link->prepare( $sql );
        			$query->execute();
                }
                else {
                    // Update
                    $sql = "
                        UPDATE
                            EducationalMaterialControl
                        SET
                            EducationalMaterialControl.Name_EN          = \"$name_EN\",
                            EducationalMaterialControl.Name_FR          = \"$name_FR\",
                            EducationalMaterialControl.URL_EN           = \"$url_EN\",
                            EducationalMaterialControl.URL_FR           = \"$url_FR\",
                            EducationalMaterialControl.ShareURL_EN      = \"$shareURL_EN\",
                            EducationalMaterialControl.ShareURL_FR      = \"$shareURL_FR\",
                            EducationalMaterialControl.LastUpdatedBy    = '$userSer',
                            EducationalMaterialControl.SessionId        = '$sessionId'
                        WHERE
                            EducationalMaterialControl.EducationalMaterialControlSerNum = $eduMatSer
                    ";
                
                    $query = $host_db_link->prepare( $sql );
                    $query->execute();
                }
            }

/*            if ($triggersUpdated) {
        
                $sql = "
    		        SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
        			FROM     
        				Filters
    		    	WHERE 
                        Filters.ControlTableSerNum       = $eduMatSer
                    AND Filters.ControlTable             = 'EducationalMaterialControl'
                    AND Filters.FilterType              != ''
                    AND Filters.FilterId                != ''
    		    ";

    		    $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        		$query->execute();

    			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
        
                    $triggerArray = array(
                        'type'  => $data[0],
                        'id'    => $data[1]
                    );
                    array_push($existingTriggers, $triggerArray);
                }

                if($existingTriggers) { 

                    // If old triggers not in new trigger list, then remove
        	    	foreach ($existingTriggers as $existingTrigger) {
                        $id     = $existingTrigger['id'];
                        $type   = $existingTrigger['type'];
                        if (!$this->nestedSearch($id, $type, $triggers)) {
    				    	$sql = "
                                DELETE FROM 
        					    	Filters
        	    				WHERE
                                    Filters.FilterId            = \"$id\"
                                AND Filters.FilterType          = '$type'
                                AND Filters.ControlTableSerNum   = $eduMatSer
                                AND Filters.ControlTable         = 'EducationalMaterialControl'
    		    		    ";  
                
    	    	    		$query = $host_db_link->prepare( $sql );
    		    	    	$query->execute();

                             $sql = "
                                UPDATE FiltersMH
                                SET 
                                    FiltersMH.LastUpdatedBy = '$userSer',
                                    FiltersMH.SessionId = '$sessionId'
                                WHERE
                                    FiltersMH.FilterId              = \"$id\"
                                AND FiltersMH.FilterType            = '$type'
                                AND FiltersMH.ControlTableSerNum    = $eduMatSer
                                AND FiltersMH.ControlTable          = 'EducationalMaterialControl'
                                ORDER BY FiltersMH.DateAdded DESC 
                                LIMIT 1
                            ";
                            $query = $host_db_link->prepare( $sql );
                            $query->execute();
                        }
                    }
    	    	}

                if($triggers) {

                    // If new triggers (i.e. not in old list), then insert
        			foreach ($triggers as $trigger) {
                        $id     = $trigger['id'];
                        $type   = $trigger['type'];
                        if (!$this->nestedSearch($id, $type, $existingTriggers)) {
                            $sql = "
                                INSERT INTO 
                                    Filters (
                                        ControlTable,
                                        ControlTableSerNum,
                                        FilterId,
                                        FilterType,
                                        DateAdded,
                                        LastUpdatedBy,
                                        SessionId
                                    )
                                VALUES (
                                    'EducationalMaterialControl',
                                    '$eduMatSer',
                                    \"$id\",
                                    '$type',
                                    NOW(),
                                    '$userSer',
                                    '$sessionId'
                                )
    	    	    		";
    		    	    	$query = $host_db_link->prepare( $sql );
    		    		    $query->execute();
        			    }
    	    		}
                }
            }*/

            if ($tocsUpdated) {
                $sql = "
                    SELECT 
                        em.EducationalMaterialControlSerNum 
                    FROM
                        EducationalMaterialControl em,
                        EducationalMaterialTOC toc
                    WHERE
                        em.EducationalMaterialControlSerNum = toc.EducationalMaterialControlSerNum
                    AND toc.ParentSerNum                    = $eduMatSer
                ";
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    			$query->execute();
        
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $sql = "
                        DELETE FROM
                            EducationalMaterialControl
                        WHERE
                            EducationalMaterialControl.EducationalMaterialControlSerNum = $data[0]
                    ";         
                    $secondQuery = $host_db_link->prepare( $sql );
                    $secondQuery->execute();
                }

                $sql = "
                    DELETE FROM
                        EducationalMaterialTOC
                    WHERE
                        EducationalMaterialTOC.ParentSerNum = $eduMatSer
                ";
    	        $query = $host_db_link->prepare( $sql );
                $query->execute();

                if($tocs) {
                    foreach ($tocs as $toc) {

                        $tocOrder       = $toc['order'];
                        $tocName_EN     = $toc['name_EN'];
                        $tocName_FR     = $toc['name_FR'];
                        $tocURL_EN      = $toc['url_EN'];
                        $tocURL_FR      = $toc['url_FR'];
                        $tocType_EN     = $toc['type_EN'];
                        $tocType_FR     = $toc['type_FR'];

                        $toc['url_EN'] = $this->urlCheck($toc['url_EN']);
                        $toc['url_FR'] = $this->urlCheck($toc['url_FR']);

                        $tocExt_EN      = $this->extensionSearch($toc['url_EN']); 
                        $tocExt_FR      = $this->extensionSearch($toc['url_FR']); 
        
                        $sql = "
                            INSERT INTO
                                EducationalMaterialControl (
                                    EducationalMaterialType_EN,
                                    EducationalMaterialType_FR,
                                    Name_EN,
                                    Name_FR,
                                    URL_EN,
                                    URL_FR,
                                    URLType_EN,
                                    URLType_FR,
                                    PhaseInTreatmentSerNum,
                                    ParentFlag,
                                    DateAdded,
                                    LastUpdatedBy,
                                    SessionId
                                )
                            SELECT 
                                \"$tocType_EN\",
                                \"$tocType_FR\",
                                \"$tocName_EN\",
                                \"$tocName_FR\",
                                \"$tocURL_EN\",
                                \"$tocURL_FR\",
                                ae_en.Type,
                                ae_fr.Type,
                                '$phaseSer',
                                0,
                                NOW(),
                                '$userSer',
                                '$sessionId'
                            FROM 
                                AllowableExtension ae_en,
                                AllowableExtension ae_fr
                            WHERE
                                ae_en.Name = '$tocExt_EN'
                            AND ae_fr.Name = '$tocExt_FR'
                        ";
                        $query = $host_db_link->prepare( $sql );
    	    			$query->execute();
        
    	    		    $tocSer = $host_db_link->lastInsertId();
                        
                        $sql = "
                            INSERT INTO
                                EducationalMaterialTOC (
                                    EducationalMaterialControlSerNum,
                                    OrderNum,
                                    ParentSerNum,
                                    DateAdded
                                )
                            VALUES (
                                '$tocSer',
                                '$tocOrder',
                                '$eduMatSer',
                                NOW()
                            )
                        ";
                        $query = $host_db_link->prepare( $sql );
    			    	$query->execute();
                    }
                }

            }

            $response['value'] = 1; // Success
            return $response;

		} catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
		}
	}

    /**
     *
     * Deletes educational material from the database
     *
     * @param integer $eduMatSer : the educational material serial
	 * @param object $user : the current user in session
     * @return array $response : response
     */
    public function deleteEducationalMaterial ( $eduMatSer, $user ){
        $this->checkDeleteAccess(array($eduMatSer, $user));

        $response = array(
            'value'     => 0,
            'message'   => ''
        );
	    try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    EducationalMaterialControl
                WHERE
                    EducationalMaterialControl.EducationalMaterialControlSerNum = $eduMatSer
            ";

	        $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                DELETE FROM
                    Filters
                WHERE
                    Filters.ControlTableSerNum   = $eduMatSer
                AND Filters.ControlTable         = 'EducationalMaterialControl'
            ";
            $query = $host_db_link->prepare( $sql );
			$query->execute();

            $sql = "
                SELECT 
                    em.EducationalMaterialControlSerNum 
                FROM
                    EducationalMaterialControl em,
                    EducationalMaterialTOC toc
                WHERE
                    em.EducationalMaterialControlSerNum = toc.EducationalMaterialControlSerNum
                AND toc.ParentSerNum = $eduMatSer
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();
    
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $sql = "
                    DELETE FROM
                        EducationalMaterialControl
                    WHERE
                        EducationalMaterialControl.EducationalMaterialControlSerNum = $data[0]
                ";         
                $secondQuery = $host_db_link->prepare( $sql );
                $secondQuery->execute();
            }

            $sql = "
                DELETE FROM
                    EducationalMaterialTOC
                WHERE
                    EducationalMaterialTOC.ParentSerNum    = $eduMatSer
            ";
            $query = $host_db_link->prepare( $sql );
			$query->execute();

            $response['value'] = 1;
            return $response;

	    } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
		}
	}

    /**
     *
     * Gets chart logs of an educational material or materials
     *
     * @param integer $serial : the educational material serial number
     * @return array $educationalMaterialLogs : the educational material logs for highcharts
     */
    public function getEducationalMaterialChartLogs ($serial) {
        $this->checkReadAccess($serial);
        $educationalMaterialLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = null;
            // get all logs for all aliases
            if (!$serial) {
                $sql = "
                    SELECT DISTINCT
                        emmh.CronLogSerNum,
                        COUNT(emmh.CronLogSerNum),
                        cl.CronDateTime,
                        emc.Name_EN
                    FROM
                        EducationalMaterialMH emmh,
                        CronLog cl,
                        EducationalMaterialControl emc
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = emmh.CronLogSerNum
                    AND emmh.CronLogSerNum IS NOT NULL
                    AND emmh.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
                    GROUP BY
                        emmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC 
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $eduMatSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = $data[3];
                    $educationalMaterialDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($eduMatSeries[$seriesName])) {
                        $eduMatSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($eduMatSeries[$seriesName]['data'], $educationalMaterialDetail);
                }

                foreach ($eduMatSeries as $seriesName => $series) {
                    array_push($educationalMaterialLogs, $series);
                }
            }
            // get logs for specific alias
            else {
                $sql = "
                    SELECT DISTINCT
                        emmh.CronLogSerNum,
                        COUNT(emmh.CronLogSerNum),
                        cl.CronDateTime
                    FROM
                        EducationalMaterialMH emmh,
                        CronLog cl
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = emmh.CronLogSerNum
                    AND emmh.CronLogSerNum IS NOT NULL
                    AND emmh.EducationalMaterialControlSerNum = $serial
                    GROUP BY
                        emmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC 
                ";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();

                $eduMatSeries = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                    $seriesName = 'Educational Material';
                    $educationalMaterialDetail = array (
                        'x' => $data[2],
                        'y' => intval($data[1]),
                        'cron_serial' => $data[0]
                    );
                    if(!isset($eduMatSeries[$seriesName])) {
                        $eduMatSeries[$seriesName] = array(
                            'name'  => $seriesName,
                            'data'  => array()
                        );
                    }
                    array_push($eduMatSeries[$seriesName]['data'], $educationalMaterialDetail);
                }

                foreach ($eduMatSeries as $seriesName => $series) {
                    array_push($educationalMaterialLogs, $series);
                }
            }
            return $educationalMaterialLogs;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for educational material. " . $e->getMessage());
        }
    }

     /**
     *
     * Gets list logs of educational material during one or many cron sessions
     *
     */
    public function getEducationalMaterialListLogs($eduIds) {
        $this->checkReadAccess($eduIds);
        return $this->opalDB->getEduMaterialLogs($eduIds);
    }  

    /**
     *
     * Does a nested search for extension
     *
     * @param string $url       : the url
     * @return string $extension or null
     */
    public function extensionSearch($url) {

        // get host
        $host = parse_url($url, PHP_URL_HOST);
        // get path
        $path = parse_url($url, PHP_URL_PATH);

        // if no host return null
        if (!$host) {return null;}

        // host extension
        $extension = pathinfo($host, PATHINFO_EXTENSION);

        // if there's a path then check extension on path
        // eg. depdocs.com/education-material/material.php
        if ($path) {
            $pathExtension = pathinfo($path, PATHINFO_EXTENSION);
            if ($pathExtension) {return $pathExtension;}
            else {return $extension;} // eg. youtube.com/embed/h583d89 -- return host extension instead (com)
        }
            
        return $extension;

    }

    /**
     *
     * Does a url check for cetain domains (eg.youtube)
     *
     * @param string $url       : the url
     * @return string $url
     */
    public function urlCheck($url) {

        $urlCheck = $url;

        // get host
        $host = parse_url($url, PHP_URL_HOST);


        // if no host return url
        if (!$host) {return $urlCheck;}

        // YouTube .. So it can render/embed properly in app 
        if (strpos($host, 'youtube') !== false) {
            // First remove any potential arguments in url
            // Eg. https://www.youtube.com/watch?v=AAAA&feature=youtu.be -> https://www.youtube.com/watch?v=AAAA
            $pos = strpos($urlCheck, "&");
            $urlCheck = $pos ===false ? $urlCheck : substr($urlCheck, 0, $pos);

            // Replace potential youtube website urls to embed
            // https://www.youtube.com/watch?v=AAAA - > https://www.youtube.com/embed/AAAA
            $urlCheck = str_replace('watch?v=', 'embed/', $urlCheck);
            return $urlCheck;
        }
        // Youtu.be .. same reason
        if (strpos($host, 'youtu.be') !== false) {
            // get youtube ID 
            // eg: https://youtu.be/AAAA ... ID = AAAA
            $pos = strrpos($url, '/');
            $id = $pos === false ? false : substr($url, $pos + 1);
            if (!id) {
                return $urlCheck;
            }
            $urlCheck = 'https://www.youtube.com/embed/' . $id;
            return $urlCheck;
        }

        return $urlCheck;

    }


}
       
    
