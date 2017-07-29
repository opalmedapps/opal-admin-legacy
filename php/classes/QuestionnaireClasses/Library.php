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
						//Questions \ date, time, short answer
						$questionSQL = "
							SELECT
								question.serNum,
								question.text_EN,
								answertype.serNum,
								answeroption.text_EN
      						FROM 
      							QuestionnaireQuestion,
      							Questiongroup,
      							QuestionnaireAnswerType,
      							QuestionnaireAnswerOption
      						WHERE
      							QuestionnaireQuestion.questiongroup_serNum = Questiongroup.serNum
      						AND
      							QuestionnaireQuestion.answertype_serNum = QuestionnaireAnswerType.serNum
      						AND
      							QuestionnaireAnswerType.serNum = QuestionnaireAnswerOption.answertype_serNum
      						AND
      							Questiongroup.serNum = $groupSerNum
      						AND 
      							QuestionnaireAnswerOption.position = 1
						";
						$questionQ = $host_db_link->prepare($questionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
						$questionQ->execute();

						while($questionrow = $questionQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
							$serNum = $questionrow[0];
							$question = $questionrow[1];
							$typeSerNum = $questionrow[2];
							$selectedOp = $questionrow[3];

        					// read in answer types
        					$atSQL = "
        						SELECT
        							serNum,
        							name_EN,
        							private,
        							category_EN,
        							created_by
        						FROM
        							QuestionnaireAnswerType
        					";
        					$atQ = $host_db_link->prepare($atSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
							$atQ->execute();

							$atrow = $atQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
							$atSerNum = $atrow[0];
							$atName = $atrow[1];
							$atPrivate = $atrow[2];
							$atCat = $atrow[3];

							$curr_at =  new AnswerType($atSerNum, $atName, $atPrivate, $atCat);
							$curr_question = new Question($serNum, $question, $curr_at, $selectedOp);
							$curr_group->addQuestion($curr_question);

							//read in options for each answer type
							if ($atCat == 'Linear Scale') {
					         	$optionSQL = "
					         		SELECT 
					         			text_EN, 
					         			caption_EN
					         		FROM 
					         			QuestionnaireAnswerOption
					          		WHERE 
					          			QuestionnaireAnswerOption.answertype_serNum = $atSerNum
					          	";
					        	$optionQ = $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
								$optionQ->execute();
					        	$min = null;
					        	while($optionrow = $optionQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
					        		$text_EN = $optionrow[0];
					        		$caption_EN = $optionrow[1];

					            	$curr_at->addOption($text_EN);
					            	// set min if first caption
						            if ($caption_EN!=null && $min==null) {
						            	$min = $text_EN
						            	$curr_at->setMinCaption($caption_EN);
						            }
					            	else if ($caption_EN!=null && $min!=null) {
					              		if ($min > $text_EN) {
					                		$curr_at->setAndSwitchMinCaptions($caption_EN);
					              		}
					              		else {
					                		$curr_qt->setMaxCaption($caption_EN);
					              		}
					            	}
					            }
					        }
					        else if ($atCat == 'Short Answer' || $atCat == 'Date' || $atCat == 'Time') {
					          // no options
					        }
					        else {
					        	$optionSQL = "
						          	SELECT 
						          		text_EN
						          	FROM 
						          		QuestionnaireAnswerOption
						          	WHERE
						          		QuestionnaireAnswerOption.answertype_serNum = $atSerNum
					         	";
					          	$optionQ = $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
								$optionQ->execute();
					         	while($optionrow = $optionQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
					            	$curr_at->addOption($text_EN);
					          	}
					        }
					    }

					    //when question is either type date, time, or short answer
					    $questionSQL = "
					    	SELECT
					    		QuestionnaireQuestion.serNum,
					    		QuestionnaireQuestion.text_EN
					    	FROM
					    		QuestionnaireQuestion,
					    		Questiongroup,
					    		QuestionnaireAnswerType
					    	WHERE
					    		QuestionnaireQuestion.questiongroup_serNum = Questiongroup.serNum
					    	AND
					    		QuestionnaireQuestion.answertype_serNum = QuestionnaireAnswerType.serNum
					    	AND
					    		QuestionnaireAnswerType.serNum = 36
					    		OR
					    		QuestionnaireAnswerType.serNum = 37
					    		OR
					    		QuestionnaireAnswerType.serNum = 38
					    	AND
					    		Questiongroup.serNum = $groupSerNum
					    ";
					    $questionQ = $host_db_link->prepare($questionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
						$questionQ->execute();

						while($questionrow = $questionQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
							$serNum = $questionrow[0];
							$question = $questionrow[1];
							$typeSerNum = $questionrow[2];

							//read in answer type
							$atSQL = "
								SELECT
									serNum,
									text_EN,
									private,
									category_EN,
									created_by
								FROM
									QuestionnaireAnswerType
							";
							$atQ = $host_db_link->prepare($atSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
							$atQ->execute();

							$atrow = $atQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
							$atSerNum = $atrow[0];
							$atName = $atrow[1];
							$atPrivate = $atrow[2];
							$atCat = $atrow[3];

							$curr_at =  new AnswerType($atSerNum, $atName, $atPrivate, $atCat);
							$curr_question = new Question($serNum, $question, $curr_at, "");
							$curr_group->addQuestion($curr_question);	
						}

						//read in all the tags for each question group
						$tagSQL = "
							SELECT
								QuestionnaireTag.serNum,
								QuestionnaireTag.name_EN
							FROM
								Questiongroup,
								Questiongroup_tag,
								QuestionnaireTag
							WHERE
								Questiongroup.serNum = Questiongroup_tag.questiongroup_serNum
							AND
								Questiongroup_tag.tag_serNum = QuestionnaireTag.serNum
							AND
								Questiongroup.serNum = $groupSerNum
						";
						$tagQ = $host_db_link->prepare($tagSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
						$tagQ->execute();

						while($tagrow = $tagQ->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
							$tagSerNum = $tagrow[0];
							$tagName = $tagrow[1];

							$tag = new Tag($tagSerNum, $tagName);
					        $curr_group->addTag($tag);

					        //add tag to category and library
					        $curr_cat->addTag($tag);
					        $curr_library->addTag($tag);
						}
					}
				}
			}
			return $groupings;
		} catch (PDOException $e) {
	 		echo $e->getMessage();
	 		return $groupings;
	 	}
	}
}
?>