angular.module('opalAdmin.controllers.application', ['ui.bootstrap', 'ngIdle', 'pascalprecht.translate']).


	/******************************************************************************
	* Top level application controller
	*******************************************************************************/
	controller('application', function ($scope, $rootScope, $state, Idle, Keepalive,
		$uibModal, Session, loginModal, AUTH_EVENTS, USER_ROLES, AuthService, $translate, 
		applicationCollectionService, LogoutService) {

		// Set current user 
		$rootScope.currentUser = Session.retrieveObject('user');

		$rootScope.siteLanguage = null;

		// Set the site language
		$rootScope.setSiteLanguage = function (user) {
			if (!user) 
				$rootScope.siteLanguage = 'EN';
			else {
				$rootScope.siteLanguage = user.language;
			}
			$translate.use($rootScope.siteLanguage.toLowerCase());
		};
		$rootScope.setSiteLanguage($rootScope.currentUser);

		$scope.userRoles = USER_ROLES;
		$scope.isAuthorized = AuthService.isAuthorized;
		$scope.isAuthenticated = AuthService.isAuthenticated;

		// Function to close idle modal
		function closeIdleModal() {
			if ($scope.warning) {
				$scope.warning.close();
				$scope.warning = null;
			}
		}

		$scope.build = null;
		// Call our collection service to get the applicaiton build type
		applicationCollectionService.getApplicationBuild().then(function (response) {
			// Assign value
			$scope.build = response.data;

		}).catch(function(response) {
			console.error('Error occured getting application build: ', response.status, response.data);
		});

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

			LogoutService.logLogout(); // send logout report to backend
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



