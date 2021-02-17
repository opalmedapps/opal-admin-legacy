angular.module('opalAdmin.controllers.questionnaire.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).controller('questionnaire.add', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, Session, uiGridConstants, ErrorHandler) {

	// navigation function
	$scope.goBack = function () {
		$state.go('questionnaire');
	};

	// Default booleans
	$scope.titleDescriptionSection = {open: false, show: true};
	$scope.privacySection = {open: false, show: false};
	$scope.questionsSection = {open: false, show: false};
	$scope.demoSection = {open: false, show: false};
	$scope.filterSection = {open: false, show: false};
	$scope.anyPrivate = false;

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	var publicPrivateWarning = true;

	// initialize variables
	$scope.groupList = [];
	$scope.selectedGroups;

	// Default toolbar for wysiwyg
	$scope.toolbar = [
		['h1', 'h2', 'h3', 'p'],
		['bold', 'italics', 'underline', 'ul', 'ol'],
		['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
		['html', 'insertLink']
	];

	// step bar
	var steps = {
		titleDescriptionSection: {completed: false},
		privacy: {completed: false},
		questions: {completed: false},
	};

	$scope.numOfCompletedSteps = 0;
	$scope.stepTotal = 3;
	$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	// Function to calculate / return step progress
	function trackProgress(value, total) {
		return Math.round(100 * value / total);
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

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');

	// new questionnaire object
	$scope.newQuestionnaire = {
		title_EN: "",
		title_FR: "",
		description_EN: "",
		description_FR: "",
		private: undefined,
		OAUserId: OAUserId,
		questions: [],
	};

	function decodeQuestions(questions) {
		questions.forEach(function(entry) {
			entry.question_EN = entry.question_EN.replace(/(<([^>]+)>)/ig,"");
			entry.question_FR = entry.question_FR.replace(/(<([^>]+)>)/ig,"");
			if (Session.retrieveObject('user').language.toUpperCase() === "FR") {
				entry.questionDisplay = entry.question_FR;
				entry.libraryDisplay = entry.library_name_FR;
			}
			else {
				entry.questionDisplay = entry.question_EN;
				entry.libraryDisplay = entry.library_name_EN;
			}

			if(entry.typeId === "2") {
				var increment = parseInt(entry.options.increment);
				var minValue = parseInt(entry.options.minValue);
				if (minValue < 0) minValue = 0;
				var maxValue = parseInt(entry.options.maxValue);

				var radiostep = [];
				for(var i = minValue; i <= maxValue; i += increment) {
					radiostep.push({"description":" " + i,"description_EN":" " + i,"description_FR":" " + i});
				}
				radiostep[0]["description"] += " " + entry.options.minCaption_EN + " / " + entry.options.minCaption_FR;
				radiostep[0]["description_EN"] += " " + entry.options.minCaption_EN;
				radiostep[0]["description_FR"] += " " + entry.options.minCaption_FR;
				radiostep[radiostep.length - 1]["description"] += " " + entry.options.maxCaption_EN + " / " + entry.options.maxCaption_FR;
				radiostep[radiostep.length - 1]["description_EN"] += " " + entry.options.maxCaption_EN;
				radiostep[radiostep.length - 1]["description_FR"] += " " + entry.options.maxCaption_FR;
				entry.subOptions = radiostep;
			}
		});
		return questions;
	}

	// update form functions

	$scope.titleDescriptionUpdate = function () {

		$scope.titleDescriptionSection.open = true;

		if ($scope.newQuestionnaire.title_EN && $scope.newQuestionnaire.title_FR &&
			$scope.newQuestionnaire.description_EN && $scope.newQuestionnaire.description_FR) {
			steps.titleDescriptionSection.completed = true;
			$scope.privacySection.show = true;
		} else {
			steps.titleDescriptionSection.completed = false;
		}
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	$scope.privacyUpdate = function (value) {
		if(!$scope.anyPrivate) {
			$scope.privacySection.open = true;
			if (value == 0 || value == 1) {
				// update value
				$scope.newQuestionnaire.private = value;
				$scope.questionsSection.show = true;
				steps.privacy.completed = true;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				steps.privacy.completed = false;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		}
	};

	var questionsUpdate = function () {
		$scope.questionsSection.open = true;
		if ($scope.newQuestionnaire.questions.length) {
			steps.questions.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		} else {
			$scope.questionsSection.open = false;
			steps.questions.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}

		var anyPrivate = false;

		$scope.newQuestionnaire.questions.forEach(function(entry) {
			if(entry.private === "1")
				anyPrivate = true;
		});

		$scope.anyPrivate = anyPrivate;
		if (anyPrivate) {
			document.getElementById("btn-public").classList.add("disabled");
			document.getElementById("btn-public").classList.remove("animated");
			if(publicPrivateWarning && $scope.newQuestionnaire.private !== 1) {
				publicPrivateWarning = false;
				alert($filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PRIVATE_QUESTION'));
			}
			$scope.newQuestionnaire.private = 1;
		}
		else {
			document.getElementById("btn-public").classList.remove("disabled");
			document.getElementById("btn-public").classList.add("animated");
			publicPrivateWarning = true;
		}


	};

	// table
	// Filter in table
	$scope.filterOptions = function (renderableRows) {
		return renderableRows;
	};

	// Template for table
	var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
		'<p>{{row.entity.questionDisplay}}</p></div>';
	var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
		'<p>{{row.entity.libraryDisplay}}</p></div>';
	var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PUBLIC')+'</p></div>' +
		'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_EDIT.PRIVATE')+'</p></div>';


	// Table Data binding
	$scope.gridOptions = {
		data: 'groupList',
		columnDefs: [
			{field: 'questionDisplay', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.TITLE'), cellTemplate: cellTemplateName, width: '63%'},
			{field: 'libraryDisplay', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.LIBRARY'), cellTemplate: cellTemplateLib, width: '20%'},
			{
				field: 'private', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.PRIVACY'), cellTemplate: cellTemplatePrivacy, width: '15%', filter: {
					type: uiGridConstants.filter.SELECT,
					selectOptions: [{value: '1', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.PRIVATE')}, {value: '0', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.PUBLIC')}]
				}
			},
		],
		enableColumnResizing: true,
		enableFiltering: true,
		enableSorting: true,
		enableRowSelection: true,
		enableSelectAll: false,
		enableSelectionBatchEvent: true,
		showGridFooter:false,
		onRegisterApi: function (gridApi) {
			$scope.gridApi = gridApi;
			gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			gridApi.selection.on.rowSelectionChanged($scope, function (row) {
				selectUpdate(row);
				questionsUpdate();
			});
		},
	};

	// function to update the newQuestionnaire content after changing selection
	var selectUpdate = function (row) {
		$scope.selectedGroups = $scope.gridApi.selection.getSelectedGridRows();
		var selectedNum = $scope.gridApi.selection.getSelectedCount();
		if (selectedNum === 0) {
			$scope.newQuestionnaire.questions = [];
		} else {
			var indexDup = -1;

			$scope.newQuestionnaire.questions.forEach(function(entry, index) {
				if(entry.ID === row.entity.ID) {
					indexDup = index;
				}
			});

			if(indexDup === -1){
				row.entity.order = $scope.newQuestionnaire.questions.length + 1;
				row.entity.optional = '0';
				$scope.newQuestionnaire.questions.push(row.entity);
			}
			else {
				$scope.newQuestionnaire.questions.splice(indexDup, 1);
				$scope.newQuestionnaire.questions.forEach(function(entry, index) {
					entry.order = index + 1;
				});
			}
		}
	};

	$scope.orderPreview = function () {
		$scope.newQuestionnaire.questions.sort(function(a,b){
			return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
		});
	};

	questionnaireCollectionService.getFinalizedQuestions(OAUserId).then(function (response) {
		$scope.groupList = decodeQuestions(response.data);
	}).catch(function (err) {
		ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.ERROR_QUESTION_lIST'));
		$state.go('questionnaire');
	});

	// Function to return boolean for form completion
	$scope.checkForm = function () {
		if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) === 100)
			return true;
		else
			return false;
	};

	// submit
	$scope.submitQuestionnaire = function () {
		if ($scope.checkForm()) {

			var formatData = copyQuestionnaireData($scope.newQuestionnaire);

			$.ajax({
				method: "POST",
				url: "questionnaire/insert/questionnaire",
				data: formatData,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_ADD.ERROR_CREATION_QUESTIONNAIRE'));
				},
				complete: function() {
					$state.go('questionnaire');
				}
			});
		}
	};

	function copyQuestionnaireData(oldData) {
		var newFormat = {
			OAUserId : oldData.OAUserId,
			title_EN : oldData.title_EN,
			title_FR : oldData.title_FR,
			description_EN : oldData.description_EN,
			description_FR : oldData.description_FR,
			private : oldData.private,
			questions : []
		};
		var temp;
		angular.forEach(oldData.questions, function(item) {
			temp = {
				ID : item.ID,
				order : item.order,
				optional : item.optional,
				typeId : item.typeId,
			};
			newFormat.questions.push(temp);
		});
		return newFormat;
	}

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
	$(window).scroll(function () {
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
