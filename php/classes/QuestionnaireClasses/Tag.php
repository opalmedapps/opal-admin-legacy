<?php

/* Questionnaire-Tag class */

include_once('questionnaire.inc');

class Tag {

	/* Add new tag
	 * @param: $tagAdded-new tag inserted
	 */
	public function addTag($tagAdded){
		$name_EN = $tagAdded['name_EN'];
		$name_FR = $tagAdded['name_FR'];
		$level = $tagAdded['level'];
		$created_by = $tagAdded['created_by'];
		$last_updated_by = $tagAdded['last_updated_by'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO
					QuestionnaireTag(
						name_EN,
						name_FR,
						level,
						created_by,
						last_updated_by
					)
				VALUES(
					\"$name_EN\",
					\"$name_FR\",
					'$level',
					'$created_by',
					'$last_updated_by'
				)
			";
			$query = $host_db_link->prepare($sql);
			$query->execute();
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
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
					name_EN,
					name_FR
				FROM
					QuestionnaireTag
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$serNum = $data[0];
				$level = $data[1];
				$name_EN = $data[2];
				$name_FR = $data[3];

				$tag = array(
					'serNum' 			=> $serNum,
					'level'  			=> $level,
					'name_EN'			=> $name_EN,
					'name_FR'			=> $name_FR,
					'added'				=> 0
				);

				array_push($tags, $tag);
			}
			return $tags;

		} catch(PDOException $e) {
			echo $e->getMessage();
			return $tags;
		}
	}
}
?>