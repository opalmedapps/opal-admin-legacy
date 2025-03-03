// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.template.question', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('template.question', function ($location, $scope, $state, $filter, $uibModal, questionnaireCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
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
			$state.go('questionnaire/template-question/add');
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
				['name_'+Session.retrieveObject('user').language, 'name_'+Session.retrieveObject('user').language, 'category_'+Session.retrieveObject('user').language, 'category_'+Session.retrieveObject('user').language].forEach(function (field) {
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
			'ng-click="grid.appScope.editTemplateQuestion(row.entity)">' +
			'<strong><a href="">{{row.entity.name_'+Session.retrieveObject('user').language+'}}</a></strong></div>';
		var cellTemplateAt = '<div class="ui-grid-cell-contents"> ' +
			'{{row.entity.category_'+Session.retrieveObject('user').language+'}}</div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editTemplateQuestion(row.entity)"><i title="'+$filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editTemplateQuestion(row.entity)"><i title="'+$filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';
		if($scope.deleteAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteTemplateQuestion(row.entity)"><i title="'+$filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';

		// Data binding for question table
		$scope.gridLib = {
			data: 'templateQuestionList',
			columnDefs: [
				{ field: 'name_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.NAME'), cellTemplate: cellTemplateTextEn, width: '50%' },
				{ field: 'category_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.CATEGORY'), cellTemplate: cellTemplateAt, width: '23%' },
				{
					field: 'private', enableColumnMenu: false, displayName: $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.PRIVACY'), cellTemplate: cellTemplatePrivacy, width: '18%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.PRIVATE') }, { value: '0', label: $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.PUBLIC') }]
					}
				},
				{ name: $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.OPERATIONS'), enableColumnMenu: false, width: '10%', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		getTemplateQuestionsList();

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
		$scope.currentTemplateQuestion = {};

		// function to edit question
		$scope.editTemplateQuestion = function (question) {
			$scope.currentTemplateQuestion = question;
			var modalInstance = $uibModal.open({
				templateUrl: ($scope.writeAccess ? 'templates/questionnaire/edit.template.question.html' : 'templates/questionnaire/view.template.question.html'),
				controller: 'template.question.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// after update, refresh data
			modalInstance.result.then(function () {
				$scope.templateQuestionList = [];
				getTemplateQuestionsList();
			});
		};

		// initialize variable for storing deleting question
		$scope.templateQuestionToDelete = {};

		// function to delete question
		$scope.deleteTemplateQuestion = function (currentTemplateQuestion) {

			$scope.templateQuestionToDelete = currentTemplateQuestion;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/questionnaire/delete.template.question.html',
				controller: 'template.question.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.templateQuestionList = [];
				getTemplateQuestionsList();
			});
		};

		function getTemplateQuestionsList() {
			questionnaireCollectionService.getTemplatesQuestions().then(function (response) {
				$scope.templateQuestionList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_LIST.ERROR_RESPONSE_TYPES_LIST'));
			});
		}
	});
