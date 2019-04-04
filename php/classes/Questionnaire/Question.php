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
    public function getQuestions(){
        $questions = array();
        try {
            $host_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "SELECT
                    q.ID AS ID,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR,
                    q.private,
                    q.typeId AS answertype_Id,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = 2) AS answertype_name_EN,
                    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = 1) AS answertype_name_FR
                    FROM question q LEFT JOIN type t ON t.ID = q.typeId WHERE deleted = 0;";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();
            $questionsLists = $query->fetchAll();

            foreach ($questionsLists as $row){
                $sql = "SELECT (SELECT d.content FROM dictionary d WHERE d.contentId = l.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN, (SELECT d.content FROM dictionary d WHERE d.contentId = l.name AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR FROM library l RIGHT JOIN libraryQuestion lq ON lq.libraryId = l.ID WHERE lq.questionId = :questionId";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->bindParam(':questionId', $row["ID"], PDO::PARAM_INT);
                $query->execute();
                $libraries = $query->fetchAll();
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

                $questionArray = array (
                    'serNum'				=> $row["ID"],
                    'text_EN'				=> $row["text_EN"],
                    'text_FR'				=> $row["text_FR"],
                    'private'				=> $row["private"],
                    'answertype_serNum'		=> $row["answertype_Id"],
                    'answertype_name_EN'	=> $row["answertype_name_EN"],
                    'answertype_name_FR'	=> $row["answertype_name_FR"],
                    'library_name_EN'		=> $libNameEn,
                    'library_name_FR'		=> $libNameFr,
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
     * Deletes a question
     *
     * @param integer $questionSerNum : the question serial number
     * @return array $response : response
     */
    public function deleteQuestion($questionId, $userId = -1) {
        try {

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $query = $host_db_link->prepare("SELECT username FROM oauser WHERE OAUserSerNum = :userId");
            $query->bindParam(':userId', $userId, PDO::PARAM_INT);
            $query->execute();
            $username = $query->fetch();
            $username = $username["username"];

            $host_db_link = new PDO( QUESTIONNAIRE_DB_2019_DSN, QUESTIONNAIRE_DB_2019_USERNAME, QUESTIONNAIRE_DB_2019_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "UPDATE question SET deleted = 1, deletedBy = :username, updatedBy = :username WHERE ID = :id ;";
            $query = $host_db_link->prepare( $sql );
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':id', $questionId, PDO::PARAM_INT);
            $query->execute();

            $response['value'] = true; // Success
            $response['message'] = null;
            return $response;

        } catch (PDOException $e) {
            $response['value'] = false;
            $response['message'] = $e->getMessage();
            return $response;
        }
    }
}
?>