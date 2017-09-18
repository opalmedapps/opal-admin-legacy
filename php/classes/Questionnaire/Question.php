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
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT
					QuestionnaireQuestion.serNum,
					QuestionnaireQuestion.text_EN,
					QuestionnaireQuestion.text_FR,
					Questiongroup.serNum,
					Questiongroup.name_EN,
					Questiongroup.name_FR,
					Questiongroup.category_EN,
					Questiongroup.category_FR,
					Questiongroup.private,
					QuestionnaireAnswerType.serNum,
					QuestionnaireAnswerType.name_EN,
					QuestionnaireAnswerType.name_FR,
					QuestionnaireLibrary.serNum,
					QuestionnaireLibrary.name_EN,
					QuestionnaireLibrary.name_FR
				FROM 
					((( Questiongroup
						INNER JOIN
					Questiongroup_library
						ON Questiongroup.serNum = Questiongroup_library.questiongroup_serNum
						INNER JOIN
					QuestionnaireLibrary
						ON Questiongroup_library.library_serNum = QuestionnaireLibrary.serNum )

						RIGHT JOIN 
							QuestionnaireQuestion 
						ON
							Questiongroup.serNum = QuestionnaireQuestion.questiongroup_serNum )
						LEFT JOIN
							QuestionnaireAnswerType
						ON
							QuestionnaireQuestion.answertype_serNum = QuestionnaireAnswerType.serNum )
				ORDER BY
					QuestionnaireQuestion.serNum ASC
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($row = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$questionSerNum = $row[0];
				$text_EN = $row[1];
				$text_FR = $row[2];
				$groupSerNum = $row[3];
				$groupNameEN = $row[4];
				$groupNameFR = $row[5];
				$groupCatEN = $row[6];
				$groupCatFR = $row[7];
				$groupPrivate = $row[8];
				$atSerNum = $row[9];
				$atNameEN = $row[10];
				$atNameFR = $row[11];
				$libSerNum = $row[12];
				$libNameEN = $row[13];
				$libNameFR = $row[14];


				$questionArray = array (
					'serNum'				=> $questionSerNum,
					'text_EN'				=> $text_EN,
					'text_FR'				=> $text_FR,
					'group_serNum'			=> $groupSerNum,
					'group_name_EN'			=> $groupNameEN,
					'group_name_FR'			=> $groupNameFR,
					'group_category_EN'		=> $groupCatEN,
					'group_category_FR'		=> $groupCatFR,
					'private'				=> $groupPrivate,
					'answertype_serNum'		=> $atSerNum,
					'answertype_name_EN'	=> $atNameEN,
					'answertype_name_FR'	=> $atNameFR,
					'library_serNum'		=> $libSerNum,
					'library_name_EN'		=> $libNameEN,
					'library_name_FR'		=> $libNameFR
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
			return $questionDetails;
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
	public function deleteQuestion($questionSerNum) {
		$response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                DELETE FROM
                    QuestionnaireQuestion
                WHERE
                    QuestionnaireQuestion.serNum = $questionSerNum
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
}
?>