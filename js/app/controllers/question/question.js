angular.module('opalAdmin.controllers.question', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Routing to go to add question page
		$scope.goToAddQuestion = function () {
			$state.go('questionnaire-question-add');
		};

		// Table
		// Filter in table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['text_EN', 'group_name_EN', 'answertype_name_EN', 'library_name_EN'].forEach(function (field) {
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
		var cellTemplateGroupName = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.group_name_EN}} / {{row.entity.group_name_FR}}</div>';
		var cellTemplateText = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<strong><a href="">{{row.entity.text_EN}} / {{row.entity.text_FR}}</a></strong></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</div>';
		var cellTemplateAt = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.answertype_name_EN}} / {{row.entity.answertype_name_FR}}</div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';

		// Data binding for question table
		$scope.gridLib = {
			data: 'questionList',
			columnDefs: [
				{ field: 'serNum', displayName: 'ID', width: '4%', enableFiltering: false },
				{ field: 'text_EN', displayName: 'Question (EN / FR)', cellTemplate: cellTemplateText, width: '30%' },
				{ field: 'group_name_EN', displayName: 'Group (EN / FR)', cellTemplate: cellTemplateGroupName, width: '25%' },
				{ field: 'answertype_name_EN', displayName: 'Answer Type (EN / FR)', cellTemplate: cellTemplateAt, width: '13%' },
				{ field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '10%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '8%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
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
		questionnaireCollectionService.getQuestions().then(function (response) {
			$scope.questionList = response.data;
			
		}).catch(function(response) {
			console.error('Error occurred getting question list:', response.status, response.data);
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
				questionnaireCollectionService.getQuestions().then(function (response) {
					$scope.questionList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting question list after modal close:', response.status, response.data);
				});

			});
		};

		// initialize variable for storing deleting question
		$scope.questionToDelete = {};

		// function to delete question
		$scope.deleteQuestion = function (currentQuestion) {
			$scope.questionToDelete = currentQuestion;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/questionnaire/delete.question.html',
				controller: 'question.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.questionList = [];
				// update data
				questionnaireCollectionService.getQuestions().then(function (response) {
					$scope.questionList = response.data;
				}).catch(function (response){
					console.error('Error occurred getting question lsit after modal close:', response.status, response.data);
				});
			});
		};

	});
