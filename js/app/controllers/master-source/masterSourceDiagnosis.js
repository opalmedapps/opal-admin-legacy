angular.module('opalAdmin.controllers.masterSourceDiagnosis', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('masterSourceDiagnosis', function ($location, $scope, $state, $filter, $uibModal, $translate, masterSourceCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.master_source];
		angular.forEach($scope.navSubMenu, function(menu) {
			menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
			menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
		});

		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.master_source]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.master_source]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.master_source]) & (1 << 2)) !== 0);

		// Routing to go to add question page
		$scope.goToAddQuestion = function () {
			$state.go('master-source/diagnosis-add');
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
				['code', 'description', 'externalId', 'creationDate', 'createdBy', 'lastUpdated', 'updatedBy'].forEach(function (field) {
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
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editQuestion(row.entity)"><i title="'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editQuestion(row.entity)"><i title="'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';

		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteMasterSourceDiagnosis(row.entity)"><i title="'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';

		cellTemplateOperations += '</div>';

		var cellTemplateCode = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editMasterSourceDiagnosis(row.entity)">' +
			'<strong><a href="">{{row.entity.code}}</a></strong></div>';
		var cellTemplatePrivacy =
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'1\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.ARIA')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'2\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.MEDIVISIT')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'3\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.MOSAIQ')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'4\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.OACIS')+'</p></div>';

		// Data binding for question table
		$scope.gridLib = {
			data: 'sourceList',
			columnDefs: [
				{ field: 'code', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.CODE'), cellTemplate: cellTemplateCode, width: '5%' },
				{ field: 'description', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.DESCRIPTION') },
				{ field: 'externalId', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.EXTERNAL_ID'), width: '7%' },
				{
					field: 'source', displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.SOURCE'), enableColumnMenu: false, cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.ARIA') }, { value: '2', label: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.MEDIVISIT') }, { value: '3', label: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.MOSAIQ') }, { value: '4', label: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.OACIS') }]
					}
				},
				{ field: 'creationDate', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.CREATION_DATE'), width: '13%' },
				{ field: 'createdBy', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.CREATED_BY'), width: '10%' },
				{ field: 'lastUpdated', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.LAST_UPDATED'), width: '13%' },
				{ field: 'updatedBy', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.UPDATED_BY'), width: '10%' },

				{ name: $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.OPERATIONS'), width: '8%', enableColumnMenu: false, cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		function getMasterSourceDiagList() {
			masterSourceCollectionService.getMasterSourceDiagnoses().then(function (response) {
				$scope.sourceList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_LIST.ERROR_LIST'));
			});
		}

		getMasterSourceDiagList();

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
		$scope.currentDiagnosis = {};

		// function to edit question
		$scope.editMasterSourceDiagnosis = function (diagnosis) {
			$scope.currentDiagnosis = diagnosis;
			var modalInstance = $uibModal.open({
				templateUrl: ($scope.writeAccess ? 'templates/master-source/edit.master.source.diagnosis.html' : 'templates/master-source/view.master.source.diagnosis.html'),
				controller: 'masterSourceDiagnosis.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// after update, refresh data
			modalInstance.result.then(function () {
				$scope.sourceList = [];
				getMasterSourceDiagList();
			});
		};

		// initialize variable for storing deleting question
		$scope.questionToDelete = {};

		// function to delete question
		$scope.deleteMasterSourceDiagnosis = function (currentDiagnosis) {
			$scope.questionToDelete = currentDiagnosis;
			var modalInstance;
			if (currentDiagnosis.locked) {
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
				$scope.sourceList = [];
				getMasterSourceDiagList();
			});
		};

	});
