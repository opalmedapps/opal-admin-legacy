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
}
?>