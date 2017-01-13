angular.module('ATO_InterfaceApp.controllers.applicationController', ['ui.bootstrap']).


	/******************************************************************************
	* Top level controller
	*******************************************************************************/
	controller('applicationController', function($scope, USER_ROLES, AuthService) {


		$scope.currentUser = null;
		$scope.userRoles = USER_ROLES;
		$scope.isAuthorized = AuthService.isAuthorized;

		$scope.setCurrentUser = function (user) {
			$scope.currentUser = user;
		}

		$scope.isLoginPage = false;
	
					
	}).

	directive('loginDialog', function (AUTH_EVENTS) {
		return {
			restrict: 'A',
			template: '<div ng-if="visible" ng-include="\'login-form.html\'">',
			link: function (scope) {
				var showDialog = function () {
					scope.visible = true;
				};

				scope.visible = false;
				scope.$on(AUTH_EVENTS.notAuthenticated, showDialog);
				scope.$on(AUTH_EVENTS.sessionTimeout, showDialog)
			}
		};
	});

