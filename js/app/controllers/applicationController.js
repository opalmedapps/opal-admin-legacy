angular.module('opalAdmin.controllers.applicationController', ['ui.bootstrap', 'ngIdle', 'pascalprecht.translate']).


	/******************************************************************************
	* Top level application controller
	*******************************************************************************/
	controller('applicationController', function ($scope, $rootScope, $state, Idle, Keepalive,
		$uibModal, Session, loginModal, AUTH_EVENTS, USER_ROLES, AuthService, $translate) {

		// Set current user 
		$rootScope.currentUser = null;

		$rootScope.siteLanguage = null;

		$rootScope.devMode = DEV_MODE; // constant fron config.js
		
		// Set the site language
		$rootScope.setSiteLanguage = function (user) {
			$rootScope.siteLanguage = user.language;
			$translate.use(user.language.toLowerCase());
		};

		$scope.userRoles = USER_ROLES;
		$scope.isAuthorized = AuthService.isAuthorized;

		// Function to close idle modal
		function closeIdleModal() {
			if ($scope.warning) {
				$scope.warning.close();
				$scope.warning = null;
			}
		}

		$scope.inAuthLoginModal = false;

		var pagesToIgnore = ['login', 'install'];

		// Trigger on idle start
		$scope.$on('IdleStart', function () {

			if ((pagesToIgnore.indexOf($state.current.name) === -1) && !$scope.inAuthLoginModal) {
				$scope.warning = $uibModal.open({
					templateUrl: 'templates/idle-warning-modal.html',
					windowClass: 'modal-danger'
				});
			}
		});

		// Close idle modal on idle end
		$scope.$on('IdleEnd', function () {
			closeIdleModal();
		});

		// Trigger on idle timeout
		$scope.$on('IdleTimeout', function () {
			closeIdleModal(); // close idle modal

			Session.destroy(); // destroy session 

			if ((pagesToIgnore.indexOf($state.current.name) === -1) && !$scope.inAuthLoginModal) {
				loginModal() // open login modal
					.then(function () {
						$scope.startIdleWatch(); // Start idle watch again
					})
					.catch(function () {
						return $state.go('login'); // Failed go to login
					});
			}
		});

		// Trigger on non-authentication
		$scope.$on(AUTH_EVENTS.notAuthorized, function () {
			$scope.warning = $uibModal.open({
				templateUrl: 'templates/authorization-warning-modal.html',
				windowClass: 'modal-danger'
			});

		});

		// Trigger on non-authentication
		$scope.$on(AUTH_EVENTS.notAuthenticated, function () {
			var currentState = $state.current.name;
			if (currentState != 'login') {
				$scope.inAuthLoginModal = true;
				loginModal() // open login modal
					.then(function () {
						$scope.startIdleWatch(); // Start idle watch again
						$scope.inAuthLoginModal = false;
						return $state.go('home'); // Go to home page
					})
					.catch(function () {
						$scope.inAuthLoginModal = false;
						return $state.go('login'); // Failed go to login
					});
			}
		});

		$scope.startIdleWatch = function () {
			closeIdleModal();
			Idle.watch();
		};


	})

	// Configs for setting idle and keep alive (in seconds)
	.config(function (IdleProvider, KeepaliveProvider) {
		IdleProvider.idle(360);
		IdleProvider.timeout(15);
		KeepaliveProvider.interval(375);
	});



