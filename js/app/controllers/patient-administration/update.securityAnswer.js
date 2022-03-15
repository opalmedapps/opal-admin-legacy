angular.module('opalAdmin.controllers.update.securityAnswer', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.securityAnswer', function ($scope, $filter, $uibModal, $uibModalInstance, patientAdministrationCollectionService, $state, Session, ErrorHandler) {

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
			return question.questionSerNum !== $scope.patientSecurityQuestions.secondQuestion && question.questionSerNum !== $scope.patientSecurityQuestions.thirdQuestion;
		};
	};

	$scope.secondFilter = function() {
		return function (question) {
			return question.questionSerNum !== $scope.patientSecurityQuestions.firstQuestion && question.questionSerNum !== $scope.patientSecurityQuestions.thirdQuestion;
		};
	};

	$scope.thirdFilter = function() {
		return function (question) {
			return question.questionSerNum !== $scope.patientSecurityQuestions.firstQuestion && question.questionSerNum !== $scope.patientSecurityQuestions.secondQuestion;
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
			if($scope.patientSecurityQuestions.firstQuestion_old !== $scope.patientSecurityQuestions.secondQuestion
                && $scope.patientSecurityQuestions.firstQuestion_old !== $scope.patientSecurityQuestions.thirdQuestion
                && $scope.patientSecurityQuestions.firstQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.firstQuestion, $scope.patientSecurityQuestions.firstAnswer, $scope.patientSecurityQuestions.firstQuestion_old);
				$scope.patientSecurityQuestions.firstQuestion_old = null;
			}
			else if($scope.patientSecurityQuestions.secondQuestion_old !== $scope.patientSecurityQuestions.secondQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== $scope.patientSecurityQuestions.thirdQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.firstQuestion, $scope.patientSecurityQuestions.firstAnswer, $scope.patientSecurityQuestions.secondQuestion_old);
				$scope.patientSecurityQuestions.secondQuestion_old = null;
			}
			else if($scope.patientSecurityQuestions.thirdQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.firstQuestion, $scope.patientSecurityQuestions.firstAnswer, $scope.patientSecurityQuestions.thirdQuestion_old);
				$scope.patientSecurityQuestions.thirdQuestion_old = null;
			}
		}

		//Validate secoond question and choose the correct spot
		if ($scope.validateInput($scope.patientSecurityQuestions.secondAnswer)) {
			if($scope.patientSecurityQuestions.firstQuestion_old !== $scope.patientSecurityQuestions.firstQuestion
                && $scope.patientSecurityQuestions.firstQuestion_old !== $scope.patientSecurityQuestions.thirdQuestion
                && $scope.patientSecurityQuestions.firstQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.secondQuestion, $scope.patientSecurityQuestions.secondAnswer, $scope.patientSecurityQuestions.firstQuestion_old);
				$scope.patientSecurityQuestions.firstQuestion_old = null;
			}
			else if($scope.patientSecurityQuestions.secondQuestion_old !== $scope.patientSecurityQuestions.firstQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== $scope.patientSecurityQuestions.thirdQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.secondQuestion, $scope.patientSecurityQuestions.secondAnswer, $scope.patientSecurityQuestions.secondQuestion_old);
				$scope.patientSecurityQuestions.secondQuestion_old = null;
			}
			else if($scope.patientSecurityQuestions.thirdQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.secondQuestion, $scope.patientSecurityQuestions.secondAnswer, $scope.patientSecurityQuestions.thirdQuestion_old);
				$scope.patientSecurityQuestions.thirdQuestion_old = null;
			}
		}

		//Validate third question and choose the correct spot
		if ($scope.validateInput($scope.patientSecurityQuestions.thirdAnswer)) {
			if($scope.patientSecurityQuestions.firstQuestion_old !== $scope.patientSecurityQuestions.secondQuestion
                && $scope.patientSecurityQuestions.firstQuestion_old !== $scope.patientSecurityQuestions.thirdQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.thirdQuestion, $scope.patientSecurityQuestions.thirdAnswer, $scope.patientSecurityQuestions.firstQuestion_old);
				$scope.patientSecurityQuestions.firstQuestion_old = null;
			}
			else if($scope.patientSecurityQuestions.secondQuestion_old !== $scope.patientSecurityQuestions.secondQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== $scope.patientSecurityQuestions.thirdQuestion
                && $scope.patientSecurityQuestions.secondQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.thirdQuestion, $scope.patientSecurityQuestions.thirdAnswer, $scope.patientSecurityQuestions.secondQuestion_old);
				$scope.patientSecurityQuestions.secondQuestion_old = null;
			}
			else if($scope.patientSecurityQuestions.thirdQuestion_old !== null) {
				updateQuestion($scope.patientSecurityQuestions.thirdQuestion, $scope.patientSecurityQuestions.thirdAnswer, $scope.patientSecurityQuestions.thirdQuestion_old);
				$scope.patientSecurityQuestions.thirdQuestion_old = null;
			}
		}
	};

	//Function to update a security question and answer
	function updateQuestion(question, answer, question_old) {
		$.ajax({
			type: "POST",
			url: "patient-administration/update/security-answer",
			data: {
				QuestionSerNum: question,
				Answer: CryptoJS.SHA512(answer.toUpperCase()).toString(),
				PatientSerNum: $scope.psnum,
				OldQuestionSerNum: question_old,
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
		patientAdministrationCollectionService.getPatientSecurityQuestions($scope.psnum).then(function (response){

			$scope.patientSecurityQuestions = {
				firstQuestion_old: response.data[0].SecurityQuestionSerNum,
				firstQuestion: response.data[0].SecurityQuestionSerNum,
				firstAnswer: null,
				firstErrorMessage: null,
				secondQuestion_old: response.data[1].SecurityQuestionSerNum,
				secondQuestion: response.data[1].SecurityQuestionSerNum,
				secondAnswer: null,
				secondErrorMessage: null,
				thirdQuestion_old: response.data[2].SecurityQuestionSerNum,
				thirdQuestion: response.data[2].SecurityQuestionSerNum,
				thirdAnswer: null,
				thirdErrorMessage: null,
				changeDetected: false,
			};
			getAllSecurityQuestions();
		});
	}

	function getAllSecurityQuestions () {
		patientAdministrationCollectionService.getAllSecurityQuestions().then(function (response) {
			$scope.questionList = [];
			response.data.forEach(function (row) {
				var question = {
					questionSerNum: row.SecurityQuestionSerNum,
					questionText: (Session.retrieveObject('user').language === "FR" ? row.QuestionText_FR : row.QuestionText_EN),
				};
				$scope.questionList.push(question);
			});
		});
	}

});