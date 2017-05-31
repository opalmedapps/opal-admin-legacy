<?php
/* Questionnaire-QuestionGroup class */

class Group{
	public $serNum;
	public $name;
	public $questions;
	public $include;
	public $tags;
	public $reveal;

	public function __construct($serNum, $name){
		$this->serNum = $serNum;
		$this->name = $name;
		$this->questions = array();
		$this->include = false;
		$this->tags = array();
		$this->reveal = false;
	}

	public function addQuestion($question){
		array_push($this->questions,$question);
	}

	public function addTag($tag){
		array_push($this->tags,$tag);
	}

	/* Add new Question group
	 * @param: $QuestionGroupadded-array
	 * @return null
	 */
	public function addQuestionGroup($QuestionGroupadded){
		$name_EN = $QuestionGroupadded['name_EN'];
		$name_FR = $QuestionGroupadded['name_FR'];
		$category_EN = $QuestionGroupadded['category_EN'];
		$category_FR = $QuestionGroupadded['category_FR'];
		$private = $QuestionGroupadded['private'];
		$created_by = $QuestionGroupadded['created_by'];
		$library_name = QuestionGroupadded['library_name'];

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
						created_by
					)
				VALUES (
					\"$name_EN\",
					\"$name_FR\",
					\"$category_EN\",
					\"$category_FR\",
					$private,
					$created_by
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			// create new Questionnaire_library entry
			$questiongroup_serNum = $host_db_link->lastInsertId();

			$sql = "
				INSERT INTO
					Questionnaire_library (
						questiongroup_serNum,
						library_serNum,
						created_by
					)
				VALUES (
					$questiongroup_serNum,
					\"$library_name\",
					created_by
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