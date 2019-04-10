<?php

/**
 *
 * Questionnaire-AnswerType class
 */
class QuestionType {

    /**
     *
     * Inserts a new answer type
     *
     * @param array $answerType : the answer type to be inserted
     * @return void
     */
    public function insertAnswerType($answerType){

        // Properties
        $name_EN 			= $answerType['name_EN'];
        $name_FR 			= $answerType['name_FR'];
        $category_EN 		= $answerType['category_EN'];
        $category_FR 		= $answerType['category_FR'];
        $private 			= $answerType['private'];
        $last_updated_by 	= $answerType['last_updated_by'];
        $created_by 		= $answerType['created_by'];
        $options 			= $answerType['options'];

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				INSERT INTO
					QuestionnaireAnswerType (
						name_EN,
						name_FR,
						category_EN,
						category_FR,
						private,
						created_by,
						last_updated_by
					)
				VALUES (
					\"$name_EN\",
					\"$name_FR\",
					\"$category_EN\",
					\"$category_FR\",
					'$private',
					'$created_by',
					'$last_updated_by'
				)
			";

            $query = $host_db_link->prepare( $sql );
            $query->execute();

            // add answer options
            if ($options) {

                $answerType_serNum = $host_db_link->lastInsertId();

                foreach ($options as $option) {

                    $text_EN = $option['text_EN'];
                    $text_FR = $option['text_FR'];
                    $position = $option['position'];

                    $sql = "
	                    INSERT INTO 
	                        QuestionnaireAnswerOption (
	                            text_EN,
	                            text_FR,
	                            answertype_serNum,
	                            position,
	                            last_updated_by,
	                            created_by
	                        )
	                    VALUES (
	                        \"$text_EN\",
							\"$text_FR\",
							'$answerType_serNum',
							'$position',
							'$last_updated_by',
							'$created_by'
	                    )
	                    ON DUPLICATE KEY UPDATE
	                        answertype_serNum = '$answerType_serNum'
					";
                    $query = $host_db_link->prepare( $sql );
                    $query->execute();
                }
            }

        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     *
     * Gets a list of existing question types
     *
     * @param integer $userId : the user id
     * @return array $questionTypes : the list of existing answer types
     */
    public function getQuestionTypes($userid){
        $questionTypes = array();
        try {
            $host_questionnaire_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_questionnaire_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                        SELECT
                        tt.ID AS serNum,
                        t.ID as typeSerNum,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = tt.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = tt.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
                        tt.private,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR,
                        tts.minValue,
                        tts.maxValue,
                        tts.increment,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.minCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS minCaption_EN,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.minCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS minCaption_FR,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.maxCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS maxCaption_EN,
                        (SELECT d.content FROM dictionary d WHERE d.contentId = tts.maxCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS maxCaption_FR,
                        dt1.name AS tableName,
                        dt2.name AS subTableName,
                        tt.OAUserId AS created_by
                        FROM typeTemplate tt
                        LEFT JOIN type t ON t.ID = tt.typeId
                        LEFT JOIN definitionTable dt1 ON dt1.ID = t.templateTableId
                        LEFT JOIN definitionTable dt2 ON dt2.ID = t.templateSubTableId
                        LEFT JOIN typeTemplateSlider tts ON tts.typeTemplateId = tt.ID
                        WHERE
                        tt.private = 0
                        OR
                        tt.OAUserId = :userId;";
            $query_questionnaire = $host_questionnaire_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query_questionnaire->bindParam(':userId', $userid, PDO::PARAM_INT);
            $query_questionnaire->execute();
            $listTypes = $query_questionnaire->fetchAll(PDO::FETCH_ASSOC);
            foreach ($listTypes as $row) {
                $temp = array(
                    'serNum'        => $row["serNum"],
                    'typeSerNum'    => $row["typeSerNum"],
                    'name_EN'       => $row["name_EN"],
                    'name_FR'       => $row["name_FR"],
                    'private'       => $row["private"],
                    'category_EN'   => $row["category_EN"],
                    'category_FR'   => $row["category_FR"],
                    'minCaption_EN'   => $row["minCaption_EN"],
                    'minCaption_FR'   => $row["minCaption_FR"],
                    'maxCaption_EN'   => $row["maxCaption_EN"],
                    'maxCaption_FR'   => $row["maxCaption_FR"],
                    'created_by'    => $row["created_by"],
                    'minValue'      => $row["minValue"],
                    'maxValue'      => $row["maxValue"],
                    'increment'     => $row["increment"],
                );

                // if the table has a subtable, returns its options
                if($row["subTableName"] != "") {
                    $subSql = "SELECT st.*, (SELECT d.content FROM dictionary d WHERE d.contentId = st.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN, (SELECT d.content FROM dictionary d WHERE d.contentId = st.description AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR FROM ".$row["subTableName"]." st WHERE parentTableId = :subTableId ORDER BY st.order;";
                    $query_questionnaire = $host_questionnaire_db_link->prepare($subSql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                    $query_questionnaire->bindParam(':subTableId', $row["serNum"]);
                    $query_questionnaire->execute();
                    $subQuestions = $query_questionnaire->fetchAll(PDO::FETCH_ASSOC);
                    $temp["options"] = $subQuestions;

                }
                array_push($questionTypes, $temp);
            }
            return $questionTypes;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $questionTypes;
        }

    }

    /**
     *
     * Gets a list of answer type categories
     *
     * @return array $answerTypeCategories : the list of answer type categories
     */
    public function getAnswerTypeCategories(){
        $answerTypeCategories = array();

        try {
            $host_questionnaire_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_questionnaire_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "SELECT ID, (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = 2) AS category_EN, (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = 1) AS category_FR FROM type t";

            $query_questionnaire = $host_questionnaire_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query_questionnaire->bindParam(':userId', $userid, PDO::PARAM_INT);
            $query_questionnaire->execute();
            $answerTypeCategories = $query_questionnaire->fetchAll(PDO::FETCH_ASSOC);

            return $answerTypeCategories;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $answerTypeCategories;
        }
    }
}

?>