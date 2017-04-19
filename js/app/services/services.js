// Angular Service
// 

angular.module('opalAdmin.services', [])

	.service('Session', function ($cookies) {
		this.create = function (session_id, user) {
			$cookies.put('session_id', session_id);
			$cookies.putObject('user', user);
		};
		this.retrieve = function (data) {
			return $cookies.get(data);
		};
		this.retrieveObject = function (data) {
			return $cookies.getObject(data);
		};
		this.destroy = function () {
			$cookies.remove('session_id');
			$cookies.remove('user');
		};
	})

	.service('loginModal', function ($uibModal) {
		return function () {
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/login-form.html',
				controller: 'loginModalController',
				backdrop: 'static',
			});

			return modalInstance.result.then(function() {});
		};

	})

	.service('LogoutService', function (Session, $state) {
		this.logout = function () {
			Session.destroy();
			$state.go('login');
		};
	});