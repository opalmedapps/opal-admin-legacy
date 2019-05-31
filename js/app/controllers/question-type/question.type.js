angular.module('opalAdmin.controllers.question.type', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.type', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

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
				['name_EN', 'name_FR', 'category_EN', 'category_FR'].forEach(function (field) {
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
		var cellTemplateTextEn = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}}</a></strong></div>';
		var cellTemplateTextFr = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<strong><a href="">{{row.entity.name_FR}}</a></strong></div>';
		var cellTemplateAt = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.category_EN}} / {{row.entity.category_FR}}</div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editQuestionType(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteQuestionType(row.entity)">Delete</a></strong></div>';

		// Data binding for question table
		$scope.gridLib = {
			data: 'questionTypeList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Name (EN)', cellTemplate: cellTemplateTextEn, width: '30%' },
				{ field: 'name_FR', displayName: 'Name (FR)', cellTemplate: cellTemplateTextFr, width: '30%' },
				{ field: 'category_EN', displayName: 'Response Category (EN / FR)', cellTemplate: cellTemplateAt, width: '23%' },
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

		// Call our API service to get the list of existing questions types
		questionnaireCollectionService.getQuestionTypes(Session.retrieveObject('user').id).then(function (response) {
			$scope.questionTypeList = response.data;
		}).catch(function(response) {
			alert('Error occurred getting response types: '+response.status +"\r\n"+ response.data);
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
				$scope.questionTypeList = [];
				// Call our API service to get the list of existing questions
				questionnaireCollectionService.getQuestionTypes(Session.retrieveObject('user').id).then(function (response) {
					$scope.questionTypeList = response.data;
				}).catch(function(response) {
					alert('Error occurred getting response types: '+response.status +"\r\n"+ response.data);
				});
			});
		};

		// initialize variable for storing deleting question
		$scope.questionTypeToDelete = {};

		// function to delete question
		$scope.deleteQuestionType = function (currentQuestion) {

			$scope.questionTypeToDelete = currentQuestion;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/questionnaire/delete.question.type.html',
				controller: 'question.type.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.questionTypeList = [];
				// update data
				questionnaireCollectionService.getQuestionTypes(Session.retrieveObject('user').id).then(function (response) {
					$scope.questionTypeList = response.data;
				}).catch(function(response) {
					alert('Error occurred getting response types: '+response.status +"\r\n"+ response.data);
				});
			});
		};

	});
