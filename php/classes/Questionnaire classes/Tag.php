<?php

/* Questionnaire-Tag class */

class Tag {
	public $serNum;
	public $level;
	public $ref_tag_serNum;
	public $name;

	public function __construct($serNum, $level, $ref_tag_serNum, $name){
		$this->serNum = $serNum;
		$this->level = $level;
		$this->ref_tag_serNum = $ref_tag_serNum;
		$this->name = $name;
	}
	/* Check if the tag exist
	 * @param: $tag-tag need to be checked
	 */
	public function ifExist($tag){

	}

	/* Add new tag
	 * @param: $tagAdded-new tag inserted
	 */
	public function addTag($tagAdded){
		$
	}

	/* Get tags from table
	 * @return array $tags
	 */
	public function getTags(){
		$tags = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT
					serNum,
					level,
					ref_tag_serNum,
					name_EN
				FROM
					QuestionnaireTag
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$serNum = $data[0];
			$level = $data[1];
			$ref_tag_serNum = $data[2];
			$name_EN = $data[3];
			// properties unselected
			//$name_FR = $data[4];
			//$last_updated = $data[5];
			//$created = $data[6];
			//$last_updated_by = $data[7];
			$created_by = $data[8];

			$tags = array(
				'serNum' 			=> $serNum;
				'level'  			=> $level;
				'ref_tag_serNum' 	=> $ref_tag_serNum;
				'name_EN'			=>$name_EN;
			);

			return $tags;

		} catch (PDOException $e) {
			echo $e->getMessage();
			return $tags;
		}
	}
}
?>