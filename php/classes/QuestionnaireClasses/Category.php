<?php
/* Questionnaire-Category class */

Class Category{
	public $category;
	public $groupings;
	public $tags;

	public function __construct($category){
		this->category = $category;
		this->groupings = array();
		this->tags = array();
	}

	public function addGrouping($group){
		array_push($this->groupings, $group);
	}

	//public function addTag($tag){}

}
?>