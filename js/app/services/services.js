// Angular Service
// 

angular.module('opalAdmin.services', [])

	.service('Session', function ($cookies, $rootScope) {
		this.create = function (sessionId, userId, userRole) {
			$cookies.put('sessionId', sessionId);
			$cookies.put('userId', userId);
			$cookies.put('userRole', userRole);
		};
		this.retrieve = function (data) {
			return $cookies.get(data);
		};
		this.destroy = function () {
			$cookies.remove('sessionId');
			$cookies.remove('userId');
			$cookies.remove('userRole');

			$rootScope.destroyCurrentUser();
		};
	})

	.service('loginModal', function ($uibModal) {
		return function () {
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/login-form.html',
				controller: 'loginModalController',
				backdrop: 'static',
			});

			return modalInstance.result.then(function() {})
		}

	})

	.service('LogoutService', function (Session, $state) {
		this.logout = function () {
			Session.destroy();
			$state.go('login');
		}
	});