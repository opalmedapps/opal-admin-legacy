angular.module('opalAdmin.controllers.role', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('role', function ($scope, $state, $filter, $uibModal, roleCollectionService, Session, uiGridConstants, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.role]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.role]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.role]) & (1 << 2)) !== 0);

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		$scope.goToAddRole = function () {
			$state.go('role-add');
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

		$scope.filterRole = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		getRolesList();

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
				['name_EN', 'name_FR'].forEach(function (field) {
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
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editRole(row.entity)"<i title="'+$filter('translate')('ROLE.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong>';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editRole(row.entity)"<i title="'+$filter('translate')('ROLE.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong>';
		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteRole(row.entity)"><i title="'+$filter('translate')('ROLE.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';
		var cellTemplateEnglish = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editRole(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}}</a></strong></div>';
		var cellTemplateFrench = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editRole(row.entity)">' +
			'<strong><a href="">{{row.entity.name_FR}}</a></strong></div>';
		var cellTemplatePublication = '<div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==1">'+$filter('translate')('ROLE.LIST.ALIAS')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==6">'+$filter('translate')('ROLE.LIST.DIAGNOSTIC')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==9">'+$filter('translate')('ROLE.LIST.TEST')+'</div>';
		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked > 0"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';

		$scope.gridOptions = {
			data: 'rolesList',
			columnDefs: [
				{ field: 'name_EN', enableColumnMenu: false, displayName: $filter('translate')('ROLE.LIST.ENGLISH'), cellTemplate: cellTemplateEnglish, sort: {direction: uiGridConstants.ASC, priority: 0}},
				{ field: 'name_FR', enableColumnMenu: false, displayName: $filter('translate')('ROLE.LIST.FRENCH'), cellTemplate: cellTemplateFrench	},
				{ field: 'total', enableColumnMenu: false, displayName: $filter('translate')('ROLE.LIST.TOTAL'),sortable: false	},
				{ name: $filter('translate')('ROLE.LIST.OPERATIONS'), width: '10%', cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		$scope.rolesList = [];

		function getRolesList() {
			roleCollectionService.getRoles(OAUserId).then(function (response) {
				$scope.rolesList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('ROLE.LIST.ERROR'));
			});
		}

		$scope.editRole = function (role) {
			$scope.currentRole = role;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: ($scope.writeAccess ? 'templates/role/edit.role.html' : 'templates/role/view.role.html'),
				controller: 'role.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			modalInstance.result.then(function () {
				getRolesList();
			});
		};

		$scope.deleteRole = function (currentRole) {
			$scope.roleToDelete = currentRole;
			var modalInstance;
			if(parseInt(currentRole.total) <= 0) {
				modalInstance = $uibModal.open({
					templateUrl: 'templates/role/delete.role.html',
					controller: 'role.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			} else {
				modalInstance = $uibModal.open({
					templateUrl: 'templates/role/cannot.delete.role.html',
					controller: 'role.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			}

			modalInstance.result.then(function () {
				getRolesList();
			});
		};
	});