angular.module('opalAdmin.controllers.role', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('role', function ($scope, $state, $filter, $uibModal, roleCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');1
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

		// Function to filter custom codes
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

		// Table
		// Templates
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editRole(row.entity)"<i title="'+$filter('translate')('ROLE.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong>' +
			'- <strong><a href="" ng-click="grid.appScope.deleteRole(row.entity)"><i title="'+$filter('translate')('ROLE.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong></div>';
		var cellTemplateEnglish = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editRole(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}}</a></strong></div>';
		var cellTemplateFrench = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editRole(row.entity)">' +
			'<strong><a href="">{{row.entity.name_FR}}</a></strong></div>';
		var cellTemplatePublication = '<div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==1">'+$filter('translate')('ROLE.LIST.ALIAS')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==6">'+$filter('translate')('ROLE.LIST.DIAGNOSTIC')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==9">'+$filter('translate')('ROLE.LIST.TEST')+'</div>';
		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked > 0"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'rolesList',
			columnDefs: [
				{ field: 'name_EN', enableColumnMenu: false, displayName: $filter('translate')('ROLE.LIST.ENGLISH'), cellTemplate: cellTemplateEnglish, sort: {direction: uiGridConstants.ASC, priority: 0}},
				{ field: 'name_FR', enableColumnMenu: false, displayName: $filter('translate')('ROLE.LIST.FRENCH'), cellTemplate: cellTemplateFrench	},
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

		// Initialize object for storing questionnaires
		$scope.rolesList = [];

		function getRolesList() {
			roleCollectionService.getRoles(OAUserId).then(function (response) {
				$scope.rolesList = response.data;
			}).catch(function(err) {
				alert($filter('translate')('ROLE.LIST.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.data));
			});
		}

		// Function to edit questionnaire
		$scope.editRole = function (role) {
			$scope.currentRole = role;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'templates/role/edit.role.html',
				controller: 'role.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				getRolesList();
			});
		};

		// Function for when the custom code has been clicked for deletion
		// Open a modal
		$scope.deleteRole = function (currentRole) {
			// Assign selected custom code as the custom code to delete
			$scope.roleToDelete = currentRole;

			var modalInstance = $uibModal.open({
				templateUrl: 'templates/role/delete.role.html',
				controller: 'role.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the custom code list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				getRolesList();
			});
		};
	});