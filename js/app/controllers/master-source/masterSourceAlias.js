angular.module('opalAdmin.controllers.masterSourceAlias', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('masterSourceAlias', function ($location, $scope, $state, $filter, $uibModal, $translate, masterSourceCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.master_source];
		angular.forEach($scope.navSubMenu, function(menu) {
			menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
			menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
		});

		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.master_source]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.master_source]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.master_source]) & (1 << 2)) !== 0);

		$scope.goToAddQuestion = function () {
			$state.go('master-source/alias-add');
		};

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

		// Templates for main alias table
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editSourceAlias(row.entity)"><i title="'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editSourceAlias(row.entity)"><i title="'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';

		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteMasterSourceAlias(row.entity)"><i title="'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';

		cellTemplateOperations += '</div>';

		var cellTemplateCode = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editSourceAlias(row.entity)">' +
			'<strong><a href="">{{row.entity.description}}</a></strong></div>';
		var cellTemplateSource =
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'1\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.ARIA')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'2\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.MEDIVISIT')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'3\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.MOSAIQ')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.source == \'4\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.OACIS')+'</p></div>';

		var cellTemplateType =
			'<div class="ui-grid-cell-contents" ng-show="row.entity.type == \'1\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.TASK')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.type == \'2\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.APPOINTMENT')+'</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.type == \'3\'"><p>'+$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.DOCUMENT')+'</p></div>';

		$scope.gridLib = {
			data: 'sourceList',
			columnDefs: [
				{ field: 'code', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.CODE'), width: '5%' },
				{ field: 'description', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.DESCRIPTION'), cellTemplate: cellTemplateCode },
				{ field: 'externalId', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.EXTERNAL_ID'), width: '7%' },
				{
					field: 'source', displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.SOURCE'), enableColumnMenu: false, cellTemplate: cellTemplateSource, width: '9%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.ARIA') }, { value: '2', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.MEDIVISIT') }, { value: '3', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.MOSAIQ') }, { value: '4', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.OACIS') }]
					}
				},
				{
					field: 'type', displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.TYPE'), enableColumnMenu: false, cellTemplate: cellTemplateType, width: '9%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.TASK') }, { value: '2', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.APPOINTMENT') }, { value: '3', label: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.DOCUMENT') }]
					}
				},
				{ field: 'creationDate', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.CREATION_DATE'), width: '11%' },
				{ field: 'createdBy', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.CREATED_BY'), width: '10%' },
				{ field: 'lastUpdated', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.LAST_UPDATED'), width: '11%' },
				{ field: 'updatedBy', enableColumnMenu: false, displayName: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.UPDATED_BY'), width: '10%' },

				{ name: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.OPERATIONS'), width: '8%', enableColumnMenu: false, cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		function getMasterSourceAliasList() {
			masterSourceCollectionService.getMasterSourceAliases().then(function (response) {
				$scope.sourceList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_LIST.ERROR_LIST'));
			});
		}

		getMasterSourceAliasList();

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

		// initialize variable for storing selected alias
		$scope.currentAlias = {};

		// function to edit alias
		$scope.editSourceAlias = function (alias) {
			$scope.currentAlias = alias;
			var modalInstance = $uibModal.open({
				templateUrl: ($scope.writeAccess ? 'templates/master-source/edit.masterSourceAlias.html' : 'templates/master-source/view.masterSourceAlias.html'),
				controller: 'masterSourceAlias.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// after update, refresh data
			modalInstance.result.then(function () {
				$scope.sourceList = [];
				getMasterSourceAliasList();
			});
		};

		// initialize variable for storing deleting question
		$scope.aliasToDelete = {};

		// function to delete question
		$scope.deleteMasterSourceAlias = function (currentAlias) {
			$scope.aliasToDelete = currentAlias;
			var modalInstance;
			modalInstance = $uibModal.open({
				templateUrl: 'templates/master-source/delete.masterSourceAlias.html',
				controller: 'masterSourceAlias.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});
			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.sourceList = [];
				getMasterSourceAliasList();
			});
		};

	});
