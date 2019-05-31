angular.module('opalAdmin.controllers.questionnaire', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaire', function ($sce, $scope, $state, $filter, $timeout, $uibModal, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// navigating functions
		$scope.goToQuestionnaire = function () {
			$state.go('questionnaire');
		};
		$scope.goToAddQuestionnaire = function () {
			$state.go('questionnaire-add');
		};
		$scope.goToQuestionnaireQuestionBank = function () {
			$state.go('questionnaire-question');
		};
		$scope.goToQuestionnaireCompleted = function () {
			$state.go('questionnaire-completed');
		};
		// Function to go to question type page
		$scope.goToQuestionType = function () {
			$state.go('questionnaire-question-type');
		};

		// Banner
		$scope.bannerMessage = "";
		// Function to show page banner 
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};

		// Function to set banner class
		$scope.setBannerClass = function (classname) {
			// Remove any classes starting with "alert-" 
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + classname);
		};

		// Filter
		// search text-box param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN'].forEach(function (field) {
					if (row.entity[field].match(matcher)) {
						match = true;
					}
				});
				if (!match) {
					row.visible = false;
				}
			});
			return renderableRows;
		};

		// Function to filter questionnaires
		$scope.filterQuestionnaire = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table
		// Templates
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editQuestionnaire(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteQuestionnaire(row.entity)">Delete</a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestionnaire(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
		var cellTemplatePublish = '<div class="ui-grid-cell-contents" ng-show="row.entity.publish == 0"><p>Draft</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.publish == 1"><p>Final</p></div>';
		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 1"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'questionnaireList',
			columnDefs: [
				{ field: 'locked', displayName: '', cellTemplate: cellTemplateLocked, width: '2%', sortable: false, enableFiltering: false},
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '53%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
				{
					field: 'publish', displayName: 'Final', cellTemplate: cellTemplatePublish, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Final' }, { value: '0', label: 'Draft' }]
					}
				},
				{ field: 'created_by', displayName: 'Author', width: '10%' },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize object for storing questionnaires
		$scope.questionnaireList = [];

		// Call API to get the list of questionnaires
		questionnaireCollectionService.getQuestionnaires(OAUserId).then(function (response) {
			$scope.questionnaireList = response.data;
		}).catch(function(response) {
			alert('Error occurred getting questionnaire list: ' + response.status + " " + response.data);
		});

		// Initialize the questionnaire to be deleted
		$scope.questionnaireToDelete = {};

		// Function to delete questionnaire
		$scope.deleteQuestionnaire = function (questionnaire) {
			$scope.questionnaireToDelete = questionnaire;

			if (questionnaire.locked) {
				modalInstance = $uibModal.open({
					templateUrl: 'templates/questionnaire/cannot.delete.questionnaire.html',
					controller: 'question.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			}
			else
			{
				var modalInstance = $uibModal.open({ // open modal
					templateUrl: 'templates/questionnaire/delete.questionnaire.html',
					controller: 'questionnaire.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			}

			// After delete, refresh the questionnaire list
			modalInstance.result.then(function () {
				questionnaireCollectionService.getQuestionnaires(OAUserId).then(function (response) {
					$scope.questionnaireList = response.data;
				}).catch(function(response) {
					alert('Error occurred getting questionnaire list after modal close: ' + response.status + " " + response.data);
				});
			});
		};

		// Initialize a scope variable for a selected questionnaire
		$scope.currentQuestionnaire = {};

		// Function to edit questionnaire
		$scope.editQuestionnaire = function (questionnaire) {
			$scope.currentQuestionnaire = questionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'templates/questionnaire/edit.questionnaire.html',
				controller: 'questionnaire.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				questionnaireCollectionService.getQuestionnaires(OAUserId).then(function (response) {
					$scope.questionnaireList = response.data;
				}).catch(function(response) {
					alert('Error occurred getting questionnaire list after modal close: ' + response.status + " " + response.data);
				});
			});
		};

	});
