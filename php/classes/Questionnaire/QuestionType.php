<?php

/**
 *
 * Questionnaire-AnswerType class
 */
class QuestionType {

    protected $questionnaireDB;
    protected $opalDB;

    public function __construct($userId = "-1") {
        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD
        );
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD
        );

        $this->setUserInfo($userId);
    }

    protected function setUserInfo($userId) {
        $userInfo = $this->opalDB->getUserInfo($userId);
        $this->opalDB->setUserId($userInfo["userId"]);
        $this->opalDB->setUsername($userInfo["username"]);
        $this->questionnaireDB->setUserId($userInfo["userId"]);
        $this->questionnaireDB->setUsername($userInfo["username"]);
    }

    /**
     *
     * Inserts a new answer type
     *
     * @param array $answerType : the answer type to be inserted
     * @return void
     */
    public function insertAnswerType($answerType){
        try {
            $host_questionnaire_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_questionnaire_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            //$temp = $newDB->getTableId(TYPE_TEMPLATE_TABLE);
            //$minCaptionId = $newDB->addToDictionary($minCaption, $minCaption, $temp);

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

            $query_questionnaire = $host_questionnaire_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query_questionnaire->bindParam(':userId', $userid, PDO::PARAM_INT);
            $query_questionnaire->execute();

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
    public function getQuestionTypes(){
        $questionTypes = array();
        $result = $this->questionnaireDB->getQuestionTypes();



        foreach ($result as $row) {
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
                $temp["options"] = $this->questionnaireDB->getQuestionTypesOptions($row["serNum"], $row["subTableName"]);
            }
            array_push($questionTypes, $temp);
        }
        return $questionTypes;
    }

    /**
     *
     * Gets a list of answer type categories
     *
     * @return array $answerTypeCategories : the list of answer type categories
     */
    public function getQuestionTypeCategories(){
        return $this->questionnaireDB->getQuestionTypeCategories();
    }
}

?>