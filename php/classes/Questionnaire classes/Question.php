<?php

/* Questionnaire-Question class */

class Question {
	public $serNum;
	public $question;
	public $type;
	public $selected;

	public function __construct($serNum, $question, $type, $selected){
		$this->serNum = $serNum;
		$this->question = $question;
		$this->type = $type;
		$this->selected = $selected;
	}

	/* Add new question into table.
	 * @param: $questionAdded-array that contains necessary infromation for a question to be stored into table
	 * @return null
	 */
 	public function addQuestion($questionAdded){
 		// properties of a question
		$text_EN = $questionAdded['text_EN'];
		$text_FR = $questionAdded['text_FR'];
		$answertype_serNum = $questionAdded['answertype_serNum'];
		$questiongroup_serNum = $questionAdded['questiongroup_serNum'];
		$created_by = $questionAdded['created_by'];

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
						created_by
					)
				VALUES (
					\"$text_EN\",
					\"$text_FR\",
					'$questiongroup_serNum',
					'$answertype_serNum',
					'$created_by'
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}
}
?>