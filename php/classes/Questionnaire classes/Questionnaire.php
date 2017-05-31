<?php

/* Questionnaire class 
 * Dependencies: Questionanire-Tag
 */
include_once('Tag.php');

class Questionnaire{
	public $serNum;
	public $name;
	public $private;
	public $publish;
	public $created_by;
	public $tags;

	public function __construct($serNum, $name, $private, $publish, $created_by){
		$this->serNum = $serNum;
		$this->name = $name;
		$this->private = $private;
		$this->publish = $publish;
		$this->created_by = $created_by;
		$this->tags = array();
	}

	public function addTag($tag){
		array_push($this->tags,$tag);
	}

	/* Get Questionnaire
	 * @param: $post(doctor id)
	 * @return questionnaires as array
	 */
	public function getQuestionnaire($post){
		$doctor_id = $post['doctorSerNum'];
		$questionnaires = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT
					serNum,
					name_EN,
					private,
					publish,
					created_by
				FROM
					Questionnaire
				WHERE
					private = 0
				OR
					created_by = $doctor_id
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			// fetch
			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

	 			$serNum 	= $data[0];
	 			$name_EN   	= $data[1];
	 			$private 	= $data[2];
	 			$publish 	= $data[3];
	 			$created_by = $data[4];

	 			$questionnaireObj = new Questionnaire($serNum, $name_EN, $private, $publish, $created_by);
	 			array_push($questionnaires, $questionnaireObj);

	 			// get tags
				$tagsql = "
					SELECT
						tag_serNum,
						name_EN
					FROM
						Questionnaire_tag,
						QuestionnaireTag
					WHERE
						Questionnaire_tag.tag_serNum = QuestionnaireTag.serNum
					AND
						Questionnaire_tag.questionnaire_serNum = $serNum
				";

				$tagquery = $host_db_link->prepare($tagsql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$tagquery->execute();

				while ($tagdata = $tagquery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
					$tag_serNum = $tagdata[0];
					$tagName_EN = $tagdata[1];

					$tagObj = new Tag($tag_serNum, $tagName_EN);
					$questionnaireObj->addTag($tagObj);
				}
	 		} catch (PDOException $e) {
	 			echo $e->getMessage();
	 			return $questionnaires;
	 		}
		}
	}

	/* Create Questionnaire
	 * @param: $post(name_EN/FR, private, publish, created_by(doctor id), tags, questiongroups)
	 * @return null
	 */

	public function createQuestionnaire($post){
		// properties
		$name_EN = $post['name_EN'];
		$name_FR = $post['name_FR'];
		$private = $post['private'];
		$publish = $post['publish'];
		$created_by = $post['created_by'];
		$tags = $post['tags'];
		$questiongroups = $post['questionGroups']

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				INSERT INTO
					Questionnaire(
						name_EN,
						name_FR,
						private,
						publish,
						created_by
					)
				VALUES(
					\"$name_EN\",
					\"$name_FR\",
					$private,
					$publish,
					$created_by
				)
			";

			$query = $host_db_link->prepare( $sql );
			$query->execute();

			$questionnaire_id =  $host_db_link->lastInsertId();
			//position counter
			$i = 0;
			foreach($questiongroups as $group){
				$group_id = $group->serNum;
				$optional = $group->optional;

				$sql = "
					INSERT INTO
						Questionnaire_questiongroup(
							questionnaire_serNum,
							questiongroup_serNum,
							position
						)
					VALUES(
						$questionnaire_id,
						$group_id,
						$i
					)
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();
				i++;
			}

			//add tag
			foreach($tags as $tag){
				$sql = "
					INSERT INTO
						Questionnaire_tag(
							Questionnaire_questiongroup(
								questionnaire_serNum,
								tah_serNum,
								created_by
							)
					VALUES(
					$questionnaire_id,
					$tag,
					$created_by
					)
				";
				$query = $host_db_link->prepare( $sql );
				$query->execute();
			}

		} catch( PDOException $e) {
			return $e->getMessage();
		}
	}

	/* Delete Questionnaire
	 * @param: $post(questionnaire serial number)
	 * @return null
	 */
	public function deleteQuestionnaire($post){
		$questionnaire_serNum = $post['questiongroup_serNum'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			//delete from questionnaire_questiongroup
			$sql1 = "
				DELETE FROM
					Questionnaire_questiongroup
				WHERE
					questionnaire_serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql1 );
			$query->execute();
			//delete from questionnaire_user
			$sql2 = "
				DELETE FROM
					Questionnaire_user
				WHERE
					questionnaire_serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql2 );
			$query->execute();
			//delete from questionnaire_tag
			$sql3 = "
				DELETE FROM
					Questionnaire_tag
				WHERE
					questionnaire_serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql3 );
			$query->execute();
			//delete from questionnaire
			$sql4 = "
				DELETE FROM
					Questionnaire
				WHERE
					serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql4 );
			$query->execute();
		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}

	/* Add question group to questionnaire
	 * @param: $post(questionnaire serial number, questiongroup serial number, created_by(doctor id))
	 * @return null
	 **********INCOMPLETE***********
	 */
	public function addGroupToQuestionnaire($post){
		$questionnaire_serNum = $post['questionnaire_serNum'];
		$questiongroup_serNum = $post['questiongroup_serNum'];
		$created_by = $post['created_by'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT MAX(
					position
				)
				FROM
					Questionnaire_questiongroup
				WHERE
					questionnaire_serNum = $questiongroup_serNum
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

			//fetch
			$data = $query->PDO

		} catch{}

	}

	/* Remove question group from questionnaire
	 * @param: $post(questionnaire serial number, questiongroup serial number, created_by(doctor id))
	 * @return null
	 */
	public function removeGroupFromQuestionnaire($post){
		$questionnaire_serNum = $post['questionnaire_serNum'];
		$questiongroup_serNum = $post['questiongroup_serNum'];
		$created_by = $post['created_by'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				DELETE FROM
					Questionnaire_questiongroup
				WHERE
					questionnaire_serNum = $questionnaire_serNum
				AND 
					questiongroup_serNum = $questiongroup_serNum
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();

		} catch(PDOException $e) {
			return $e->getMessage();
		}	

	}
	
	/* Publish Questionnaire
	 * @param: $post(questionnaire serial number)
	 * @return null
	 */
	public function publishQuestionnaire($post){
		$questionnaire_serNum = $post['questionnaire_serNum'];
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				UPDATE
					Questionnaire
				SET
					publish = 1
				WHERE
					serNum = $questionnaire_serNum
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();
		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}

	/* Get patients' completed Questionnaire
	 * @param: $post(questionnaire_patient serial number, questionnaire serial number, created_by(doctor id))
	 * @return questionGroups as array
	 */
	public function getCompletedQuestionnaire($post){
		$qpSerNum = $post['qpSerNum'];
		$qSerNum = $post['qSerNum'];
		$created_by = $post['created_by'];

		$questionGroups = array();

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT
					Questiongroup.serNum,
					Questiongroup.name_EN
				FROM
					Questionnaire,
					Questionnaire_questiongroup,
					Questiongroup
				WHERE
					Questionnaire.serNum = Questionnaire_questiongroup.questionnaire_serNum
				AND
					Questionnaire_questiongroup.questiongroup_serNum = questiongroup_serNum
				AND
					Questionnaire.serNum = $qSerNum
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
				$questiongroup_serNum = $data[0];
				$questiongroup_name = $data[1];
				$questiongroup = new Group($questiongroup_serNum, $questiongroup_name);
				array_push($questionGroups,$questiongroup) ;

				//get questions from each question group
				$qSQL = "
					SELECT
						QuestionnaireQuestion.serNum,
						QuestionnaireQuestion.text_EN,
						QuestionnaireQuestion.answertype_serNum
					FROM
						QuestionnaireQuestion
					WHERE
						QuestionnaireQuestion.questiongroup_serNum = $questiongroup_serNum
				";
				$qResult = $host_db_link->prepare($qSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$qResult->execute();

				while($row = $qResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
					$question_serNum = $row[0];
					$question_text = $row[1];
					$type_serNum = $row[2];
					
					//read in answer type
					$qtSQL = "
						SELECT
							serNum,
							name_EN,
							private,
							category_EN,
							created_by
						FROM
							QuestionnaireAnswerType
						WHERE
							serNum = $type_serNum
					";
					$qtResult = $host_db_link->prepare($qtSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$qtResult->execute();

					$qtrow = $qtResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
					$qtSerNum = $qtrow[0];
					$qtName = $qtrow[1];
					$qtPrivate = $qtrow[2];
					$qtCat = $qtrow[3];

					//get patients' answer for the question
					$pSQL = "
						SELECT
							QuestionnaireAnswerOption.text_EN
						FROM
							QuestionnaireAnswer,
							QuestionnaireAnswerOption,
							Questionnaire_patient
						WHERE
							Questionnaire_patient.serNum = $qpSerNum
						AND
							Questionnaire_patient.serNum = QuestionnaireAnswer.questionnaire_patient_serNum
						AND
							QuestionnaireAnswer.answeroption_serNum = QuestionnaireAnswerOption.serNum
						AND
							QuestionnaireAnswer.question_serNum = $question_serNum
					";
					$pResult = $host_db_link->prepare($pSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$pResult->execute();

					//store answertype
					$curr_qt;
					//if answer exists
					if($prow = $pResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
						$answer_text = $prow[0];
						if($qtCat == 'Short Answer'){
							$optionSQL = "
								SELECT
									QuestionnaireAnswerText.answer_text
								FROM
									QuestionnaireAnswerText,
									QuestionnaireAnswerOption
								WHERE
									QuestionnaireAnswerText.answer_serNum = QuestionnaireAnswerOption.serNum
								AND 
									QuestionnaireAnswerOption.answertype_serNum = $qtSerNum
							";
							$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
							$optionResult->execute();

							$optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
							$answer_text = $optionrow[0];
						}
						$curr_qt = new AnswerType($qtSerNum, $qtName, $qtPrivate, $qtCat, $answer_text);
					}
					//no answer
					else{
						$curr_qt = new AnswerType($qtSerNum, $qtName, $qtPrivate, $qtCat, null);
					}

					$question = new Question($question_serNum, $question_text, $curr_qt);
					$questionGroup->addQuestion($question);

					//read in options for each anwser type
					if($qtCat = 'Linear Scale'){
						$optionSQL = "
							SELECT
								text_EN,
								caption_EN
							FROM
								QuestionnaireAnswerOption
							WHERE
								QuestionnaireAnswerOption.answertype_serNum = $qtSerNum
						";
						$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
						$optionResult->execute();

						$min = null;
						while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
							$text_EN = $optionrow[0];
							$caption_EN = $optionrow[1];

							$curr_qt->addOption($text_EN);
							//set min if 1st caption
							if($caption_EN!=null && $min==null){
								$min = $caption_EN;
								$curr_qt->setMinCaption($caption_EN);
							}
							//check if caption is max and set
							else if($caption_EN!=null && $min!=null){
								if($min>$text_EN){
									$curr_qt->setAndSwitchMinCaption($caption_EN);
								}
								else {
									$curr_qt->setMaxCaption($caption_EN);
								}
							}
						}
					}
					else if($qtCat = 'Short Answer'){
						// no option
					}
					else {
						$optionSQL = "
							SELECT
								text_EN
							FROM
								QuestionnaireAnswerOption
							WHERE
								QuestionnaireAnswerOption.answertype_serNum = $qtSerNum
						";
						$optionResult= $host_db_link->prepare($optionSQL, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
						$optionResult->execute();

						while($optionrow = $optionResult->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)){
							$curr_qt->addOption($optionrow[0]);
						}
					}
				}
			}
			return $questionGroups;
		} catch(PDOException $e) {
	 		echo $e->getMessage();
	 		return $questionGroups;
	 	})
	}


}
?>