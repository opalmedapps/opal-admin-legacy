<?php

/* Questionnaire-Library class
 * Dependencies: Questionnaire-Tag,
 *				 Questionnaire-AnswerType,
 *				 Questionnaire-QuestionGroup,
 *				 Questionnaire-Question,
 *				 Questionnaire-Category
 */
include_once('Tag.php');
include_once('AnswerType.php');
include_once('Group.php');
include_once('Question.php');
include_once('Category.php');

class Library{
	public $serNum;
	public $name;
	public $private;
	public $created_by;
	public $tags;
	public $categories;

	public function __construct($serNum, $name, $private, $created_by){
		$this->serNum = $serNum;
		$this->name = $name;
		$this->private = $private;
		$this->created_by = $created_by;
		$this->categories = array();
		$this->tags = array();
	}

	public function addCategory($cat){
		array_push($this->categories,$cat);
	}

	public function addTag($tag){
		// check if already exist
		$existance = false;
		foreach($this->tags as $tag){
			if($tag->serNum == $tag->serNum){
				$existance = true;
				break;
			}
		}
		if(!existance){
			array_push($this->tags,$tag);
		}
	}

	/* Read libraries that already exist in the db
	 * @param: null
	 * @return groupings as array
	 **************INCOMPLETE*****************
	 Nested requests for getting queries&push ----> optimize??
	 */
	public function readInLibrary(){
		$groupings = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$librarySQL = "
				SELECT
					serNum,
					name_EN,
					private,
					created_by
				FROM
					QuestionnaireLibrary
			";
			$libraryQ = $host_db_link->prepare($librarySQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$libraryQ->execute();

			//fetch
			while($librow = $libraryQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$librarySerNum = $librow[0];
				$libraryName = $librow[1];
				$libraryPrivate = $librow[2];
				$libraryCreated = $librow[3];

				$curr_library = new Library($librarySerNum, $libraryName, $libraryPrivate, $libraryCreated);
				array_push($groupings, $curr_library);

				//read category in each library
				$catSQL = "
					SELECT
						category_EN
					FROM
						Questiongroup_library,
						Questiongroup,
						QuestionnaireLibrary
					WHERE
						Questiongroup_library.questiongroup_serNum = Questiongroup.serNum
					AND
						Questiongroup_library.library_serNum = QuestionnaireLibrary.serNum
					AND
						QuestionnaireLibrary.serNum = $librarySerNum
					GROUP BY
						Questiongroup.category_EN
				";
				$catQ = $host_db_link->prepare($catSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$catQ->execute();

				//fetch
				while($catrow = $catQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
					$catName = $catrow[0];

					$curr_cat = new Category($catName);
					$curr_library->addCategory($curr_cat);

					//read in all question groups for each category
					$groupSQL = "
						SELECT
							Questiongroup.serNum,
							Questiongroup.name_EN
						FROM
							Questiongroup,
							Questiongroup_library
						WHERE
							questiongroup_serNum = Questiongroup_library.questiongroup_serNum
						AND
							Questiongroup.category_EN = \"$catName\"
						AND
							Questiongroup_library.library_serNum = $librarySerNum
					";
					$groupQ = $host_db_link->prepare($groupSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$groupQ->execute();

					while($grouprow = $groupQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$groupSerNum = $grouprow[0];
						$groupName = $grouprow[1];

						$curr_group = new Group($groupSerNum, $groupName);
						$curr_cat->addGroup($curr_group);

						//read in all questions with options for each group
						$questionSQL = "
							"
					}
				}
			}
		}
	}
}
?>