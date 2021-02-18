angular.module('opalAdmin.controllers.template.question.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).
controller('template.question.add', function ($scope, $state, $filter, $uibModal, Session, questionnaireCollectionService, ErrorHandler) {
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
	$scope.language = Session.retrieveObject('user').language;

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	// step bar
	var steps = {
		name: { completed: false },
		type: { completed: false },
	};

	$scope.numOfCompletedSteps = 0;
	$scope.preview = [];
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
	$scope.newTemplateQuestion = {
		name_EN: "",
		name_FR: "",
		private: 0,
		OAUserId: OAUserId,
		options: {},
		subOptions: [],
	};

	// Initialize variables for holding selected answer type & group
	$scope.selectedAt = [];

	// Filter lists initialized
	$scope.atCatList = [];

	$scope.updateTemplateQuestion = function (selected) {
		$scope.newTemplateQuestion.typeId = selected.ID;

		$scope.newTemplateQuestion.options = {};
		$scope.selectedAt.name_EN = selected.category_EN;
		$scope.selectedAt.name_FR = selected.category_FR;

		$scope.answerTypeSection.open = true;

		if(selected.ID === "2") {
			$scope.newTemplateQuestion.options.minValue = 1;
			$scope.newTemplateQuestion.options.minCaption_EN = undefined;
			$scope.newTemplateQuestion.options.minCaption_FR = undefined;
			$scope.newTemplateQuestion.options.maxValue = 10;
			$scope.newTemplateQuestion.options.maxCaption_EN = undefined;
			$scope.newTemplateQuestion.options.maxCaption_FR = undefined;
			$scope.newTemplateQuestion.options.increment = 1;
			$scope.updateSlider();
		}
		$scope.newTemplateQuestion.subOptions = [];
	};

	$scope.submitTemplateQuestion = function () {
		if ($scope.checkForm()) {
			// Submit
			$.ajax({
				type: "POST",
				url: "template-question/insert/template-question",
				data: $scope.newTemplateQuestion,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_ADD.ERROR_SET_TEMPLATE_QUESTION'));
				},
				complete: function(err) {
					$state.go('questionnaire/template-question');
				}
			});
		}
	};

	// Update values from form
	$scope.updateQuestionText = function () {
		$scope.titleSection.open = true;
		if (!$scope.newTemplateQuestion.name_EN && !$scope.newTemplateQuestion.name_FR) {
			$scope.titleSection.open = false;
		}
		if ($scope.newTemplateQuestion.name_EN && $scope.newTemplateQuestion.name_FR) {
			$scope.answerTypeSection.show = true;
			steps.name.completed = true;
		} else {
			steps.name.completed = false;
		}
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// questionnaire API: retrieve data
	questionnaireCollectionService.getTemplateQuestionCategory().then(function (response) {
		$scope.atCatList = response.data;
		$scope.atCatList.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.category_display = entry.category_FR;
			else
				entry.category_display = entry.category_EN;
		});
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_ADD.ERROR_SET_TEMPLATE_QUESTION'));
		$state.go('questionnaire/template-question');
	});

	// add options
	$scope.addOptions = function () {
		$scope.newTemplateQuestion.subOptions.push({
			description_EN: "",
			description_FR: "",
			order: $scope.newTemplateQuestion.subOptions.length + 1,
			OAUserId: OAUserId
		});
		$scope.checkCompletion();
	};

	// delete options
	$scope.deleteOptions = function (optionToDelete) {
		var index = $scope.newTemplateQuestion.subOptions.indexOf(optionToDelete);
		if (index > -1) {
			$scope.newTemplateQuestion.subOptions.splice(index, 1);
		}

		var i = 1;
		$scope.newTemplateQuestion.subOptions.forEach(function(entry) {
			entry.order = i;
			i++;
		});
	};

	$scope.orderPreview = function () {
		$scope.newTemplateQuestion.subOptions.sort(function(a,b){
			return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
		});
	};

	$scope.updateSelection = function (){
		$scope.newTemplateQuestion.options = [];
		$scope.updateSlider();
	};

	$scope.updateSlider = function () {
		var radiostep = new Array();
		var increment = parseFloat($scope.newTemplateQuestion.options.increment);
		var minValue = parseFloat($scope.newTemplateQuestion.options.minValue);
		var maxValue = parseFloat($scope.newTemplateQuestion.options.maxValue);

		if (minValue <= 0.0 || maxValue <= 0.0 || increment <= 0 || minValue >= maxValue || $scope.newTemplateQuestion.options.minCaption_EN === undefined || $scope.newTemplateQuestion.options.minCaption_FR === undefined || $scope.newTemplateQuestion.options.maxCaption_EN === undefined || $scope.newTemplateQuestion.options.maxCaption_FR === undefined)
			$scope.validSlider = false;
		else {
			$scope.newTemplateQuestion.options.maxValue = parseInt(maxValue);
			$scope.validSlider = true;
			for(var i = minValue; i <= maxValue; i += increment) {
				radiostep.push({"description_EN":" " + i,"description_FR":" " + i});
			}
			radiostep[0]["description_EN"] += " " + $scope.newTemplateQuestion.options.minCaption_EN;
			radiostep[0]["description_FR"] += " " + $scope.newTemplateQuestion.options.minCaption_FR;
			radiostep[radiostep.length - 1]["description_EN"] += " " + $scope.newTemplateQuestion.options.maxCaption_EN;
			radiostep[radiostep.length - 1]["description_FR"] += " " + $scope.newTemplateQuestion.options.maxCaption_FR;
		}
		$scope.preview = radiostep;
	};

	$scope.checkCompletion = function () {
		if($scope.newTemplateQuestion.typeId === "4" || $scope.newTemplateQuestion.typeId === "1") {
			var allGood = true;

			if (typeof $scope.newTemplateQuestion.subOptions === 'undefined' || $scope.newTemplateQuestion.subOptions.length <= 0)
				allGood = false;
			else
				$scope.newTemplateQuestion.subOptions.forEach(function(entry) {
					if (entry.description_EN === undefined || entry.description_FR === undefined  || entry.description_EN === "" || entry.description_FR === ""  || entry.order === 0 || entry.order === undefined)
						allGood = false;
				});
			if(allGood)
				steps.type.completed = true;
			else
				steps.type.completed = false;
		}
		else if($scope.newTemplateQuestion.typeId === "2") {
			if($scope.newTemplateQuestion.options) {
				if($scope.validSlider)
					steps.type.completed = true;
				else
					steps.type.completed = false;
			}
		}
		else
			steps.type.completed = true;
		$scope.numOfCompletedSteps = stepsCompleted(steps);
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
