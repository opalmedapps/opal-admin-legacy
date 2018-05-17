<?php

/**
 *
 * Questionnaire-AnswerType class 
 */
class AnswerType {

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
     * Gets a list of existing answer types
     *
     * @param integer $userId : the user id
     * @return array $answerTypes : the list of existing answer types
     */
	public function getAnswerTypes($userid){
		$answerTypes = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT
					serNum,
					name_EN,
					name_FR,
					private,
					category_EN,
					category_FR,
					created_by
				FROM
					QuestionnaireAnswerType
				WHERE
					private = 0
				OR
					created_by = $userid
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			// fetch
			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$serNum = $data[0];
				$name_EN = $data[1];
				$name_FR = $data[2];
				$private = $data[3];
				$category_EN = $data[4];
				$category_FR = $data[5];
				$created_by = $data[6];


                $curr_qt = array(
                    'serNum'        => $serNum,
                    'name_EN'       => $name_EN,
                    'name_FR'       => $name_FR,
                    'private'       => $private,
                    'category_EN'   => $category_EN,
                    'category_FR'   => $category_FR,
                    'created_by'    => $created_by,
                    'options'       => array()
                );

				// options for answer type
				if($category_EN === 'Short Answer' || $category_EN === 'Time' || $category_FR === 'Date'){
					// no option
				}
				else {
					$optionSQL = "
						SELECT 
							text_EN,
							text_FR,
							position
						FROM
							QuestionnaireAnswerOption
						WHERE
							answertype_serNum = $serNum
						ORDER BY
							position ASC
					";
					$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$optionResult->execute();

					while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$optext_EN = $optionrow[0];
						$optext_FR = $optionrow[1];
						$opposition = $optionrow[2];
						$optext = array(
							'position'	=> $opposition,
							'text_EN'	=> $optext_EN,
							'text_FR'	=> $optext_FR
						);
						array_push($curr_qt['options'], $optext);
					}
				}
				array_push($answerTypes, $curr_qt);
			}
			return $answerTypes;
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $answerTypes;
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
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT
					category_EN,
					category_FR
				FROM
					QuestionnaireAnswerType
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$category_EN = $data[0];
				$category_FR = $data[1];

				$answerTypeCategoryArray = array(
					'category_EN'	=> $category_EN,
					'category_FR'	=> $category_FR
				);

				array_push($answerTypeCategories, $answerTypeCategoryArray);
			}

			return $answerTypeCategories;
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $answerTypeCategories;
		}
	}
}

?>