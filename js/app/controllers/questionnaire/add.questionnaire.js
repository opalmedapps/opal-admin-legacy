angular.module('opalAdmin.controllers.questionnaire.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).controller('questionnaire.add', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

	// navigation function
	$scope.goBack = function () {
		$state.go('questionnaire');
	};

	// Default booleans
	$scope.titleSection = {open: false, show: true};
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
	$scope.tagList = [];
	$scope.groupList = [];
	$scope.selectedGroups;
	$scope.tagFilter = "";

	// step bar
	var steps = {
		title: {completed: false},
		privacy: {completed: false},
		questions: {completed: false},
	};

	$scope.numOfCompletedSteps = 0;
	$scope.stepTotal = 3;
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
	$scope.showProcessingModal(); // Calling function

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

	// Initialize search field variables
	$scope.appointmentSearchField = "";
	$scope.dxSearchField = "";
	$scope.doctorSearchField = "";
	$scope.resourceSearchField = "";
	$scope.patientSearchField = "";

	// new questionnaire object
	$scope.newQuestionnaire = {
		text_EN: "",
		text_FR: "",
		private: undefined,
		OAUserId: OAUserId,
		questions: [],
	};

	$scope.formLoaded = false;
	// Function to load form as animations
	$scope.loadForm = function () {
		$('.form-box-left').addClass('fadeInDown');
		$('.form-box-right').addClass('fadeInRight');
	};

	filterCollectionService.getFilters().then(function () {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
		$scope.formLoaded = true;
		$scope.loadForm();
	});

	function decodeQuestions(questions) {
		questions.forEach(function(entry) {
			entry.text_EN = entry.text_EN.replace(/(<([^>]+)>)/ig,"");
			entry.text_FR = entry.text_FR.replace(/(<([^>]+)>)/ig,"");
			if(entry.typeId === "2") {
				var increment = parseFloat(entry.options.increment);
				var minValue = parseFloat(entry.options.minValue);
				if (minValue === 0.0) minValue = increment;
				var maxValue = parseFloat(entry.options.maxValue);

				var radiostep = new Array();
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
	$scope.titleUpdate = function () {

		$scope.titleSection.open = true;

		if (!$scope.newQuestionnaire.text_EN && !$scope.newQuestionnaire.text_FR) {
			$scope.titleSection.open = false;
		}

		if ($scope.newQuestionnaire.text_EN && $scope.newQuestionnaire.text_FR) {
			$scope.privacySection.show = true;
			steps.title.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		} else {
			steps.title.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
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
				alert("When selecting a private question, a questionnaire has to be set to private.");
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
		'<p>{{row.entity.text_EN}} / {{row.entity.text_FR}}</p></div>';
	var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
		'<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
	var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
		'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';


	// Table Data binding
	$scope.gridOptions = {
		data: 'groupList',
		columnDefs: [
			{field: 'text_EN', displayName: 'Name (EN / FR)', cellTemplate: cellTemplateName, width: '50%'},
			{field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '38%'},
			{
				field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
					type: uiGridConstants.filter.SELECT,
					selectOptions: [{value: '1', label: 'Private'}, {value: '0', label: 'Public'}]
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

			row.entity.order = $scope.newQuestionnaire.questions.length + 1;
			row.entity.optional = '0';
			$scope.newQuestionnaire.questions.push(row.entity);
		}
	};

	$scope.orderPreview = function () {
		$scope.newQuestionnaire.questions.sort(function(a,b){
			return (a.order > b.order) ? 1 : ((b.order > a.order) ? -1 : 0);
		});
	}

	questionnaireCollectionService.getFinalizedQuestions(OAUserId).then(function (response) {
		$scope.groupList = decodeQuestions(response.data);
	}).catch(function (response) {
		alert('Error occurred getting group list:' + response.status + response.data);
	});

	// Function to return boolean for form completion
	$scope.checkForm = function () {
		if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
			return true;
		else
			return false;
	};

	// submit
	$scope.submitQuestionnaire = function () {
		if ($scope.checkForm()) {
			// Submit
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.questionnaire.php",
				data: $scope.newQuestionnaire,
				success: function (result) {
					result = JSON.parse(result);
					if (result.code === 200) {
						$state.go('questionnaire');
					} else {
						alert("Unable to create the questionnaire. Code " + result.code + ".\r\nError message: " + result.message);
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
