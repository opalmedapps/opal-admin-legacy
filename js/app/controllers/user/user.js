angular.module('opalAdmin.controllers.user', ['ui.bootstrap', 'ui.grid']).


/******************************************************************************
 * Controller for the users page
 *******************************************************************************/
controller('user', function ($scope, $uibModal, $filter, $sce, $state, userCollectionService, Encrypt) {

	// Function to go to register new user
	$scope.goToAddUser = function () {
		$state.go('user-register');
	};

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

	// Templates for the users table
	var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
		'<strong><a href="" ng-click="grid.appScope.showActivityLog(row.entity)"><i title="'+$filter('translate')('USERS.LIST.LOGS')+'" class="fa fa-area-chart" aria-hidden="true"></i></a></strong> ' +
		'- <strong><a href="" ng-click="grid.appScope.editUser(row.entity)"><i title="'+$filter('translate')('USERS.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ' +
		'- <strong><a href="" ng-click="grid.appScope.deleteUser(row.entity)"><i title="'+$filter('translate')('USERS.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong></div>';

	// user table search textbox param
	$scope.filterOptions = function (renderableRows) {
		var matcher = new RegExp($scope.filterValue, 'i');
		renderableRows.forEach(function (row) {
			var match = false;
			['username'].forEach(function (field) {
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

	$scope.filterUser = function (filterValue) {
		$scope.filterValue = filterValue;
		$scope.gridApi.grid.refresh();

	};

	// Table options for user
	$scope.gridOptions = {
		data: 'userList',
		columnDefs: [
			{ field: 'username', displayName: $filter('translate')('USERS.LIST.USERNAME'), width: '50%', enableColumnMenu: false },
			{ field: 'role', displayName: $filter('translate')('USERS.LIST.ROLE'), width: '35%', enableColumnMenu: false },
			{ name: $filter('translate')('USERS.LIST.OPERATIONS'), cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '15%', enableColumnMenu: false }
		],
		enableColumnResizing: true,
		enableFiltering: true,
		onRegisterApi: function (gridApi) {
			$scope.gridApi = gridApi;
			$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
		},
	};

	// Initialize list of existing users
	$scope.userList = [];

	getUsersList();

	// Function for when a user has been clicked for deletion
	// Open a modal
	$scope.userToDelete = null;
	$scope.deleteUser = function (currentUser) {

		$scope.userToDelete = currentUser;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/user/delete.user.html',
			windowClass: 'deleteModal',
			controller: 'user.delete',
			scope: $scope,
			backdrop: 'static'
		});

		// After delete, refresh the user list
		modalInstance.result.then(function () {
			getUsersList();
		});
	};

	// Function for when the user has been clicked for viewing activity logs
	$scope.showActivityLog = function (user) {

		$scope.currentUser = user;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/user/log.user.html',
			controller: 'user.log',
			scope: $scope,
			windowClass: 'logModal',
			backdrop: 'static',
		});
	};

	// Function for when the user has been clicked for editing
	// We open a modal
	$scope.editUser = function (user) {

		$scope.currentUser = user;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/user/edit.user.html',
			controller: 'user.edit',
			scope: $scope,
			windowClass: 'editUserModal',
			backdrop: 'static'
		});

		// After update, refresh the user list
		modalInstance.result.then(function () {
			getUsersList();
		});
	};

	function getUsersList() {
		userCollectionService.getUsers().then(function (response) {
			$scope.userList = response.data;
		}).catch(function(response) {
			alert($filter('translate')('USERS.LIST.ERROR_USERS') + "\r\n" + response.status + " - " + response.data);
		});
	}
});

