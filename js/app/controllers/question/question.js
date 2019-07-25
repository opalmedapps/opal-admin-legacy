angular.module('opalAdmin.controllers.question', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Routing to go to add question page
		$scope.goToAddQuestion = function () {
			$state.go('questionnaire-question-add');
		};

		// Function to filter questionnaires
		$scope.filterQuestion = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		// Table
		// Filter in table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['question_EN', 'question_FR', 'questionType_EN', 'questionType_FR', 'library_name_EN', 'library_name_FR'].forEach(function (field) {
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
		$scope.filterLibrary = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		// Templates for main question table
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editQuestion(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteQuestion(row.entity)">Delete</a></strong></div>';
		var cellTemplateText = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<strong><a href="">{{row.entity.question_EN}} / {{row.entity.question_FR}}</a></strong></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</div>';
		var cellTemplateAt = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.questionType_EN}} / {{row.entity.questionType_FR}}</div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';

		var cellTemplateFinal = '<div class="ui-grid-cell-contents" ng-show="row.entity.final == 1"><p>Final</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.final == 0"><p>Draft</p></div>';

		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 1"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';
		// Data binding for question table
		$scope.gridLib = {
			data: 'questionList',
			columnDefs: [
				{ field: 'locked', displayName: '', cellTemplate: cellTemplateLocked, width: '2%', sortable: false, enableFiltering: false},
				{ field: 'question_EN', displayName: 'Question (EN / FR)', cellTemplate: cellTemplateText, width: '49%' },
				{ field: 'questionType_EN', displayName: 'Response Type (EN / FR)', cellTemplate: cellTemplateAt, width: '13%' },
				{ field: 'library_name_EN', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.LIBRARY'), cellTemplate: cellTemplateLib, width: '10%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '8%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
				{
					field: 'final', displayName: 'Status', cellTemplate: cellTemplateFinal, width: '8%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Final' }, { value: '0', label: 'Draft' }]
					}
				},
				{ name: 'Operations', width: '10%', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Call our API service to get the list of existing questions
		questionnaireCollectionService.getQuestions(Session.retrieveObject('user').id).then(function (response) {
			$scope.questionList = response.data;

		}).catch(function(response) {
			alert('Error occurred getting question list:\r\n' + response.status + " " + response.data);
		});

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

		// initialize variable for storing selected question
		$scope.currentQuestion = {};

		// function to edit question
		$scope.editQuestion = function (question) {
			$scope.currentQuestion = question;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/questionnaire/edit.question.html',
				controller: 'question.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// after update, refresh data
			modalInstance.result.then(function () {
				$scope.questionList = [];
				// Call our API service to get the list of existing questions
				questionnaireCollectionService.getQuestions(Session.retrieveObject('user').id).then(function (response) {
					$scope.questionList = response.data;
				}).catch(function(response) {
					alert('Error occurred getting question list after modal close:\r\n' + response.status + " " + response.data);
				});

			});
		};

		// initialize variable for storing deleting question
		$scope.questionToDelete = {};

		// function to delete question
		$scope.deleteQuestion = function (currentQuestion) {


			$scope.questionToDelete = currentQuestion;
			var modalInstance;

			if (currentQuestion.locked) {
				modalInstance = $uibModal.open({
					templateUrl: 'templates/questionnaire/cannot.delete.question.html',
					controller: 'question.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			} else {
				modalInstance = $uibModal.open({
					templateUrl: 'templates/questionnaire/delete.question.html',
					controller: 'question.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			}
			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.questionList = [];
				// update data
				questionnaireCollectionService.getQuestions(Session.retrieveObject('user').id).then(function (response) {
					$scope.questionList = response.data;
				}).catch(function (response) {
					alert('Error occurred getting question list after modal close. Code: ' + response.status + "\r\n" +  response.data);
				});
			});
		};

	});
