angular.module('opalAdmin.controllers.study', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('study', function ($scope, $state, $filter, $uibModal, studyCollectionService, Session, uiGridConstants, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.study]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.study]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.study]) & (1 << 2)) !== 0);

		// get current user id
		var user = Session.retrieveObject('user');1
		var OAUserId = user.id;

		$scope.goToAddStudy = function () {
			$state.go('study-add');
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

		// Function to filter custom codes
		$scope.filterStudy = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		getstudiesList();

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
				['code', 'title'].forEach(function (field) {
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

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editStudy(row.entity)"<i title="'+$filter('translate')('STUDY.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong>';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editStudy(row.entity)"<i title="'+$filter('translate')('STUDY.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong>';
		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteStudy(row.entity)"><i title="'+$filter('translate')('STUDY.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editStudy(row.entity)">' +
			'<strong><a href="">{{row.entity.title}}</a></strong></div>';
		var cellTemplatePublication = '<div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==1">'+$filter('translate')('STUDY.LIST.ALIAS')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==6">'+$filter('translate')('STUDY.LIST.DIAGNOSTIC')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==9">'+$filter('translate')('STUDY.LIST.TEST')+'</div>';
		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked > 0"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'studiesList',
			columnDefs: [
				{ field: 'title', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.TITLE_2'), cellTemplate: cellTemplateName, sort: {direction: uiGridConstants.ASC, priority: 0}},
				{ field: 'code', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.STUDY_ID'), width: '10%'},
				{ field: 'investigator', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.INVESTIGATOR'), width: '15%'},
				{ field: 'startDate', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.START_DATE'), width: '10%'},
				{ field: 'endDate', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.END_DATE'), width: '10%'},
				{ field: 'creationDate', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.CREATION_DATE'), width: '15%'},
				{ name: $filter('translate')('STUDY.LIST.OPERATIONS'), width: '10%', cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
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
		$scope.studiesList = [];

		function getstudiesList() {
			studyCollectionService.getStudies(OAUserId).then(function (response) {
				$scope.studiesList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('STUDY.LIST.ERROR_PUBLICATION'));
			});
		}

		// Function to edit questionnaire
		$scope.editStudy = function (study) {
			$scope.currentStudy = study;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: ($scope.writeAccess ? 'templates/study/edit.study.html' : 'templates/study/view.study.html'),
				controller: 'study.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				getstudiesList();
			});
		};

		// Function for when the custom code has been clicked for deletion
		// Open a modal
		$scope.deleteStudy = function (currentStudy) {
			// Assign selected custom code as the custom code to delete
			$scope.studyToDelete = currentStudy;

			var modalInstance = $uibModal.open({
				templateUrl: 'templates/study/delete.study.html',
				controller: 'study.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the custom code list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				getstudiesList();
			});
		};
	});

