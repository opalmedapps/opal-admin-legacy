angular.module('opalAdmin.controllers.question.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).
controller('question.add', function ($scope, $state, $filter, $uibModal, Session, filterCollectionService, questionnaireCollectionService) {
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

	// get current user id
	var user = Session.retrieveObject('user');
	var userid = user.id;

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
		text_EN: "",
		text_FR: "",
		library_ID: null,
		questiontype_ID: null,
		userid: userid
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
		if (!$scope.newQuestion.text_EN && !$scope.newQuestion.text_FR) {
			$scope.titleSection.open = false;
		}
		if ($scope.newQuestion.text_EN && $scope.newQuestion.text_FR) {

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
		if ($scope.newQuestion.questiontype_ID) {
			$scope.questionLibrarySection.show = true;
			$scope.selectedAt = selectedAt;
			steps.answerType.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);

			var increment = parseFloat($scope.selectedAt.increment);
			var minValue = parseFloat($scope.selectedAt.minValue);
			if (minValue === 0.0) minValue = increment;
			var maxValue = parseFloat($scope.selectedAt.maxValue);

			$scope.radiostep = new Array();
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			for(var i = minValue; i <= maxValue; i += increment) {
				$scope.radiostep.push({"name":""});
			}
			$scope.radiostep[0]["name"] = $scope.selectedAt.minCaption_EN + " / " + $scope.selectedAt.minCaption_FR;
			$scope.radiostep[$scope.radiostep.length - 1]["name"] = $scope.selectedAt.maxCaption_EN + " / " + $scope.selectedAt.maxCaption_FR;
		} else {

			steps.answerType.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		}
	};

	$scope.updateLibrary = function (selectedLibrary) {
		$scope.questionLibrarySection.open = true;
		if ($scope.newQuestion.library_ID) {

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
		$scope.newQuestion.questiontype_ID = false;
	};

	$scope.groupCancelSelection = function () {
		$scope.newQuestion.library_ID = false;
	};

	// search function
	$scope.searchAtFilter = function (Filter) {
		var keyword = new RegExp($scope.atEntered, 'i');
		return !$scope.atEntered || keyword.test(Filter.name_EN);
	};
	$scope.searchLibFilter = function (Filter) {
		var keyword = new RegExp($scope.libEntered, 'i');
		return !$scope.libEntered || keyword.test(Filter.name_EN);
	};

	// questionnaire API: retrieve data
	questionnaireCollectionService.getQuestionTypes(userid).then(function (response) {
		$scope.atFilterList = response.data;
	}).catch(function(response) {
		console.error('Error occurred getting response types:', response.status, response.data);
	});

	// library with sub-array: categories
	questionnaireCollectionService.getLibraries(userid).then(function (response) {
		$scope.libFilterList = response.data;
	}).catch(function(response) {
		console.error('Error occurred getting libraries:', response.status, response.data);
	});

	//questionnaireCollectionService.getQuestionGroups(userid).then(function (response) {
	questionnaireCollectionService.getLibraries(userid).then(function (response) {
		$scope.groupFilterList = response.data;
	}).catch(function(response) {
		console.error('Error occurred getting question libraries:', response.status, response.data);
	});

	questionnaireCollectionService.getAnswerTypeCategories().then(function (response) {
		$scope.atCatList = response.data;
	}).catch(function(response) {
		console.error('Error occurred getting response type categories:', response.status, response.data);
	});

	// add new types & write into DB
	// Initialize the new answer type object
	$scope.newAnswerType = {
		name_EN: "",
		name_FR: "",
		category_EN: "",
		category_FR: "",
		private: 0,
		userid: userid,
		options: [],
		slider: []
	};

	$scope.addNewAt = function (atCatSelected) {
		// Binding categories
		$scope.newAnswerType.ID = atCatSelected.ID;
		$scope.newAnswerType.userId = Session.retrieveObject('user').id;
		// Prompt to confirm user's action
		var confirmation = confirm("Are you sure you want to create a new " + atCatSelected.category_EN.toLowerCase()  +  " response type named '" + $scope.newAnswerType.name_EN + "'?");
		if (confirmation) {
			// write in to db
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.question_type.php",
				data: $scope.newAnswerType,
				success: function (result) {
					result = JSON.parse(result);
					alert('Successfully added the new response type. Please find your new response type in the radio button form above.');
					// update answer type list
					questionnaireCollectionService.getQuestionTypes(userid).then(function (response) {
						$scope.atFilterList = response.data;
					}).catch(function(response) {
						console.error('Error occurred getting response types:', response.status, response.data);
					});
				},
				error: function () {
					alert("A problem occurred. Please try again.");
				}
			});
		}
	};

	// add options
	$scope.addOptions = function () {
		$scope.newAnswerType.options.push({
			text_EN: "",
			text_FR: "",
			position: undefined,
			userid: userid
		});
	};

	// delete options
	$scope.deleteOptions = function (optionToDelete) {
		var index = $scope.newAnswerType.options.indexOf(optionToDelete);
		if (index > -1) {
			$scope.newAnswerType.options.splice(index, 1);
		}
	};

	// Initialize the new library object
	$scope.newLibrary = {
		name_EN: "",
		name_FR: "",
		private: 0,
		userid: userid
	};
	$scope.addNewLib = function () {
		// Prompt to confirm user's action
		var confirmation = confirm("Are you sure you want to create new library " + $scope.newLibrary.name_EN + " / "+$scope.newLibrary.name_FR+ "?");
		if (confirmation) {
			// write in to db
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.library.php",
				data: $scope.newLibrary,
				success: function (result) {
					result = JSON.parse(result);
					if(result.message === 200) {
						alert('Successfully added the new library. Please find your new library in the panel above.');
						// update
						questionnaireCollectionService.getLibraries(userid).then(function (response) {
							$scope.groupFilterList = response.data;
						}).catch(function (response) {
							alert('Error occurred getting libraries. Code '+ response.status +"\r\n" + response.data);
						});
					}
					else {
						alert("Unable to create the library. Code " + result.message + ".\r\nErrore message: " + result.details);
					}
				},
				error: function () {
					alert("Something went wrong.");
				}
			});
		}
	};

	// check if form is completed
	$scope.checkForm = function () {
		if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) >= 100)
			return true;
		else
			return false;
	};

	// submit question: write into DB
	$scope.submitQuestion = function () {
		console.log($scope.newQuestion);
		/*if ($scope.checkForm()) {
			// Submit
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.question.php",
				data: $scope.newQuestion,
				success: function () {
					$state.go('questionnaire-question');
				}
			});
		}*/
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
