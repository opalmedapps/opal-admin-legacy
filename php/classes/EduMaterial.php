<?php
/**
 *
 * EduMaterial class
 */
class EduMaterial {

    /**
     *
     * Updates the publish flags in the database
     *
     * @param array $eduMatList : the list of educational materials
     * @return array $response : response
     */    
    public function updatePublishFlags( $eduMatList ) {

        // Initialize response array
        $response = array(
            'value'     => 0,
            'message'   => ''
        );
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
						EducationalMaterialControl.PublishFlag = $eduMatPublish 
					WHERE 
						EducationalMaterialControl.EducationalMaterialControlSerNum = $eduMatSer
				";

				$query = $host_db_link->prepare( $sql );
				$query->execute();
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
     * Gets a list of distinct phases in treatment defined in the database
     *
     * @return array $phases : the phases in treatment
     */
    public function getPhasesInTreatment() {
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
			echo $e->getMessage();
			return $phases;
		}
    }

    /**
     *
     * Gets a list of educational material types
     *
     * @return array $types : the educational material types
     */
    public function getEducationalMaterialTypes() {

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
			echo $e->getMessage();
			return $types;
		}
    }

    /**
     *
     * Gets educational material details
     *
     * @param integer $eduMatSer : the educational material serial number
     * @return array $eduMatDetails : the educational material details
     */
    public function getEducationalMaterialDetails ($eduMatSer) {

        $eduMatDetails = array();

 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    em.EducationalMaterialType_EN,
                    em.EducationalMaterialType_FR,
                    em.Name_EN,
                    em.Name_FR,
                    em.URL_EN,
                    em.URL_FR,
                    phase.PhaseInTreatmentSerNum,
                    phase.Name_EN,
                    phase.Name_FR,
                    em.ShareURL_EN,
                    em.ShareURL_FR
                FROM
                    EducationalMaterialControl em,
                    PhaseInTreatment phase
                WHERE
                    em.EducationalMaterialControlSerNum = $eduMatSer
                AND phase.PhaseInTreatmentSerNum        = em.PhaseInTreatmentSerNum
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $type_EN                = $data[0];
            $type_FR                = $data[1];
            $name_EN                = $data[2];
            $name_FR                = $data[3];
            $url_EN                 = urldecode($data[4]);
            $url_FR                 = urldecode($data[5]);
            $phaseSer               = $data[6];
            $phaseName_EN           = $data[7];
            $phaseName_FR           = $data[8];
            $shareURL_EN            = $data[9];
            $shareURL_FR            = $data[10];
            $filters                = array();
            $tocs                   = array(); // Table of contents

            $sql = "
                SELECT DISTINCT 
                    Filters.FilterType,
                    Filters.FilterId
                FROM
                    EducationalMaterialControl em,
                    Filters
                WHERE
                    em.EducationalMaterialControlSerNum     = $eduMatSer
                AND Filters.ControlTable                    = 'EducationalMaterialControl'
                AND Filters.ControlTableSerNum              = em.EducationalMaterialControlSerNum
                AND Filters.FilterType                      != ''
                AND Filters.FilterId                        != ''
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	    	$query->execute();

	    	while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

		    	$filterType = $data[0];
				$filterId   = $data[1];
    			$filterArray = array (
	    			'type'  => $filterType,
	    			'id'    => $filterId,
		    		'added' => 1
				);
    
	    		array_push($filters, $filterArray);
            }

            $sql = "
                SELECT DISTINCT
                    toc.OrderNum,
                    em.EducationalMaterialControlSerNum,
                    em.Name_EN,
                    em.Name_FR,
                    em.EducationalMaterialType_EN,
                    em.EducationalMaterialType_FR,
                    em.URL_EN,
                    em.URL_FR
                FROM
                    EducationalMaterialTOC toc,
                    EducationalMaterialControl em
                WHERE
                    toc.EducationalMaterialControlSerNum    = em.EducationalMaterialControlSerNum
                AND toc.ParentSerNum                        = $eduMatSer
                ORDER BY
                    toc.OrderNum
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		    $query->execute();

		    while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $tocOrder       = $data[0];
                $tocSer         = $data[1];
                $tocName_EN     = $data[2];
                $tocName_FR     = $data[3];
                $tocType_EN     = $data[4];
                $tocType_FR     = $data[5];
                $tocURL_EN      = urldecode($data[6]);
                $tocURL_FR      = urldecode($data[7]);

                $tocArray = array (
                    'serial'        => $tocSer,
                    'order'         => $tocOrder,
                    'name_EN'       => $tocName_EN,
                    'name_FR'       => $tocName_FR,
                    'type_EN'       => $tocType_EN,
                    'type_FR'       => $tocType_FR,
                    'url_EN'        => $tocURL_EN,
                    'url_FR'        => $tocURL_FR,
                    'parent_serial' => $eduMatSer
                );    
                array_push($tocs, $tocArray);
            }
        
            $eduMatDetails = array (
                'name_EN'           => $name_EN,
                'name_FR'           => $name_FR,
                'serial'            => intval($eduMatSer),
                'type_EN'           => $type_EN,
                'type_FR'           => $type_FR,
                'url_EN'            => $url_EN,
                'url_FR'            => $url_FR,
                'share_url_EN'      => $shareURL_EN,
                'share_url_FR'      => $shareURL_FR,
                'publish'           => $publish,
                'phase_serial'      => $phaseSer,
                'phase_EN'          => $phaseName_EN,
                'phase_FR'          => $phaseName_FR,
                'filters'           => $filters,
                'tocs'              => $tocs
            );
            return $eduMatDetails;
	    } catch (PDOException $e) {
			echo $e->getMessage();
			return $eduMatDetails;
		}
	}

     /**
     *
     * Gets a list of existing educational materials
     *
     * @return array $eduMatList : the list of existing educational materials 
     */                  
    public function getEducationalMaterials() {
        $eduMatList = array();
 		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    em.EducationalMaterialControlSerNum,
                    em.EducationalMaterialType_EN,
                    em.EducationalMaterialType_FR,
                    em.Name_EN,
                    em.Name_FR,
                    em.URL_EN,
                    em.URL_FR,
                    phase.PhaseInTreatmentSerNum,
                    phase.Name_EN,
                    phase.Name_FR,
                    em.PublishFlag,
                    em.ParentFlag,
                    em.ShareURL_EN,
                    em.ShareURL_FR,
                    em.LastUpdated
                FROM
                    EducationalMaterialControl em,
                    PhaseInTreatment phase
                WHERE
                    phase.PhaseInTreatmentSerNum = em.PhaseInTreatmentSerNum
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $eduMatSer              = intval($data[0]);
                $type_EN                = $data[1];
                $type_FR                = $data[2];
                $name_EN                = $data[3];
                $name_FR                = $data[4];
                $url_EN                 = urldecode($data[5]);
                $url_FR                 = urldecode($data[6]);
                $phaseSer               = $data[7];
                $phaseName_EN           = $data[8];
                $phaseName_FR           = $data[9];
                $publish                = $data[10];
                $parentFlag             = $data[11];
                $shareURL_EN            = $data[12];
                $shareURL_FR            = $data[13];
                $lastUpdated            = $data[14];
                $rating                 = -1;
                $filters                = array();
                $tocs                   = array();

                if ($parentFlag == 1) {
                    $sql = "
                        SELECT
                            AVG(emr.RatingValue) 
                        FROM
                            EducationalMaterialRating emr
                        WHERE
                            emr.EducationalMaterialControlSerNum = $eduMatSer
                    ";
                    $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                    $secondQuery->execute();
        
                    while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                        if ($secondData[0]) {
                            $rating = $secondData[0];
                        }
                    }
                }

                $sql = "
                    SELECT DISTINCT 
                        Filters.FilterType,
                        Filters.FilterId
                    FROM
                        EducationalMaterialControl em,
                        Filters
                    WHERE   
                        em.EducationalMaterialControlSerNum     = $eduMatSer
                    AND Filters.ControlTable                    = 'EducationalMaterialControl'
                    AND Filters.ControlTableSerNum              = em.EducationalMaterialControlSerNum
                    AND Filters.FilterType                      != ''
                    AND Filters.FilterId                        != ''
                ";
                $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$secondQuery->execute();
    
				while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
    
    				$filterType = $secondData[0];
                    $filterId   = $secondData[1];
			    	$filterArray = array (
						'type'  => $filterType,
				    	'id'    => $filterId,
					    'added' => 1
    				);
    
    				array_push($filters, $filterArray);
                }
    
                $sql = "
                    SELECT DISTINCT
                        em.EducationalMaterialControlSerNum,
                        em.Name_EN,
                        em.Name_FR,
                        toc.OrderNum,
                        em.EducationalMaterialType_EN,
                        em.EducationalMaterialType_FR,
                        em.URL_EN,
                        em.URL_FR
                    FROM
                        EducationalMaterialTOC toc,
                        EducationalMaterialControl em
                    WHERE
                        toc.EducationalMaterialControlSerNum    = em.EducationalMaterialControlSerNum
                    AND toc.ParentSerNum                        = $eduMatSer
                    ORDER BY
                        toc.OrderNum
                ";
                $secondQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			    $secondQuery->execute();
    
    			while ($secondData = $secondQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
    
                    $tocSer         = $secondData[0];
                    $tocName_EN     = $secondData[1];
                    $tocName_FR     = $secondData[2];
                    $tocOrder       = $secondData[3];
                    $tocType_EN     = $secondData[4];
                    $tocType_FR     = $secondData[5];
                    $tocURL_EN      = urldecode($secondData[6]);
                    $tocURL_FR      = urldecode($secondData[7]);
                    $tocArray = array (
                        'serial'        => $tocSer,
                        'order'         => $tocOrder,
                        'name_EN'       => $tocName_EN,
                        'name_FR'       => $tocName_FR,
                        'type_EN'       => $tocType_EN,
                        'type_FR'       => $tocType_FR,
                        'url_EN'        => $tocURL_EN,
                        'url_FR'        => $tocURL_FR,
                        'parent_serial' => $eduMatSer
                    );
                    array_push($tocs, $tocArray);
                }

                $eduMatArray = array (
                    'name_EN'           => $name_EN,
                    'name_FR'           => $name_FR,
                    'serial'            => $eduMatSer,
                    'type_EN'           => $type_EN,
                    'type_FR'           => $type_FR,
                    'url_EN'            => $url_EN,
                    'url_FR'            => $url_FR,
                    'share_url_EN'      => $shareURL_EN,
                    'share_url_FR'      => $shareURL_FR,
                    'phase_serial'      => $phaseSer,
                    'phase_EN'          => $phaseName_EN,
                    'phase_FR'          => $phaseName_FR,
                    'parentFlag'        => $parentFlag,
                    'publish'           => $publish,
                    'lastupdated'       => $lastUpdated,
                    'rating'            => $rating,
                    'filters'           => $filters,
                    'tocs'              => $tocs
                );

                array_push($eduMatList, $eduMatArray);
            }
            return $eduMatList;
	    } catch (PDOException $e) {
			echo $e->getMessage();
			return $eduMatList;
		}
	}

    /**
     *
     * Inserts educational material into the database
     *
     * @param array $eduMatDetails : the educational material details
	 * @return array $response : response
     */
    public function insertEducationalMaterial ( $eduMatDetails ) {

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
        $filters        = $eduMatDetails['filters'];

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
                        LastPublished
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
                    NOW()
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

            if ($filters) {
                foreach ($filters as $filter) {
    
                    $filterType = $filter['type'];
                    $filterId   = $filter['id'];
    
	    			$sql = "
                        INSERT INTO 
                            Filters (
                                ControlTable,
                                ControlTableSerNum,
                                FilterType,
                                FilterId,
                                DateAdded
                            )
                        VALUES (
                            'EducationalMaterialControl',
                            '$eduMatSer',
                            '$filterType',
                            \"$filterId\",
                            NOW()
                        )
		    		";
			    	$query = $host_db_link->prepare( $sql );
				    $query->execute();
                }
            }

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
                                LastPublished
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
                            NOW()
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
            $response['message'] = $e->getMessage();
            return $response; // Fail
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

        $name_EN            = $eduMatDetails['name_EN'];
        $name_FR            = $eduMatDetails['name_FR'];
        $url_EN             = $eduMatDetails['url_EN'];
        $url_FR             = $eduMatDetails['url_FR'];
        $shareURL_EN        = $eduMatDetails['share_url_EN'];
        $shareURL_FR        = $eduMatDetails['share_url_FR'];
        $eduMatSer          = $eduMatDetails['serial'];
        $filters            = $eduMatDetails['filters'];
        $tocs               = $eduMatDetails['tocs'];
        $phaseSer           = $eduMatDetails['phase_serial'];
        $urlExt_EN          = null;
        $urlExt_FR          = null;
		$existingFilters	= array();
        $existingTOCs       = array();

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

            // Update
			$sql = "
                UPDATE
                    EducationalMaterialControl,
                    AllowableExtension ae_en,
                    AllowableExtension ae_fr
                SET
                    EducationalMaterialControl.Name_EN     = \"$name_EN\",
                    EducationalMaterialControl.Name_FR     = \"$name_FR\",
                    EducationalMaterialControl.URL_EN      = \"$url_EN\",
                    EducationalMaterialControl.URL_FR      = \"$url_FR\",
                    EducationalMaterialControl.URLType_EN  = ae_en.Type,
                    EducationalMaterialControl.URLType_FR  = ae_fr.Type,
                    EducationalMaterialControl.ShareURL_EN = \"$shareURL_EN\",
                    EducationalMaterialControl.ShareURL_FR = \"$shareURL_FR\"
                WHERE
                    EducationalMaterialControl.EducationalMaterialControlSerNum = $eduMatSer
                AND ae_en.Name = '$urlExt_EN'
                AND ae_fr.Name = '$urlExt_FR'
            ";
        
			$query = $host_db_link->prepare( $sql );
			$query->execute();

    
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
    
                $filterArray = array(
                    'type'  => $data[0],
                    'id'    => $data[1]
                );
                array_push($existingFilters, $filterArray);
            }

            if($existingFilters) { 

                // If old filters not in new filter list, then remove
    	    	foreach ($existingFilters as $existingFilter) {
                    $id     = $existingFilter['id'];
                    $type   = $existingFilter['type'];
                    if (!$this->nestedSearch($id, $type, $filters)) {
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
                    }
                }
	    	}

            if($filters) {

                // If new filters (i.e. not in old list), then insert
    			foreach ($filters as $filter) {
                    $id     = $filter['id'];
                    $type   = $filter['type'];
                    if (!$this->nestedSearch($id, $type, $existingFilters)) {
                        $sql = "
                            INSERT INTO 
                                Filters (
                                    ControlTable,
                                    ControlTableSerNum,
                                    FilterId,
                                    FilterType,
                                    DateAdded
                                )
                            VALUES (
                                'EducationalMaterialControl',
                                '$eduMatSer',
                                \"$id\",
                                '$type',
                                NOW()
                            )
	    	    		";
		    	    	$query = $host_db_link->prepare( $sql );
		    		    $query->execute();
    			    }
	    		}
            }
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
                                DateAdded
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
                            NOW()
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
		    $response['message'] = $e->getMessage();
			return $response; // Fail
		}
	}

    /**
     *
     * Deletes educational material from the database
     *
     * @param integer $eduMatSer : the educational material serial
     * @return array $response : response
     */
    public function deleteEducationalMaterial ( $eduMatSer ){

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
		    $response['message'] = $e->getMessage();
			return $response;
		}
	}

    /**
     *
     * Does a nested search for match
     *
     * @param string $id    : the needle id
     * @param string $type  : the needle type
     * @param array $array  : the key-value haystack
     * @return boolean
     */
    public function nestedSearch($id, $type, $array) {
        if(empty($array) || !$id || !$type){
            return 0;
        }
        foreach ($array as $key => $val) {
            if ($val['id'] === $id and $val['type'] === $type) {
                return 1;
            }
        }
        return 0;
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
       
    
