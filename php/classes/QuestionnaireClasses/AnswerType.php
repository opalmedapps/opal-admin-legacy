<?php

/* Questionnaire-AnswerType class */
class AnswerType {
	public $serNum;
	public $type;
	public $private;
	public $category;
	public $num_options;
	public $minCaption;
	public $maxCaption;
	public $optiosn;

	public function __construct($serNum, $type, $private, $category){
		$this->serNum = $serNum;
		$this->type = $type;
		$this->private = $private;
		$this->num_options = 0;
		$this->options = array();
	}

	// setter
	function setMinCaption($caption){
		$this->minCaption = $caption;
	}

	function setMaxCaption($caption){
		$this->maxCaption = $caption;
	}

	function setAndSwitchMinCaption($caption){
		$this->maxCaption = $this->minCaption;
		$this->minCaption = $caption;
	}

	public function addOption($opt){
		$this->num_options++;
		array_push($this->options, $opt);
	}

	/* Add new answer type into table.
	 * @param: $answerTypeAdded-array that contains necessary infromation for a answer type to be stored into table
	 * @return null
	 */
	public function addNewAnswerType($answerTypeAdded){
		// properties
		$category_EN = $answerTypeadded['category_EN'];
		$category_FR = $answerTypeadded['category_FR'];
		$private = $answerTypeadded['private'];
		$created_by = $answerTypeadded['created_by'];
		$options = $answerTypeadded['options'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO
					QuestionnaireAnswerType (
						category_EN,
						category_FR,
						private,
						created_by
					)
				VALUES (
					\"$category_EN\",
					\"$category_FR\",
					'$private',
					'$created_by'
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();
			// add options
			$answertype_serNum = $host_db_link->lastInsertId();

			foreach ($options as $option) {
				$sql = "
                    INSERT INTO 
                        QuestionnaireAnswerOption (
                            text_EN,
                            text_FR,
                            answertype_serNum,
                            position,
                            created_by
                        )
                    VALUES (
                        \"$text_EN\",
						\"$text_FR\",
						'$answertype_serNum',
						'$position',
						'$created_by'
                    )
                    ON DUPLICATE KEY UPDATE
                        answertype_serNum = '$answertype_serNum'
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();
			}

		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}

	/* Read in answer types from table.
	 * @param: null
	 * @return answerTypes as array
	 */
	public function getAnswerTypes(){
		$answerTypes = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT
					serNum,
					name_EN,
					private,
					category_EN,
					created_by
				FROM
					QuestionnaireAnswerType
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			//fetch
			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$serNum = $data[0];
				$name = $data[1];
				$private = $data[2];
				$category = $data[3];
				$created_by = $data[4];

				$curr_qt = new AnswerType($serNum, $name, $private, $category);
				array_push($answerTypes, $curr_qt);

				//options for each answer type
				if($category = 'Linear Scale'){
					$optionSQL = "
						SELECT
							text_EN,
							caption_EN
						FROM
							QuestionnaireAnswerOption
						WHERE
							QuestionnaireAnswerOption.answertype_serNum = $serNum
					";
					$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$optionResult->execute();

					$min = null;
					while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$text_EN = $optionrow[0];
						$caption_EN = $optionrow[1];

						$curr_qt->addOption($text_EN);
						//set min if 1st caption
						if($caption_EN!=null && $min==null){
							$min = $caption_EN;
							$curr_qt->setMinCaption($caption_EN);
						}
						//check if caption is max and set
						else if($caption_EN!=null && $min!=null){
							if($min>$text_EN){
								$curr_qt->setAndSwitchMinCaption($caption_EN);
							}
							else {
								$curr_qt->setMaxCaption($caption_EN);
							}
						}
					}
				}
				else if($category = 'Short Answer'){
					// no option
				}
				else {
					$optionSQL = "
						SELECT
							text_EN
						FROM
							QuestionnaireAnswerOption
						WHERE
							QuestionnaireAnswerOption.answertype_serNum = $serNum
					";
					$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$optionResult->execute();

					while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$curr_qt->addOption($optionrow[0]);
					}
				}
			}
			return $answerTypes;
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $answerTypes;
	 	}

	}
}

?>