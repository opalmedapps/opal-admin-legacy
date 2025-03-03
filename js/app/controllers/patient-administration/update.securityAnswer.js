// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.update.securityAnswer',
	['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller(
		'update.securityAnswer', function ($scope, $filter, $uibModal, $uibModalInstance, patientAdministrationCollectionService, $state, Session, ErrorHandler
	) {

	$scope.patientSecurityQuestions = {}
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	//Initialize the params field
	getPatientSecurityQuestions();

	//Function to change the security questions
	$scope.changeFirstQuestion = function() {

		if ($scope.validateInput($scope.patientSecurityQuestions.firstAnswer)) {
			if($scope.patientSecurityQuestions.firstAnswer.length < 3) {
				$scope.patientSecurityQuestions.firstErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_TOO_SHORT');
			}
			else if ($scope.patientSecurityQuestions.firstAnswer === $scope.patientSecurityQuestions.secondAnswer
                ||$scope.patientSecurityQuestions.firstAnswer === $scope.patientSecurityQuestions.thirdAnswer) {
				$scope.patientSecurityQuestions.firstErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_DUPLICATE');
			}
			else {
				$scope.patientSecurityQuestions.firstErrorMessage = null;
			}
			$scope.patientSecurityQuestions.changeDetected = true;
		}
		else if ($scope.validateQuestion($scope.patientSecurityQuestions.firstQuestion)) {
			$scope.patientSecurityQuestions.firstErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_MISSING');
			$scope.patientSecurityQuestions.changeDetected = true;
		}
		else if (!$scope.validateQuestion($scope.patientSecurityQuestions.secondQuestion) && !$scope.validateQuestion($scope.patientSecurityQuestions.thirdQuestion)
            && !$scope.validateInput($scope.patientSecurityQuestions.secondAnswer) && !$scope.validateInput($scope.patientSecurityQuestions.thirdAnswer)) {
			$scope.patientSecurityQuestions.firstErrorMessage = null;
			$scope.patientSecurityQuestions.changeDetected = false;
		}
		else $scope.patientSecurityQuestions.firstErrorMessage = null;
	};

	$scope.changeSecondQuestion = function() {

		if ($scope.validateInput($scope.patientSecurityQuestions.secondAnswer)) {
			if($scope.patientSecurityQuestions.secondAnswer.length < 3) {
				$scope.patientSecurityQuestions.secondErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_TOO_SHORT');
			}
			else if ($scope.patientSecurityQuestions.secondAnswer === $scope.patientSecurityQuestions.firstAnswer
                ||$scope.patientSecurityQuestions.secondAnswer === $scope.patientSecurityQuestions.thirdAnswer) {
				$scope.patientSecurityQuestions.secondErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_DUPLICATE');
			}
			else {
				$scope.patientSecurityQuestions.secondErrorMessage = null;
			}
			$scope.patientSecurityQuestions.changeDetected = true;
		}
		else if ($scope.validateQuestion($scope.patientSecurityQuestions.secondQuestion)) {
			$scope.patientSecurityQuestions.secondErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_MISSING');
			$scope.patientSecurityQuestions.changeDetected = true;
		}
		else if (!$scope.validateQuestion($scope.patientSecurityQuestions.firstQuestion) && !$scope.validateQuestion($scope.patientSecurityQuestions.thirdQuestion)
            && !$scope.validateInput($scope.patientSecurityQuestions.firstAnswer) && !$scope.validateInput($scope.patientSecurityQuestions.thirdAnswer)) {
			$scope.patientSecurityQuestions.secondErrorMessage = null;
			$scope.patientSecurityQuestions.changeDetected = false;
		}
		else $scope.patientSecurityQuestions.secondErrorMessage = null;
	};

	$scope.changeThirdQuestion = function() {

		if ($scope.validateInput($scope.patientSecurityQuestions.thirdAnswer)) {
			if($scope.patientSecurityQuestions.thirdAnswer.length < 3) {
				$scope.patientSecurityQuestions.thirdErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_TOO_SHORT');
			}
			else if ($scope.patientSecurityQuestions.thirdAnswer === $scope.patientSecurityQuestions.firstAnswer
                ||$scope.patientSecurityQuestions.thirdAnswer === $scope.patientSecurityQuestions.secondAnswer) {
				$scope.patientSecurityQuestions.thirdErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_DUPLICATE');
			}
			else {
				$scope.patientSecurityQuestions.thirdErrorMessage = null;
			}
			$scope.patientSecurityQuestions.changeDetected = true;
		}
		else if ($scope.validateQuestion($scope.patientSecurityQuestions.thirdQuestion)) {
			$scope.patientSecurityQuestions.thirdErrorMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ANSWER_MISSING');
			$scope.patientSecurityQuestions.changeDetected = true;
		}
		else if (!$scope.validateQuestion($scope.patientSecurityQuestions.firstQuestion) && !$scope.validateQuestion($scope.patientSecurityQuestions.secondQuestion)
            && !$scope.validateInput($scope.patientSecurityQuestions.firstAnswer) && !$scope.validateInput($scope.patientSecurityQuestions.secondAnswer)) {
			$scope.patientSecurityQuestions.thirdErrorMessage = null;
			$scope.patientSecurityQuestions.changeDetected = false;
		}
		else $scope.patientSecurityQuestions.thirdErrorMessage = null;
	};

	//Function to filter the chosen questions out to avoid choosing same question
	$scope.firstFilter = function() {
		return function (question) {
			return question.question_title !== $scope.patientSecurityQuestions.secondQuestion && question.question_title !== $scope.patientSecurityQuestions.thirdQuestion;
		};
	};

	$scope.secondFilter = function() {
		return function (question) {
			return question.question_title !== $scope.patientSecurityQuestions.firstQuestion && question.question_title !== $scope.patientSecurityQuestions.thirdQuestion;
		};
	};

	$scope.thirdFilter = function() {
		return function (question) {
			return question.question_title !== $scope.patientSecurityQuestions.firstQuestion && question.question_title !== $scope.patientSecurityQuestions.secondQuestion;
		};
	};

	//Function to validate the given question
	$scope.validateQuestion = function(question) {
		return ($scope.validateInput(question) && question !== $scope.patientSecurityQuestions.firstQuestion_old
            && question !== $scope.patientSecurityQuestions.secondQuestion_old && question !== $scope.patientSecurityQuestions.thirdQuestion_old);
	};

	//Function to check all error message
	$scope.checkErrorMessage = function () {
		return $scope.validateInput($scope.patientSecurityQuestions.firstErrorMessage) || $scope.validateInput($scope.patientSecurityQuestions.secondErrorMessage) || $scope.validateInput($scope.patientSecurityQuestions.thirdErrorMessage);
	};

	//function to validate input is not empty
	$scope.validateInput = function(input) {
		return (input !== null && input !== undefined && input !== '');
	};

	//Initialize the error messages
	var arrValidationUpdate = [
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.QUESTION'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.ANSWER'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.PATIENTSERNUM'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.QUESTION_OLD'),
	];

	//Function to update the patient security questions
	$scope.updateSecurityQuestions = function () {
		//Validate first question and choose the correct spot
		if ($scope.validateInput($scope.patientSecurityQuestions.firstAnswer)) {
			updateQuestion($scope.patientSecurityQuestions.firstQuestion, $scope.patientSecurityQuestions.firstAnswer, $scope.patientSecurityQuestions.firstAnswerId);
		}

		//Validate secoond question and choose the correct spot
		if ($scope.validateInput($scope.patientSecurityQuestions.secondAnswer)) {
			updateQuestion($scope.patientSecurityQuestions.secondQuestion, $scope.patientSecurityQuestions.secondAnswer, $scope.patientSecurityQuestions.secondAnswerId);
		}

		//Validate third question and choose the correct spot
		if ($scope.validateInput($scope.patientSecurityQuestions.thirdAnswer)) {
			updateQuestion($scope.patientSecurityQuestions.thirdQuestion, $scope.patientSecurityQuestions.thirdAnswer, $scope.patientSecurityQuestions.thirdAnswerId);
		}
	};

	//Function to update a security question and answer
	function updateQuestion(question, answer, answer_id) {
		$.ajax({
			type: "POST",
			url: "patient-administration/update/security-answer",
			data: {
				username: $scope.puid,
				language: $scope.plang,
				question: question,
				answer: CryptoJS.SHA512(answer.toUpperCase()).toString(),
				answer_id: answer_id,
			},
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage =  $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.SUCCESS');
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ERROR'), arrValidationUpdate);
				$scope.setBannerClass('danger');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.SECURITY_ANSWER.ERROR');
			},
			complete: function () {
				$scope.showBanner();
				$uibModalInstance.close();
			}
		});
	}

	function getPatientSecurityQuestions () {
		patientAdministrationCollectionService.getPatientSecurityQuestions($scope.puid, $scope.plang).then(function (response){
			const results = response.data;

			$scope.patientSecurityQuestions = {
				firstQuestion_old: results[0].question,
				firstQuestion: results[0].question,
				firstAnswerId: results[0].id,
				firstAnswer: null,
				firstErrorMessage: null,
				secondQuestion_old: results[1].question,
				secondQuestion: results[1].question,
				secondAnswerId: results[1].id,
				secondAnswer: null,
				secondErrorMessage: null,
				thirdQuestion_old: results[2].question,
				thirdQuestion: results[2].question,
				thirdAnswerId: results[2].id,
				thirdAnswer: null,
				thirdErrorMessage: null,
				changeDetected: false,
			};

			getAllSecurityQuestions();
		});
	}

	function getAllSecurityQuestions () {
		patientAdministrationCollectionService.getAllSecurityQuestions($scope.plang).then(function (response) {
			const results = response.data;
			$scope.questionList = [];
			results.forEach(function (row) {
				const question = {
					question_id: row.id,
					question_title: ($scope.plang === "FR" ? row.title_fr : row.title_en),
				};
				$scope.questionList.push(question);
			});
		});
	}

});