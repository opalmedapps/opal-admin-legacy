angular.module('opalAdmin.controllers.question.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).
controller('question.add', function ($scope, $state, $filter, $uibModal, Session, questionnaireCollectionService, ErrorHandler) {
	// navigation function
	$scope.goBack = function () {
		$state.go('questionnaire');
	};

	$scope.goBack = function () {
		window.history.back();
	};

	// Default booleans
	$scope.titleSection = { open: false, show: true };
	$scope.answerTypeSection = { open: false, show: false };
	$scope.questionLibrarySection = { open: false, show: false };

	$scope.list = ["one", "two", "three", "four", "five", "six"];
	$scope.language = Session.retrieveObject('user').language;
	$scope.responseType = [];

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	// step bar
	var steps = {
		question: { completed: false },
		answerType: { completed: false },
		questionGroup: { completed: false }
	};

	$scope.numOfCompletedSteps = 0;
	$scope.stepTotal = 2;
	$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	$scope.decreaseStep = function() {
		$scope.newQuestion.library_ID = null;
		$scope.stepTotal = 2;
		$scope.numOfCompletedSteps = 2;
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		steps.question.completed = true;
		$scope.questionLibrarySection.open = false;
	};

	$scope.increaseStep = function() {
		$scope.stepTotal = 3;
		steps.question.completed = false;
		$scope.numOfCompletedSteps = 2;
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		$scope.questionLibrarySection.open = true;
	};

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};

	// Function to calculate / return step progress
	function trackProgress(value, total) {
		var result = Math.round(100 * value / total);
		if (result > 100) result = 100;
		return result;
	}

	// Function to return number of steps completed
	function stepsCompleted(steps) {
		var numberOfTrues = 0;
		for (var step in steps) {
			if (steps[step].completed === true) {
				numberOfTrues++;
			}
		}
		return numberOfTrues;
	}

	// Initialize the new question object
	$scope.newQuestion = {
		display_EN: "",
		display_FR: "",
		question_EN: "",
		question_FR: "",
		library_ID: null,
		libraries: [],
		typeId: null,
		private: 0,
		OAUserId: OAUserId,
	};

	// Initialize variables for holding selected answer type & group
	$scope.selectedAt = null;
	$scope.selectedGroup = null;

	// Filter lists initialized
	$scope.atFilterList = [];
	$scope.libFilterList = [];
	// $scope.catFilterList = [];
	$scope.groupFilterList = [];
	$scope.atCatList = [];

	// Initialize search field variables
	$scope.atEntered = '';
	$scope.libEntered = '';
	$scope.catEntered = '';
	$scope.groupEntered = '';

	// Update values from form
	$scope.updateQuestionText = function () {

		$scope.titleSection.open = true;
		if (!$scope.newQuestion.question_EN && !$scope.newQuestion.question_FR && !$scope.newQuestion.display_EN && !$scope.newQuestion.display_FR) {
			$scope.titleSection.open = false;
		}
		else if ($scope.newQuestion.question_EN && $scope.newQuestion.question_FR && $scope.newQuestion.display_EN && $scope.newQuestion.display_FR) {

			$scope.answerTypeSection.show = true;

			steps.question.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		} else {
			steps.question.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	$scope.updateAt = function (selectedAt) {
		$scope.answerTypeSection.open = true;
		if ($scope.newQuestion.typeId) {
			$scope.questionLibrarySection.show = true;
			$scope.selectedAt = selectedAt;
			steps.answerType.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			if (selectedAt.typeId === "2") {
				var increment = parseFloat($scope.selectedAt.increment);
				var minValue = parseFloat($scope.selectedAt.minValue);
				if (minValue === 0.0) minValue = increment;
				var maxValue = parseFloat($scope.selectedAt.maxValue);

				$scope.radiostep = new Array();
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
				for (var i = minValue; i <= maxValue; i += increment) {
					$scope.radiostep.push({"name": i});
				}
				$scope.radiostep[0]["name"] +=  " " + $scope.selectedAt.minCaption_EN + " / " + $scope.selectedAt.minCaption_FR;
				$scope.radiostep[$scope.radiostep.length - 1]["name"] += " " + $scope.selectedAt.maxCaption_EN + " / " + $scope.selectedAt.maxCaption_FR;
			}
		}
		else {

			steps.answerType.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		}
	};

	$scope.updateOptions = function() {

		$scope.newTemplateQuestion.options = {};
		$scope.newTemplateQuestion.subOptions = [];
	};

	$scope.updateLibrary = function (selectedLibrary) {
		$scope.questionLibrarySection.open = true;

		var idx = $scope.newQuestion.libraries.indexOf(selectedLibrary.serNum);

		if (idx > -1) {
			$scope.newQuestion.libraries.splice(idx, 1);
		}

		else {
			$scope.newQuestion.libraries.push(selectedLibrary.serNum);
		}

		if ($scope.newQuestion.libraries.length > 0) {

			$scope.selectedGroup = selectedLibrary;

			steps.questionGroup.completed = true;
			$scope.numOfCompletedSteps = 3;
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		} else {

			steps.questionGroup.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		}
	};

	// assign functions
	$scope.searchAt = function (field) {
		$scope.atEntered = field;
	};
	$scope.searchLib = function (field) {
		$scope.libEntered = field;
	};

	// cancel selection
	$scope.atCancelSelection = function () {
		$scope.newQuestion.typeId = false;
	};

	$scope.groupCancelSelection = function () {
		$scope.newQuestion.library_ID = false;
	};

	// search function
	$scope.searchAtFilter = function (Filter) {
		var keyword = new RegExp($scope.atEntered, 'i');
		return !$scope.atEntered || keyword.test($scope.language.toUpperCase() === "FR"?Filter.name_FR:Filter.name_EN);
	};
	$scope.searchLibFilter = function (Filter) {
		var keyword = new RegExp($scope.libEntered, 'i');
		return !$scope.libEntered || keyword.test($scope.language.toUpperCase() === "FR"?Filter.name_FR:Filter.name_EN);
	};

	// questionnaire API: retrieve the template of questions
	getTemplatesQuestionsList();

	getLibrariesList();

	questionnaireCollectionService.getTemplateQuestionCategory().then(function (response) {
		$scope.atCatList = response.data;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.ERROR_GET_CATEGORY'));
		$state.go('questionnaire/question');
	});

	// add new types & write into DB
	// Initialize the new answer type object
	$scope.newTemplateQuestion = {
		name_EN: "",
		name_FR: "",
		category_EN: "",
		category_FR: "",
		private: 0,
		OAUserId: OAUserId,
		options: {},
		subOptions: [],
	};

	$scope.addNewTemplateQuestion = function (atCatSelected) {
		// Binding categories
		$scope.newTemplateQuestion.typeId = atCatSelected.ID;
		$scope.newTemplateQuestion.OAUserId = Session.retrieveObject('user').id;
		var toSend = $scope.newTemplateQuestion;
		// Prompt to confirm user's action
		var confirmation = confirm($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.CONFIRM_RESPONSE_TYPE') + "\r\n\r\n" + $scope.newTemplateQuestion.name_EN + " / " + $scope.newTemplateQuestion.name_FR);
		if (confirmation) {
			$.ajax({
				type: "POST",
				url: "template-question/insert/template-question",
				data: toSend,
				success: function () {
					alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.SUCCESS_RESPONSE_TYPE'));
					getTemplatesQuestionsList();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.ERROR_SET_RESPONSE_TYPE'));
					$state.go('questionnaire/question');
				}
			});
		}
	};

	function getTemplatesQuestionsList() {
		questionnaireCollectionService.getTemplatesQuestions().then(function (response) {
			$scope.atFilterList = response.data;
			$scope.atFilterList.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR") {
					entry.name_display = entry.name_FR;
					entry.category_display = entry.category_FR;
				} else {
					entry.name_display = entry.name_EN;
					entry.category_display = entry.category_EN;
				}
			});
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.ERROR_SET_RESPONSE_TYPE'));
			$state.go('questionnaire/question');
		});
	};

	// add options
	$scope.addOptions = function () {
		$scope.newTemplateQuestion.subOptions.push({
			description_EN: "",
			description_FR: "",
			order: $scope.newTemplateQuestion.subOptions.length + 1,
			OAUserId: OAUserId
		});
	};

	// delete options
	$scope.deleteOptions = function (optionToDelete) {
		var index = $scope.newTemplateQuestion.subOptions.indexOf(optionToDelete);
		if (index > -1) {
			$scope.newTemplateQuestion.subOptions.splice(index, 1);
		}
	};

	// Initialize the new library object
	$scope.newLibrary = {
		name_EN: "",
		name_FR: "",
		private: 0,
		OAUserId: OAUserId
	};
	$scope.addNewLib = function () {
		// Prompt to confirm user's action
		var confirmation = confirm($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.CONFIRM_LIBRARY') + "\r\n\r\n" + $scope.newLibrary.name_EN + " / "+$scope.newLibrary.name_FR);
		if (confirmation) {
			// write in to db
			$.ajax({
				type: "POST",
				url: "library/insert/library",
				data: $scope.newLibrary,
				success: function () {
					alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.SUCCESS_LIBRARY'));
					getLibrariesList();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.ERROR_SET_LIBRARY'));
					$state.go('questionnaire/question');
				}
			});
		}
	};

	function getLibrariesList() {
		questionnaireCollectionService.getLibraries().then(function (response) {
			$scope.libraries = [];
			$scope.groupFilterList = response.data;
			$scope.groupFilterList.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name_EN;
			});
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.ERROR_GET_LIBRARY'));
			$state.go('questionnaire/question');
		});
	}

	// check if form is completed
	$scope.checkForm = function () {
		if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) >= 100)
			return true;
		else
			return false;
	};

	// submit question: write into DB
	$scope.submitQuestion = function () {
		if ($scope.checkForm()) {
			// Submit
			$.ajax({
				type: "POST",
				url: "question/insert/question",
				data: $scope.newQuestion,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_ADD.ERROR_SET_QUESTION'));
				},
				complete: function() {
					$state.go('questionnaire/question');
				}
			});
		}
	};

	var fixmeTop = $('.summary-fix').offset().top;
	$(window).scroll(function () {
		var currentScroll = $(window).scrollTop();
		if (currentScroll >= fixmeTop) {
			$('.summary-fix').css({
				position: 'fixed',
				top: '0',
				width: '15%'
			});
		} else {
			$('.summary-fix').css({
				position: 'static',
				width: ''
			});
		}
	});

	var fixMeMobile = $('.mobile-side-panel-menu').offset().top;
	$(window).scroll(function() {
		var currentScroll = $(window).scrollTop();
		if (currentScroll >= fixMeMobile) {
			$('.mobile-side-panel-menu').css({
				position: 'fixed',
				top: '50px',
				width: '100%',
				zIndex: '100',
				background: '#6f5499',
				boxShadow: 'rgba(93, 93, 93, 0.6) 0px 3px 8px -3px'

			});
			$('.mobile-summary .summary-title').css({
				color: 'white'
			});
		} else {
			$('.mobile-side-panel-menu').css({
				position: 'static',
				width: '',
				background: '',
				boxShadow: ''
			});
			$('.mobile-summary .summary-title').css({
				color: '#6f5499'
			});
		}
	});
});
