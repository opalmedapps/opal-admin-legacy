<?php

/* Questionnaire class 
 * Dependencies: Questionanire-Tag
 */
include_once('Tag.php');

class Questionnaire{
	public $serNum;
	public $name;
	public $private;
	public $publish;
	public $created_by;
	public $tags;

	public function __construct($serNum, $name, $private, $publish, $created_by){
		$this->serNum = $serNum;
		$this->name = $name;
		$this->private = $private;
		$this->publish = $publish;
		$this->created_by = $created_by;
		$this->tags = array();
	}

	public function addTag($tag){
		array_push($this->tags,$tag);
	}

	/* Get Questionnaire
	 * @return questionnaires as array
	 */
	public function getQuestionnaire($post){
		$doctor_id = $post['doctorSerNum'];
		$questionnaires = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT
					serNum,
					name_EN,
					private,
					publish,
					created_by
				FROM
					Questionnaire
				WHERE
					private = 0
				OR
					created_by ="
					. $doctor_id . 
					";";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			// fetch
			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

	 			$serNum 	= $data[0];
	 			$name_EN   	= $data[1];
	 			$private 	= $data[2];
	 			$publish 	= $data[3];
	 			$created_by = $data[4];

	 			$questionnaireObj = new Questionnaire($serNum, $name_EN, $private, $publish, $created_by);
	 			array_push($questionnaires, $questionnaireObj);

	 			// get tags
				$tagsql = "
					SELECT
						tag_serNum,
						name_EN
					FROM
						Questionnaire_tag,
						QuestionnaireTag
					WHERE
						Questionnaire_tag.tag_serNum = QuestionnaireTag.serNum
					AND
						Questionnaire_tag.questionnaire_serNum ="
						. $data['serNum'] . 
						";";

				$tagquery = $host_db_link->prepare( $tagsql );
				$tagquery->execute();

				while ($tagdata = $tagquery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
					$tag_serNum = $tagdata[0];
					$tagName_EN = $tagdata[1];

					$tagObj = new Tag($tag_serNum, $tagName_EN);
					$questionnaireObj->addTag($tagObj);
				}
	 		} catch (PDOException $e) {
	 			echo $e->getMessage();
	 			return $questionnaires;
	 		}
		}
	}

	public function createQuestionnaire($post){
		// properties
		$name_EN = $post['name_EN'];
		$name_FR = $post['name_FR'];
		$private = $post['private'];
		$publish = $post['publish'];
		$created_by = $post['created_by'];
		$tags = $post['tags'];
		$questiongroups = $post['questionGroups']

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				INSERT INTO
					Questionnaire(
						name_EN,
						name_FR,
						private,
						publish,
						created_by
					)
				VALUES(
					\"$name_EN\",
					\"$name_FR\",
					$private,
					$publish,
					$created_by
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$questionnaire_id =  $host_db_link->lastInsertId();
			//position counter
			$i = 0;
			foreach($questiongroups as $group){
				$group_id = $group->serNum;
				$optional = $group->optional;

				$sql = "
					INSERT INTO
						Questionnaire_questiongroup(
							questionnaire_serNum,
							questiongroup_serNum,
							position
						)
					VALUES(
						$questionnaire_id,
						$group_id,
						$i
					)
				";
				i++;
			}

			//add tag
			

		} catch( PDOException $e) {
			return $e->getMessage();
		}
	}

	
	public function addGroupToQuestionnaire($post){

	}
}
?>