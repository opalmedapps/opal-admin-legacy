<?php

/**
 *
 * Questionnaire-Tag class 
 */
class Tag {

	/**
     *
     * Inserts a tag into our database
     *
     * @param array $tagDetails  : the tag details
     * @return void
     */
	public function insertTag($tagDetails){

		$name_EN 			= $tagDetails['name_EN'];
		$name_FR 			= $tagDetails['name_FR'];
		$level 				= $tagDetails['level'];
		$created_by 		= $tagDetails['created_by'];
		$last_updated_by 	= $tagDetails['last_updated_by'];

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

	/**
     *
     * Gets a list of existing tags 
     *
     * @return array $tags : the list of existing tags
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