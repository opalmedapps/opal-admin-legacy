<?php
	/**
	 *
	 * Questionnaire-Library class
	 */
class Library{

	/**
     *
     * Get a list of existing libraries for a particular user
     *
     * @param integer $userid    : the user id
     * @return array $libraries : the list of existing libraries
     */
	public function getLibraries($userid){

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

			$libraryQuery = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$libraryQuery->execute();

			while($row = $libraryQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$serNum = $row[0];
				$name_EN = $row[1];
				$name_FR = $row[2];

				$libraryArray = array(
					'serNum'	=> $serNum,
					'name_EN'	=> $name_EN,
					'name_FR'	=> $name_FR,
					'categories'=> array()
				);

				$categorySQL = "
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

				$categoryQuery = $host_db_link->prepare($categorySQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$categoryQuery->execute();

				while($data = $categoryQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
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

	/**
     *
     * Inserts a library into our database
     *
     * @param array $libraryDetails  : the library details
     * @return void
     */
	public function insertLibrary($libraryDetails){
		
		$name_EN 			= $libraryDetails['name_EN'];
		$name_FR 			= $libraryDetails['name_FR'];
		$private 			= $libraryDetails['private'];
		$created_by 		= $libraryDetails['created_by'];
		$last_updated_by 	= $libraryDetails['last_updated_by'];

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