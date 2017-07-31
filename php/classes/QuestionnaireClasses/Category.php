<?php
/* Questionnaire-Category class */

include_once('questionnaire.inc');

Class Category{
	/* Get a list of categories
	 * @param: none
	 * @return categories as an array
	 */
	public function getCategory(){
		$categories = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT
					category_EN,
					category_FR
				FROM
					Questiongroup
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$category_EN = $data[0];
				$category_FR = $data[1];

				$catArray = array(
					'category_EN'	=> $category_EN,
					'category_FR'	=> $category_FR
				);

				array_push($categories, $catArray);
			}

			return $categories;
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $categories;
		}
	}

}
?>