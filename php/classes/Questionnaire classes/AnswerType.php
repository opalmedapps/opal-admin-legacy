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

		} catch( PDOException $e) {
			return $e->getMessage();
		}
	}

	/* Read in answer types from table.
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

		}

	}
}

?>