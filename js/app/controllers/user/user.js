angular.module('opalAdmin.controllers.user', ['ui.bootstrap', 'ui.grid']).


/******************************************************************************
 * Controller for the users page
 *******************************************************************************/
controller('user', function ($scope, $uibModal, $filter, $state, userCollectionService, Session, ErrorHandler, uiGridConstants, MODULE) {
	$scope.navMenu = Session.retrieveObject('menu');
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.user]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.user]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.user]) & (1 << 2)) !== 0);
	
	var OAUserId = Session.retrieveObject('user').id;
	// Function to go to register new user
	$scope.goToAddUser = function () {

		if ($scope.configs.login.activeDirectory.enabled === 1)
			$state.go('user-ad-register');
		else
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
	var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
		'ng-click="grid.appScope.editUser(row.entity)">' +
		'<strong><a href="">{{row.entity.username}}</a></strong></div>';

	var cellTemplateFinal = '<div class="ui-grid-cell-contents" ng-show="row.entity.type == 1"><p>'+$filter('translate')('USERS.LIST.HUMAN')+'</p></div>' +
		'<div class="ui-grid-cell-contents" ng-show="row.entity.type == 2"><p>'+$filter('translate')('USERS.LIST.SYSTEM')+'</p></div>';

	var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
	if($scope.readAccess)
		cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showActivityLog(row.entity)"><i title="'+$filter('translate')('USERS.LIST.LOGS')+'" class="fa fa-area-chart"></i></a></strong> ';
	if($scope.writeAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editUser(row.entity)"><i title="'+$filter('translate')('USERS.LIST.EDIT')+'" class="fa fa-pencil"></i></a></strong> ';
	else
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editUser(row.entity)"><i title="'+$filter('translate')('USERS.LIST.VIEW')+'" class="fa fa-eye"></i></a></strong> ';
	if($scope.deleteAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteUser(row.entity)"><i title="'+$filter('translate')('USERS.LIST.DELETE')+'" class="fa fa-trash" ></i></a></strong>';
	cellTemplateOperations += '</div>';

	// Table options for user
	$scope.gridOptions = {
		data: 'userList',
		columnDefs: [
			{ field: 'username', displayName: $filter('translate')('USERS.LIST.USERNAME'), width: '35%', cellTemplate: cellTemplateName, enableColumnMenu: false },
			{
				field: 'type', displayName: $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_LIST.STATUS'), enableColumnMenu: false, cellTemplate: cellTemplateFinal, width: '15%', filter: {
					type: uiGridConstants.filter.SELECT,
					selectOptions: [{ value: '1', label: $filter('translate')('USERS.LIST.HUMAN') }, { value: '2', label: $filter('translate')('USERS.LIST.SYSTEM') }]
				}
			},
			{ field: 'name_'+ Session.retrieveObject('user').language, displayName: $filter('translate')('USERS.LIST.ROLE'), width: '35%', enableColumnMenu: false },
			{ name: $filter('translate')('USERS.LIST.OPERATIONS'), cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '15%', enableColumnMenu: false }
		],
		enableColumnResizing: true,
		enableFiltering: true,
		onRegisterApi: function (gridApi) {
			$scope.gridApi = gridApi;
			$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
		},
	};

	// user table search textbox param
	$scope.filterOptions = function (renderableRows) {
		var matcher = new RegExp($scope.filterValue, 'i');
		renderableRows.forEach(function (row) {
			var match = false;
			['username', 'role_display'].forEach(function (field) {
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

		var templateUrl = ($scope.writeAccess ? 'templates/user/edit.user.html' : 'templates/user/view.user.ad.html');
		var controller = 'user.edit';

		if ($scope.configs.login.activeDirectory.enabled === 1) {
			templateUrl = ($scope.writeAccess ? 'templates/user/edit.user.ad.html' : 'templates/user/view.user.ad.html');
			controller = 'user.edit.ad';
		}

		var modalInstance = $uibModal.open({
			templateUrl: templateUrl,
			controller: controller,
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
		userCollectionService.getUsers(OAUserId).then(function (response) {
			$scope.userList = response.data;
			response.data.forEach(function(row) {
				switch (row.role) {
				case "admin":
					row.role_display = $filter('translate')('USERS.ADD.ADMIN');
					break;
				case "clinician":
					row.role_display = $filter('translate')('USERS.ADD.CLINICIAN');
					break;
				case "editor":
					row.role_display = $filter('translate')('USERS.ADD.EDITOR');
					break;
				case "education-creator":
					row.role_display = $filter('translate')('USERS.ADD.EDUCATION_CREATOR');
					break;
				case "guest":
					row.role_display = $filter('translate')('USERS.ADD.GUEST');
					break;
				case "manager":
					row.role_display = $filter('translate')('USERS.ADD.MANAGER');
					break;
				case "registrant":
					row.role_display = $filter('translate')('USERS.ADD.REGISTRANT');
					break;
				default:
					row.role_display = $filter('translate')('USERS.ADD.NOT_TRANSLATED');
				}
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('USERS.LIST.ERROR_USERS'));
		});
	}
});

