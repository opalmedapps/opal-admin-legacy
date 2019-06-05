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

	// Initialize the new answer type object
	$scope.newQuestionType = {
		ID: "",
		name_EN: "",
		name_FR: "",
		category_EN: "",
		category_FR: "",
		private: 0,
		OAUserId: OAUserId,
		options: [],
		minValue: 1,
		minCaption_EN: undefined,
		minCaption_FR: undefined,
		maxValue: 10,
		maxCaption_EN: undefined,
		maxCaption_FR: undefined,
		increment: 1,
		slider: {
			options: [],
		},
	};

	// Initialize variables for holding selected answer type & group
	$scope.selectedAt = null;

	// Filter lists initialized
	$scope.atCatList = [];

	// Initialize search field variables
	// $scope.atEntered = '';
	// $scope.libEntered = '';
	// $scope.catEntered = '';
	// $scope.groupEntered = '';
	// $scope.atCategory = "";

	$scope.updateQuestionType = function (selected) {
		$scope.newQuestionType.ID = selected.ID;

		$scope.newQuestionType.minValue = 1;
		$scope.newQuestionType.minCaption_EN = undefined;
		$scope.newQuestionType.minCaption_FR = undefined;
		$scope.newQuestionType.maxValue = 10;
		$scope.newQuestionType.maxCaption_EN = undefined;
		$scope.newQuestionType.maxCaption_FR = undefined;
		$scope.newQuestionType.increment = 1;
		$scope.newQuestionType.slider = {
			options: [],
		};
		$scope.newQuestionType.options = [];

		if (selected.ID === "2")
			$scope.updateSlider();

	};

	$scope.submitQuestionType = function () {
		if ($scope.checkForm()) {
			// Submit
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.question_type.php",
				data: $scope.newQuestionType,
				success: function (result) {
					result = JSON.parse(result);
					if (result.message === 200) {
						$state.go('questionnaire-question-type');
					} else {
						alert("Unable to create the question type. Code " + result.code + ".\r\nError message: " + result.message);
					}
				},
				error: function () {
					alert("Something went wrong.");
				}
			});
		}


	};

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

	// questionnaire API: retrieve data
	questionnaireCollectionService.getQuestionTypeCategory(OAUserId).then(function (response) {
		$scope.atCatList = response.data;
	}).catch(function(response) {
		alert('Error occurred getting response type categories: '+response.status +"\r\n"+ response.data);
	});

	// add options
	$scope.addOptions = function () {
		$scope.newQuestionType.options.push({
			text_EN: "",
			text_FR: "",
			order: $scope.newQuestionType.options.length + 1,
			OAUserId: OAUserId
		});
		$scope.checkCompletion();
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

	$scope.updateSelection = function (){
		$scope.newQuestionType.options = [];
		$scope.updateSlider();
	};

	$scope.updateSlider = function () {
		var radiostep = new Array();
		var increment = parseFloat($scope.newQuestionType.increment);
		var minValue = parseFloat($scope.newQuestionType.minValue);
		var maxValue = parseFloat($scope.newQuestionType.maxValue);

		if (minValue <= 0.0 || maxValue <= 0.0 || increment <= 0 || minValue >= maxValue || $scope.newQuestionType.minCaption_EN === undefined || $scope.newQuestionType.minCaption_FR === undefined || $scope.newQuestionType.maxCaption_EN === undefined || $scope.newQuestionType.maxCaption_FR === undefined)
			$scope.validSlider = false;
		else {
			//maxValue = (Math.floor((maxValue - minValue) / increment) * increment) + minValue;
			$scope.newQuestionType.maxValue = parseInt(maxValue);
			$scope.validSlider = true;
			for(var i = minValue; i <= maxValue; i += increment) {
				radiostep.push({"text_EN":" " + i,"text_FR":" " + i});
			}
			radiostep[0]["text_EN"] += " " + $scope.newQuestionType.minCaption_EN;
			radiostep[0]["text_FR"] += " " + $scope.newQuestionType.minCaption_FR;
			radiostep[radiostep.length - 1]["text_EN"] += " " + $scope.newQuestionType.maxCaption_EN;
			radiostep[radiostep.length - 1]["text_FR"] += " " + $scope.newQuestionType.maxCaption_FR;
		}
		$scope.newQuestionType.options = radiostep;
	};

	$scope.checkCompletion = function () {
		if($scope.newQuestionType.ID === "4" || $scope.newQuestionType.ID === "1") {
			var allGood = true;

			if (typeof $scope.newQuestionType.options === 'undefined' || $scope.newQuestionType.options.length <= 0)
				allGood = false;
			else
				$scope.newQuestionType.options.forEach(function(entry) {
					if (entry.text_EN === undefined || entry.text_FR === undefined  || entry.text_EN === "" || entry.text_FR === ""  || entry.order === 0 || entry.order === undefined)
						allGood = false;
				});
			if(allGood)
				$scope.numOfCompletedSteps = 2;
			else
				$scope.numOfCompletedSteps = 1;
		}
		else if($scope.newQuestionType.ID === "2") {
			if($scope.newQuestionType.slider) {
				if($scope.validSlider)
					$scope.numOfCompletedSteps = 2;
				else
					$scope.numOfCompletedSteps = 1;
			}
		}
		else
			$scope.numOfCompletedSteps = 2;
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// check if form is completed
	$scope.checkForm = function () {
		if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) >= 100)
			return true;
		else
			return false;
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
