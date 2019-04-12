<?php

/**
 *
 * Questionnaire-Question class
 */
class Question {

    protected $questionnaireDB;
    protected $opalDB;
    protected $userInfo;

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
        $this->userInfo = $this->opalDB->getUserInfo($userId);
        $this->opalDB->setUserId($this->userInfo["userId"]);
        $this->opalDB->setUsername($this->userInfo["username"]);
        $this->questionnaireDB->setUserId($this->userInfo["userId"]);
        $this->questionnaireDB->setUsername($this->userInfo["username"]);
    }

    /**
     *
     * Inserts a question into our database
     *
     * @param array $questionDetails  : the question details
     * @return void
     */
    public function insertQuestion($questionDetails){

        $text_EN 				= $questionDetails['text_EN'];
        $text_FR 				= $questionDetails['text_FR'];
        $answertype_serNum 		= $questionDetails['answertype_serNum'];
        $questiongroup_serNum 	= $questionDetails['questiongroup_serNum'];
        $created_by 			= $questionDetails['created_by'];
        $last_updated_by 		= $questionDetails['last_updated_by'];

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				INSERT INTO
					QuestionnaireQuestion (
						text_EN,
						text_FR,
						questiongroup_serNum,
						answertype_serNum,
						last_updated_by,
						created_by
					)
				VALUES (
					\"$text_EN\",
					\"$text_FR\",
					'$questiongroup_serNum',
					'$answertype_serNum',
					'$last_updated_by',
					'$created_by'
				)
			";

            $query = $host_db_link->prepare( $sql );
            $query->execute();

        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Gets a list of existing questions. For each question, it will list the libraries it belongs too (if any) and
     * if the question is locked (e.a. if the question was already sent to a patient).
     *
     * @return array $questions : the list of existing questions
     */
    public function getQuestions(){
        $questions = array();
        $questionsLists = $this->questionnaireDB->fetchAllQuestions();
        foreach ($questionsLists as $row){
            $libraries = $this->questionnaireDB->fetchLibrariesQuestion($row["ID"]);
            $libNameEn = array();
            $libNameFr = array();
            foreach($libraries as $library) {
                array_push($libNameEn, $library["text_EN"]);
                array_push($libNameFr, $library["text_FR"]);
            }

            $libNameEn = implode(", ", $libNameEn);
            $libNameFr = implode(", ", $libNameFr);

            if ($libNameEn == "") $libNameEn = "None";
            if ($libNameFr == "") $libNameFr = "Aucune";
            $questionnairesList = array();
            $questionnaires = $this->questionnaireDB->fetchQuestionnairesIdQuestion($row["ID"]);

            foreach ($questionnaires as $questionnaire) {
                array_push($questionnairesList, $questionnaire["ID"]);
            }

            $questionLocked = 0;
            if (count($questionnairesList) > 0) {
                $questionnairesList = implode(", ", $questionnairesList);
                $questionLocked = $this->opalDB->countLockedQuestionnaires($questionnairesList);
                $questionLocked = intval($questionLocked["total"]);
            }

            $questionArray = array (
                'serNum'				=> $row["ID"],
                'text_EN'				=> strip_tags($row["text_EN"]),
                'text_FR'				=> strip_tags($row["text_FR"]),
                'private'				=> $row["private"],
                'answertype_serNum'		=> $row["answertype_Id"],
                'answertype_name_EN'	=> $row["answertype_name_EN"],
                'answertype_name_FR'	=> $row["answertype_name_FR"],
                'library_name_EN'		=> $libNameEn,
                'library_name_FR'		=> $libNameFr,
                'final'         		=> $row["final"],
                'locked'        		=> $questionLocked,
            );
            array_push($questions, $questionArray);
        }
        return $questions;
    }

    /**
     *
     * Gets question details
     *
     * @param integer $questionSerNum : the question serial number
     * @return array $questionDetails : the question details
     */
    public function getQuestionDetails ($questionSerNum) {

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
				SELECT
					QuestionnaireQuestion.text_EN,
					QuestionnaireQuestion.text_FR,
					QuestionnaireQuestion.answertype_serNum,
					QuestionnaireQuestion.questiongroup_serNum,
					QuestionnaireQuestion.last_updated_by,
					QuestionnaireAnswerType.name_EN,
					QuestionnaireAnswerType.name_FR,
					Questiongroup.name_EN,
					Questiongroup.name_FR
				FROM
					QuestionnaireQuestion,
					QuestionnaireAnswerType,
					Questiongroup
				WHERE
					QuestionnaireQuestion.serNum = $questionSerNum
				AND
					QuestionnaireAnswerType.serNum = QuestionnaireQuestion.answertype_serNum
				AND
					Questiongroup.serNum = QuestionnaireQuestion.questiongroup_serNum
			";

            $query = $host_db_link->prepare($sql);
            $query->execute();

            $row = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $text_EN = $row[0];
            $text_FR = $row[1];
            $answertype_serNum = $row[2];
            $questiongroup_serNum = $row[3];
            $last_updated_by = $row[4];
            $atNameEN = $row[5];
            $atNameFR = $row[6];
            $groupNameEN = $row[7];
            $groupNameFR = $row[8];

            $questionDetails = array(
                'serNum'				=> $questionSerNum,
                'text_EN'				=> $text_EN,
                'text_FR'				=> $text_FR,
                'answertype_serNum'		=> $answertype_serNum,
                'questiongroup_serNum'	=> $questiongroup_serNum,
                'last_updated_by'		=> $last_updated_by,
                'answertype_name_EN'	=> $atNameEN,
                'answertype_name_FR'	=> $atNameFR,
                'group_name_EN'			=> $groupNameEN,
                'group_name_FR'			=> $groupNameFR
            );

            return $questionDetails;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     *
     * Updates a question
     *
     * @param array $questionDetails  : the question details
     * @return array $response : response
     */
    public function updateQuestion($questionDetails) {

        $serNum 				= $questionDetails['serNum'];
        $text_EN 				= $questionDetails['text_EN'];
        $text_FR 				= $questionDetails['text_FR'];
        $answertype_serNum 		= $questionDetails['answertype_serNum'];
        $questiongroup_serNum 	= $questionDetails['questiongroup_serNum'];
        $last_updated_by 		= $questionDetails['last_updated_by'];

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				UPDATE
					QuestionnaireQuestion
				SET
					QuestionnaireQuestion.text_EN = \"$text_EN\",
					QuestionnaireQuestion.text_FR = \"$text_FR\",
					QuestionnaireQuestion.answertype_serNum = '$answertype_serNum',
					QuestionnaireQuestion.questiongroup_serNum = '$questiongroup_serNum',
					QuestionnaireQuestion.last_updated_by = '$last_updated_by'
				WHERE
					QuestionnaireQuestion.serNum = $serNum
			";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1; // Success
            return $response;

        } catch (PDOException $e) {
            $response['message'] = $e->getMessage();
            return $response;
        }
    }

    /**
     * Mark a question as deleted. First, it get the last time it was updated, and check if the question was already
     * sent to a patient. Then it checked if the record was updated in the meantime, and if not, it marks the question
     * as being deleted.
     *
     * WARNING!!! No record should be EVER be removed from the questionnaire database! It should only being marked as
     * being deleted ONLY  after it was verified the record is not locked, the user has the proper authorization and
     * no more than one user is doing modification on it at a specific moment. Not following the proper procedure will
     * have some serious impact on the integrity of the database and its records.
     *
     * REMEMBER !!! NO DELETE STATEMENT EVER !!! YOU HAVE BEING WARNED !!!
     *
     * @param $questionId (ID of the question), $userId (ID of the user who requested the deletion)
     * @return array $response : response
     */
    public function deleteQuestion($questionId) {
        if ($this->userInfo["userId"] == -1) {
            $response['value'] = false; // User is not identified.
            $response['message'] = 401;
            return $response;
        }

        $lastUpdated = $this->questionnaireDB->getLastTimeTableUpdated(QUESTION_TABLE, $questionId);
        $questionnaires = $this->questionnaireDB->fetchQuestionnairesIdQuestion($questionId);
        $questionnairesList = array();
        foreach ($questionnaires as $questionnaire) {
            array_push($questionnairesList, $questionnaire["ID"]);
        }

        $wasQuestionSent = false;
        if (count($questionnairesList) > 0) {
            $wasQuestionSent = $this->opalDB->countLockedQuestionnaires(implode(", ", $questionnairesList));
            $wasQuestionSent = intval($wasQuestionSent["total"]);
        }

        $nobodyUpdated = $this->questionnaireDB->canRecordBeUpdated(QUESTION_TABLE, $questionId, $lastUpdated["lastUpdated"], $lastUpdated["updatedBy"]);
        $nobodyUpdated = intval($nobodyUpdated["total"]);

        if ($nobodyUpdated && !$wasQuestionSent){
            $this->questionnaireDB->markAsDeleted(QUESTION_TABLE, $questionId);
            $response['value'] = true; // Success
            $response['message'] = 200;
            return $response;
        }
        else if (!$nobodyUpdated) {
            $response['value'] = false; // conflict error. Somebody already updated the question or record does not exists.
            $response['message'] = 409;
            return $response;
        } else {
            $response['value'] = false; // Question locked.
            $response['message'] = 423;
            return $response;
        }
    }
}
?>