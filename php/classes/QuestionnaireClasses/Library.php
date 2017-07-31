<?php

include_once('questionnaire.inc');

class Library{

	/* Read libraries in database
	 * @param: userid - used for getting private libraries
	 * @return libraries as array
	 */
	public function getLibrary($userid){
		$libraries = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT
					serNum,
					name_EN,
					name_FR
				FROM
					QuestionnaireLibrary
				WHERE
					private = 0 
				OR 
					created_by = $userid
			";

			$libraryQ = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$libraryQ->execute();

			while($row = $libraryQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$serNum = $row[0];
				$name_EN = $row[1];
				$name_FR = $row[2];

				$libraryArray = array(
					'serNum'	=> $serNum,
					'name_EN'	=> $name_EN,
					'name_FR'	=> $name_FR,
					'categories'=> array()
				);

				$catSQL = "
					SELECT DISTINCT
						category_EN,
						category_FR
					FROM
						Questiongroup_library,
						Questiongroup,
						QuestionnaireLibrary
					WHERE
						Questiongroup_library.questiongroup_serNum = Questiongroup.serNum
					AND
						Questiongroup_library.library_serNum = QuestionnaireLibrary.serNum
					AND
						QuestionnaireLibrary.serNum = $serNum
				";

				$catQ = $host_db_link->prepare($catSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$catQ->execute();

				while($data = $catQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
					$category_EN = $data[0];
					$category_FR = $data[1];

					$categoryArray = array(
						'category_EN'	=> $category_EN,
						'category_FR'	=> $category_FR
					);

					array_push($libraryArray['categories'], $categoryArray);
				}

				array_push($libraries, $libraryArray);
			}
			return $libraries;
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $libraries;
	 	}

	}


	/* Add library
	 * @param: post - containing details of library that need to be added
	 * @return none
	 */
	public function addLibrary($post){
		$name_EN = $post['name_EN'];
		$name_FR = $post['name_FR'];
		$private = $post['private'];
		$created_by = $post['created_by'];
		$last_updated_by = $post['last_updated_by'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				INSERT INTO
					QuestionnaireLibrary(
						name_EN,
						name_FR,
						private,
						last_updated_by,
						created_by
					)
				VALUES(
					\"$name_EN\",
					\"$name_FR\",
					'$private',
					'$created_by',
					'$last_updated_by'
				)
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 	}
	}
}
?>