<?php

/**
 *
 * Questionnaire-Question class
 */
class Question {

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
     *
     * Gets a list of existing questions
     *
     * @return array $questions : the list of existing questions
     */
    public function getQuestions($userId = ""){
        $questions = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $host_questionnaire_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_questionnaire_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "SELECT
                    q.ID AS ID,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR,
                    q.private,
                    q.typeId AS answertype_Id,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS answertype_name_EN,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS answertype_name_FR,
                    q.final
                    FROM question q LEFT JOIN type t ON t.ID = q.typeId WHERE deleted = 0 AND (OAUserId = '".$userId."' OR private = 0);";
            $query_questionnaire = $host_questionnaire_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query_questionnaire->execute();
            $questionsLists = $query_questionnaire->fetchAll();

            foreach ($questionsLists as $row){
                $sql = "SELECT (SELECT d.content FROM dictionary d WHERE d.contentId = l.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN, (SELECT d.content FROM dictionary d WHERE d.contentId = l.name AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR FROM library l RIGHT JOIN libraryQuestion lq ON lq.libraryId = l.ID WHERE lq.questionId = :questionId";

                $query_questionnaire = $host_questionnaire_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query_questionnaire->bindParam(':questionId', $row["ID"], PDO::PARAM_INT);
                $query_questionnaire->execute();
                $libraries = $query_questionnaire->fetchAll();
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

                $query_questionnaire = $host_questionnaire_db_link->prepare( "SELECT DISTINCT qst.ID FROM questionnaire qst RIGHT JOIN section s ON s.questionnaireId = qst.ID RIGHT JOIN questionSection qs ON qs.sectionId = s.ID WHERE qs.questionId = :questionId" );
                $query_questionnaire->bindParam(':questionId', $row["ID"], PDO::PARAM_INT);
                $query_questionnaire->execute();
                $questionnaires = $query_questionnaire->fetchAll();
                $questionnairesList = array();
                foreach ($questionnaires as $questionnaire) {
                    array_push($questionnairesList, $questionnaire["ID"]);
                }

                $questionLocked = 0;
                if (count($questionnairesList) > 0) {
                    $questionnairesList = implode(", ", $questionnairesList);
                    $query = $host_db_link->prepare("SELECT COUNT(*) AS total FROM questionnairecontrol WHERE QuestionnaireDBSerNum IN ( $questionnairesList )");
                    $query->execute();
                    $questionLocked = $query->fetch();
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
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $questions;

        }
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
     *
     * Mark a question as deleted. A question can only being marked as deleted if was never sent to a patient.
     * Nothing should be ever deleted from the database!
     *
     * @param $questionId (ID of the question), $userId (ID of the user who requested the deletion)
     * @return array $response : response
     */
    public function deleteQuestion($questionId, $userId = -1) {
        if ($userId == -1) {
            $response['value'] = false; // User is not identified.
            $response['message'] = 401;
            return $response;
        }
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $host_questionnaire_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_questionnaire_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            /*
             * Storing the last updated time during the process of validation of deletion in case somebody else modified
             * the question.
             * */
            $query_questionnaire = $host_questionnaire_db_link->prepare("SELECT lastUpdated, updatedBy FROM question WHERE ID = :questionId;");
            $query_questionnaire->bindParam(':questionId', $questionId, PDO::PARAM_INT);
            $query_questionnaire->execute();
            $lastUpdatedOrigin = $query_questionnaire->fetch();

            /*
             * If the user is trying to delete a private question that does not belongs to him/ger, reject the request
             * */
            $query_questionnaire = $host_questionnaire_db_link->prepare("SELECT COUNT(*) AS total FROM question WHERE ID = :questionId AND (OAUserId = :userId OR private = 0);");
            $query_questionnaire->bindParam(':questionId', $questionId, PDO::PARAM_INT);
            $query_questionnaire->bindParam(':userId', $userId, PDO::PARAM_INT);
            $query_questionnaire->execute();
            $accessAuthorized = $query_questionnaire->fetch();
            $accessAuthorized = (intval($accessAuthorized["total"]) > 0?true:false);

            if (!$accessAuthorized) {
                $response['value'] = false; // Unauthorized deletion request.
                $response['message'] = 403;
                return $response;
            }

            /* Loading the username of the user requesting the deletion */
            $query = $host_db_link->prepare("SELECT username FROM oauser WHERE OAUserSerNum = :userId");
            $query->bindParam(':userId', $userId, PDO::PARAM_INT);
            $query->execute();
            $username = $query->fetch();
            $username = $username["username"];

            /*
             * Listing all the questionnaires with the question to be deleted.
             * */
            $query_questionnaire = $host_questionnaire_db_link->prepare( "SELECT DISTINCT qst.ID FROM questionnaire qst RIGHT JOIN section s ON s.questionnaireId = qst.ID RIGHT JOIN questionSection qs ON qs.sectionId = s.ID WHERE qs.questionId = :questionId" );
            $query_questionnaire->bindParam(':questionId', $questionId, PDO::PARAM_INT);
            $query_questionnaire->execute();
            $questionnaires = $query_questionnaire->fetchAll();
            $questionnairesList = array();
            foreach ($questionnaires as $questionnaire) {
                array_push($questionnairesList, $questionnaire["ID"]);
            }

            /*If the question was already sent to any patient, it cannot be deleted*/
            $wasQuestionSent = false;
            if (count($questionnairesList) > 0) {
                $questionnairesList = implode(", ", $questionnairesList);
                $query = $host_db_link->prepare("SELECT COUNT(*) AS total FROM questionnairecontrol WHERE QuestionnaireDBSerNum IN ( $questionnairesList )");
                $query->execute();
                $wasQuestionSent = $query->fetch();
                $wasQuestionSent = intval($wasQuestionSent["total"]);
            }

            /* if the question was not updated during the verification process, it can be deleted */
            $query_questionnaire = $host_questionnaire_db_link->prepare("SELECT COUNT(*) AS total FROM question WHERE ID = :questionId AND lastUpdated = :lastUpdated AND updatedBy = :updatedBy;");
            $query_questionnaire->bindParam(':questionId', $questionId, PDO::PARAM_INT);
            $query_questionnaire->bindParam(':lastUpdated', $lastUpdatedOrigin["lastUpdated"], PDO::PARAM_STR);
            $query_questionnaire->bindParam(':updatedBy', $lastUpdatedOrigin["updatedBy"], PDO::PARAM_STR);
            $query_questionnaire->execute();
            $nobodyUpdated = $query_questionnaire->fetch();
            $nobodyUpdated = intval($nobodyUpdated["total"]);

            if ($nobodyUpdated && !$wasQuestionSent){
                $sql = "UPDATE question SET deleted = 1, deletedBy = :username, updatedBy = :username WHERE ID = :id ;";
                $query_questionnaire = $host_questionnaire_db_link->prepare( $sql );
                $query_questionnaire->bindParam(':username', $username, PDO::PARAM_STR);
                $query_questionnaire->bindParam(':id', $questionId, PDO::PARAM_INT);
                $query_questionnaire->execute();

                $response['value'] = true; // Success
                $response['message'] = 200;
                return $response;
            }
            else if (!$nobodyUpdated) {
                $response['value'] = false; // conflict error. Somebody already updated the question.
                $response['message'] = 409;
                return $response;
            } else {
                $response['value'] = false; // Question locked.
                $response['message'] = 423;
                return $response;
            }

        } catch (PDOException $e) {
            $response['value'] = false;
            $response['message'] = $e->getMessage();
            return $response;
        }
    }
}
?>