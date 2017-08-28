<?php
/**
 *
 * Questionnaire-QuestionGroup class 
 */
class QuestionGroup{

	/**
     *
     * Inserts a question group
     *
     * @param array $questionGroup  : the question group details
     * @return void
     */
	public function insertQuestionGroup($questionGroup){

		$name_EN 			= $questionGroup['name_EN'];
		$name_FR 			= $questionGroup['name_FR'];
		$category_EN 		= $questionGroup['category_EN'];
		$category_FR 		= $questionGroup['category_FR'];
		$private 			= $questionGroup['private'];
		$last_updated_by 	= $questionGroup['last_updated_by'];
		$created_by 		= $questionGroup['created_by'];
		$library_serNum 	= $questionGroup['library_serNum'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO
					QuestionGroup (
						name_EN,
						name_FR,
						category_EN,
						category_FR,
						private,
						last_updated_by,
						created_by
					)
				VALUES (
					\"$name_EN\",
					\"$name_FR\",
					\"$category_EN\",
					\"$category_FR\",
					'$private',
					'$last_updated_by',
					'$created_by'
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			// create new Questiongroup_library entry
			$questiongroup_serNum = $host_db_link->lastInsertId();

			$sql = "
				INSERT INTO
					Questiongroup_library (
						questiongroup_serNum,
						library_serNum,
						last_updated_by,
						created_by
					)
				VALUES (
					'$questiongroup_serNum',
					'$library_serNum',
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
     * Gets a list of existing question groups from a particular user
     *
     * @param integer $userid    : the user id
     * @return array $questionGroups : the list of existing question groups
     */
	public function getQuestionGroups($userid){
		$questionGroups = array();

		try{
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					name_EN,
					name_FR,
					serNum
				FROM
					Questiongroup
				WHERE
					private = 0
				OR
					created_by = $userid
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){

				$name_EN = $data[0];
				$name_FR = $data[1];
				$serNum = $data[2];

				$questionGroupArray = array(
					'name_EN'	=> $name_EN,
					'name_FR'	=> $name_FR,
					'serNum'	=> $serNum
				);
				array_push($questionGroups, $questionGroupArray);
			}
			return $questionGroups;

		} catch (PDOException $e) {
			return $e->getMessage();
			return $questionGroups;
		}
	}

	/**
     *
     * Gets a list of question groups with library included for a particular user
     *
     * @param integer $userid    : the user id
     * @return array $questionGroups : the list of question groups
     */
	public function getQuestionGroupsWithLibraries($userid){
		$questionGroups = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT
					Questiongroup.serNum,
					Questiongroup.name_EN,
					Questiongroup.name_FR,
					Questiongroup.category_EN,
					Questiongroup.category_FR,
					Questiongroup.private,
					QuestionnaireLibrary.serNum,
					QuestionnaireLibrary.name_EN,
					QuestionnaireLibrary.name_FR
				FROM
					( Questiongroup
						INNER JOIN
					Questiongroup_library
						ON Questiongroup.serNum = Questiongroup_library.questiongroup_serNum
						INNER JOIN
					QuestionnaireLibrary
						ON Questiongroup_library.library_serNum = QuestionnaireLibrary.serNum )
				WHERE
					Questiongroup.private = 0
				OR
					Questiongroup.created_by = $userid
				ORDER BY
					Questiongroup.serNum ASC
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				
				$serNum = $data[0];
				$name_EN = $data[1];
				$name_FR = $data[2];
				$category_EN = $data[3];
				$category_FR = $data[4];
				$private = $data[5];
				$library_serNum = $data[6];
				$library_name_EN = $data[7];
				$library_name_FR = $data[8];

				$group = array(
					'serNum'			=> $serNum,
					'name_EN'			=> $name_EN,
					'name_FR'			=> $name_FR,
					'category_EN'		=> $category_EN,
					'category_FR'		=> $category_FR,
					'private'			=> $private,
					'library_serNum'	=> $library_serNum,
					'library_name_EN'	=> $library_name_EN,
					'library_name_FR'	=> $library_name_FR,
					'tags'				=> array(),
					'questions'			=> array()
				);

				// retrieve tags 
				$tagSQL = "
					SELECT
						QuestionnaireTag.serNum,
						QuestionnaireTag.name_EN,
						QuestionnaireTag.name_FR
					FROM
						QuestionnaireTag,
						Questiongroup_tag
					WHERE
						Questiongroup_tag.questiongroup_serNum = $serNum
					AND
						QuestionnaireTag.serNum = Questiongroup_tag.tag_serNum
				";
				$query1 = $host_db_link->prepare($tagSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$query1->execute();
				$result = $query1->rowCount();
				// if query not empty
				if($result != 0){
					while($data = $query1->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$tag_serNum = $data[0];
						$tag_name_EN = $data[1];
						$tag_name_FR = $data[2];

						$tag = array(
							'serNum'	=> $tag_serNum,
							'name_EN'	=> $tag_name_EN,
							'name_FR'	=> $tag_name_FR
						);
						array_push($group['tags'], $tag);
					}
				}

				// retrieve questions
				$questionSQL = "
					SELECT
						QuestionnaireQuestion.serNum,
						QuestionnaireQuestion.text_EN,
						QuestionnaireQuestion.text_FR,
						QuestionnaireAnswerType.serNum,
						QuestionnaireAnswerType.name_EN,
						QuestionnaireAnswerType.name_FR,
						QuestionnaireAnswerType.category_EN,
						QuestionnaireAnswerType.category_FR
					FROM
						QuestionnaireQuestion,
						QuestionnaireAnswerType
					WHERE
						QuestionnaireQuestion.questiongroup_serNum = $serNum
					AND
						QuestionnaireAnswerType.serNum = QuestionnaireQuestion.answertype_serNum
				";

				$query2 = $host_db_link->prepare($questionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$query2->execute();

				while($data = $query2->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
					$question_serNum = $data[0];
					$question_text_EN = $data[1];
					$question_text_FR = $data[2];
					$answertype_serNum = $data[3];
					$answertype_name_EN = $data[4];
					$answertype_name_FR = $data[5];
					$answertype_category_EN = $data[6];
					$answertype_category_FR = $data[7];

					$question = array(
						'serNum'					=> $question_serNum,
						'text_EN'					=> $question_text_EN,
						'text_FR'					=> $question_text_FR,
						'answertype_name_EN'		=> $answertype_name_EN,
						'answertype_name_FR'		=> $answertype_name_FR,
						'answertype_category_EN'	=> $answertype_category_EN,
						'answertype_category_FR'	=> $answertype_category_FR,
						'options'					=> array()
					);

					$optionSQL = "
						SELECT
							serNum,
							text_EN,
							text_FR,
							position
						FROM
							QuestionnaireAnswerOption
						WHERE
							answertype_serNum = $answertype_serNum
						ORDER BY
							position ASC
					";

					$query3 = $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$query3->execute();

					while($data = $query3->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$option = array(
							'serNum'	=> $data[0],
							'text_EN'	=> $data[1],
							'text_FR'	=> $data[2],
							'position'	=> $data[3]
						);
						array_push($question['options'], $option);
					}
					
					array_push($group['questions'], $question);
				}

				array_push($questionGroups, $group);
			}
			return $questionGroups;

		} catch (PDOException $e) {
			return $e->getMessage();
			return $questionGroups;
		}
	}
}
?>