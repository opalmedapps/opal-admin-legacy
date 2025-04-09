// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.question', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question', function ($location, $scope, $state, $filter, $uibModal, $translate, questionnaireCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.questionnaire];
		angular.forEach($scope.navSubMenu, function(menu) {
			menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
			menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
		});
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.questionnaire]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.questionnaire]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.questionnaire]) & (1 << 2)) !== 0);

		// Routing to go to add question page
		$scope.goToAddQuestion = function () {
			$state.go('questionnaire/question-add');
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
				['question_'+Session.retrieveObject('user').language, 'questionType_'+Session.retrieveObject('user').language, 'library_name_'+Session.retrieveObject('user').language].forEach(function (field) {
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

		// Templates for main question table
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editQuestion(row.entity)"><i title="'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editQuestion(row.entity)"><i title="'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';

		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteQuestion(row.entity)"><i title="'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';

		cellTemplateOperations += '</div>';

		var cellTemplateText = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestion(row.entity)">' +
			'<strong><a href="">{{row.entity.question_' + Session.retrieveObject('user').language + '}}</a></strong></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.library_name_'+ Session.retrieveObject('user').language +'}}</div>';
		var cellTemplateAt = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.questionType_'+ Session.retrieveObject('user').language +'}}</div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.PUBLIC')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.PRIVATE')+'</p></div>';

		var cellTemplateFinal = '<div class="ui-grid-cell-contents" ng-show="row.entity.final == 1"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.FINAL')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.final == 0"><p>'+$filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.DRAFT')+'</p></div>';

		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 1"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';
		// Data binding for question table
		$scope.gridLib = {
			data: 'questionList',
			columnDefs: [
				{ field: 'locked', enableColumnMenu: false, displayName: '', cellTemplate: cellTemplateLocked, width: '2%', sortable: false, enableFiltering: false},
				{ field: 'question_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.QUESTION'), cellTemplate: cellTemplateText, width: '49%' },
				{ field: 'questionType_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.RESPONSE_TYPE'), cellTemplate: cellTemplateAt, width: '13%' },
				{ field: 'library_name_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.LIBRARY'), cellTemplate: cellTemplateLib, width: '10%' },
				{
					field: 'private', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.PRIVACY'), enableColumnMenu: false, cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.PRIVATE') }, { value: '0', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.PUBLIC') }]
					}
				},
				{
					field: 'final', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.STATUS'), enableColumnMenu: false, cellTemplate: cellTemplateFinal, width: '8%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.FINAL') }, { value: '0', label: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.DRAFT') }]
					}
				},
				{ name: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.OPERATIONS'), width: '8%', enableColumnMenu: false, cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		function getQuestionsList() {
			questionnaireCollectionService.getQuestions().then(function (response) {
				$scope.questionList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.ERROR_QUESTIONS'));
			});
		}

		getQuestionsList();

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
				templateUrl: ($scope.writeAccess ? 'templates/questionnaire/edit.question.html' : 'templates/questionnaire/view.question.html'),
				controller: 'question.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// after update, refresh data
			modalInstance.result.then(function () {
				$scope.questionList = [];
				getQuestionsList();
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
				getQuestionsList();
			});
		};

	});
