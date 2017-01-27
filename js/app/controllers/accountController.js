angular.module('opalAdmin.controllers.accountController', ['ui.bootstrap', 'ngIdle']).


	/******************************************************************************
	* Top level account controller
	*******************************************************************************/
	controller('accountController', function($scope, $rootScope, $state, Idle, Keepalive, 
		$uibModal, Session, loginModal, AUTH_EVENTS, USER_ROLES, AuthService) {


		// Store current user in rootScope
		$rootScope.currentUser = null;

		$scope.userRoles = USER_ROLES;
  		$scope.isAuthorized = AuthService.isAuthorized;

		// Top-level function for settign the current user
		$rootScope.setCurrentUser = function (user) {
			$rootScope.currentUser = user;
		}

		// Top-level function for destroying the current user
		$rootScope.destroyCurrentUser = function() {
			$rootScope.currentUser = null;
		}

 		// Function to close idle modal
 		function closeIdleModal() {
 			if ($scope.warning) {
 				$scope.warning.close();
 				$scope.warning = null;
 			}
 		}

 		$scope.inAuthLoginModal = false;

 		// Trigger on idle start
 		$scope.$on('IdleStart', function() {

 			if ($state.current.name != 'login' && !$scope.inAuthLoginModal) {
	 			$scope.warning = $uibModal.open({
	 				templateUrl: 'templates/idle-warning-modal.html',
	 				windowClass: 'modal-danger'
	 			});
	 		}
 		});

 		// Close idle modal on idle end
 		$scope.$on('IdleEnd', function() {
 			closeIdleModal();
 		});

 		// Trigger on idle timeout
 		$scope.$on('IdleTimeout', function() {
 			closeIdleModal(); // close idle modal

 			Session.destroy(); // destroy session 

 			if ($state.current.name != 'login' && !$scope.inAuthLoginModal) {
	 			loginModal() // open login modal
				.then(function () {
					$scope.startIdleWatch(); // Start idle watch again
				})
				.catch(function () {
					return $state.go('login'); // Failed go to login
				});
			}
 		});

 		// Trigger on non-authetication
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

 		$scope.startIdleWatch = function() {
 			closeIdleModal();
 			Idle.watch();
 		};


 	})

	// Configs for setting idle and keep alive (in seconds)
	.config(function(IdleProvider, KeepaliveProvider) {
		IdleProvider.idle(600);
		IdleProvider.timeout(15);
		KeepaliveProvider.interval(615);
	});
	
					
	
