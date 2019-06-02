angular.module('opalAdmin.controllers.question.type.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).
controller('question.type.add', function ($scope, $state, $filter, $uibModal, Session, filterCollectionService, questionnaireCollectionService) {
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
	$scope.newQuestionType = {
		text_EN: "",
		text_FR: "",
		library_ID: null,
		libraries: [],
		typeId: null,
		private: null,
		OAUserId: OAUserId
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



		if (!$scope.newQuestionType.name_EN && !$scope.newQuestionType.name_FR) {
			$scope.titleSection.open = false;
		}
		if ($scope.newQuestionType.name_EN && $scope.newQuestionType.name_FR) {

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
		if ($scope.newQuestionType.typeId) {
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

		var idx = $scope.newQuestionType.libraries.indexOf(selectedLibrary.serNum);

		if (idx > -1) {
			$scope.newQuestionType.libraries.splice(idx, 1);
		}

		else {
			$scope.newQuestionType.libraries.push(selectedLibrary.serNum);
		}

		if ($scope.newQuestionType.libraries.length > 0) {

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
		$scope.newQuestionType.typeId = false;
	};

	$scope.groupCancelSelection = function () {
		$scope.newQuestionType.library_ID = false;
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
	questionnaireCollectionService.getQuestionTypes(OAUserId).then(function (response) {
		$scope.atFilterList = response.data;
	}).catch(function(response) {
		alert('Error occurred getting response types: '+response.status +"\r\n"+ response.data);
	});

	questionnaireCollectionService.getLibraries(OAUserId).then(function (response) {
		$scope.groupFilterList = response.data;
	}).catch(function(response) {
		alert('Error occurred getting question libraries: '+response.status +"\r\n"+ response.data);
	});

	questionnaireCollectionService.getQuestionTypeList(OAUserId).then(function (response) {
		$scope.atCatList = response.data;
	}).catch(function(response) {
		alert('Error occurred getting response type categories: '+response.status +"\r\n"+ response.data);
	});

	// add new types & write into DB
	// Initialize the new answer type object
	$scope.newQuestionType = {
		name_EN: "",
		name_FR: "",
		category_EN: "",
		category_FR: "",
		private: 0,
		OAUserId: OAUserId,
		options: [],
		slider: {
			minValue: 1,
			minCaption_EN: "Minimum english text",
			minCaption_FR: "Minimum texte français",
			maxValue: 10,
			maxCaption_EN: "Maximum english text",
			maxCaption_FR: "Maximum texte français",
			increment: 1,
		},
	};

	$scope.addnewQuestionType = function (atCatSelected) {
		// Binding categories
		$scope.newQuestionType.ID = atCatSelected.ID;
		$scope.newQuestionType.OAUserId = Session.retrieveObject('user').id;
		// Prompt to confirm user's action
		var confirmation = confirm("Are you sure you want to create a new " + atCatSelected.category_EN.toLowerCase()  +  " response type named '" + $scope.newQuestionType.name_EN + "'?");
		if (confirmation) {
			// write in to db
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.question_type.php",
				data: $scope.newQuestionType,
				success: function (result) {
					result = JSON.parse(result);
					if(result.message === 200) {

						alert('Successfully added the new response type. Please find your new response type in the form above.');
						// update answer type list
						questionnaireCollectionService.getQuestionTypes(OAUserId).then(function (response) {
							$scope.atFilterList = response.data;
						}).catch(function (response) {
							alert('Error occurred getting response types: '+response.status +"\r\n"+ response.data);
						});
					} else {
						alert("Unable to create the response type. Code " + result.message + ".\r\nError message: " + result.details);
					}

				},
				error: function (request, status, err) {
					alert("A problem occurred. Please try again.\r\n"+request.responseText);
				}
			});
		}
	};

	// add options
	$scope.addOptions = function () {
		$scope.newQuestionType.options.push({
			text_EN: "",
			text_FR: "",
			order: $scope.newQuestionType.options.length + 1,
			OAUserId: OAUserId
		});
	};

	// delete options
	$scope.deleteOptions = function (optionToDelete) {
		var index = $scope.newQuestionType.options.indexOf(optionToDelete);
		if (index > -1) {
			$scope.newQuestionType.options.splice(index, 1);
		}

		var i = 1;
		$scope.newQuestionType.options.forEach(function(entry) {
			entry.order = i;
			i++;
		});
	};

	$scope.orderPreview = function () {
		$scope.newQuestionType.options.sort(function(a,b){
			return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
		});
	};

	$scope.updateSlider = function () {
		var radiostep = new Array();
		var increment = parseFloat($scope.newQuestionType.slider.increment);
		var minValue = parseFloat($scope.newQuestionType.slider.minValue);
		var maxValue = parseFloat($scope.newQuestionType.slider.maxValue);

		if (minValue <= 0.0 || maxValue <= 0.0 || increment <= 0 || minValue >= maxValue)
			$scope.validSlider = false;
		else {
			maxValue = (Math.floor((maxValue - minValue) / increment) * increment) + minValue;
			$scope.newQuestionType.slider.maxValue = parseInt(maxValue);
			$scope.validSlider = true;
			for(var i = minValue; i <= maxValue; i += increment) {
				radiostep.push({"name_EN":" " + i,"name_FR":" " + i});
			}
			radiostep[0]["name_EN"] += " " + $scope.newQuestionType.slider.minCaption_EN;
			radiostep[0]["name_FR"] += " " + $scope.newQuestionType.slider.minCaption_FR;
			radiostep[radiostep.length - 1]["name_EN"] += " " + $scope.newQuestionType.slider.maxCaption_EN;
			radiostep[radiostep.length - 1]["name_FR"] += " " + $scope.newQuestionType.slider.maxCaption_FR;
		}
		$scope.newQuestionType.options = radiostep;
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
				url: "php/questionnaire/insert.question.php",
				data: $scope.newQuestionType,
				success: function (result) {
					result = JSON.parse(result);
					if (result.code === 200) {
						$state.go('questionnaire-question');
					} else {
						alert("Unable to create the question. Code " + result.code + ".\r\nError message: " + result.message);
					}
				},
				error: function () {
					alert("Something went wrong.");
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
