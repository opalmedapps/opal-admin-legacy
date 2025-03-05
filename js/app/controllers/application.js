// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.application', ['ui.bootstrap', 'ngIdle', 'pascalprecht.translate']).


	/******************************************************************************
	 * Top level application controller
	 *******************************************************************************/
	controller('application', function ($scope, $rootScope, $state, $filter, Idle, Keepalive,
		$uibModal, Session, loginModal, AUTH_EVENTS, USER_ROLES, AuthService, $translate,
		applicationCollectionService, LogoutService) {

		// Set current user
		$rootScope.currentUser = Session.retrieveObject('user');

		$rootScope.siteLanguage = null;

		$rootScope.newOpalAdminHost = null;
		$rootScope.ormsHost = null;
		$rootScope.isADEnabled = false;

		$scope.configs = null;
		$scope.sourceDatabases = null;

		$scope.seconds = $filter('translate')('PROFILE.SECONDS');
		$scope.second = $filter('translate')('PROFILE.SECOND');

		// Call our collection service to get configs
		applicationCollectionService.getConfigs().then(function (response) {
			$scope.configs = response.data;
			$rootScope.newOpalAdminHost = $scope.configs.newOpalAdminHost;
			$rootScope.ormsHost = $scope.configs.ormsHost;
			$rootScope.isADEnabled = $scope.configs.login.activeDirectory.enabled === '1';

			// Check whether the user is logged in and coming from ORMS
			if ($rootScope.currentUser && document.referrer) {
				if ($rootScope.ormsHost && $rootScope.ormsHost.startsWith(document.referrer)) {
					// Check if the user only has access to ORMS (Clinician Dashboard)
					const userAccess = Session.retrieveObject('access');
					const countAccess = userAccess.filter(x => x >= 1).length;
					// the Clinician Dashboard module ID is 25
					const ormsAccess = userAccess[25];


					if (countAccess == 1 && ormsAccess >= 1) {
						return LogoutService.logout();
					}

				}
			}

			// Call our collection service to get enabled flags in the source database table
			var updateNeeded = false;
			applicationCollectionService.getSourceDatabases().then(function(response) {
				// Assign value
				$scope.sourceDatabases = response.data;

				// Loop keys (i.e. source databases) and compare enabled flag
				for (let sourceDatabase in $scope.sourceDatabases) {
					if ($scope.sourceDatabases.hasOwnProperty(sourceDatabase)) {
						if (typeof($scope.configs.databaseConfig[sourceDatabase]) !== "undefined" && $scope.sourceDatabases[sourceDatabase].enabled != $scope.configs.databaseConfig[sourceDatabase].enabled) {
							$scope.sourceDatabases[sourceDatabase].update = 1;
							$scope.sourceDatabases[sourceDatabase].enabled = $scope.configs.databaseConfig[sourceDatabase].enabled;
							updateNeeded = true;
						}
					}
				}

				// Update enabled flags in our database if needed
				if (updateNeeded) {
					$.ajax({
						type: "POST",
						url: "application/update/source-databases",
						data: $scope.sourceDatabases,
						dataType: 'json',
						success: function (response) {
							console.log("Updated source databases");
						}
					});
				}
			}).catch(function(response) {
				alert('Error occurred getting source databases: ' + response.status + " " + response.data)
				console.error('Error occurred getting source databases: ', response.status, response.data);
			});
		}).catch(function(response) {
			alert('Error occurred getting source databases: ' + response.status + " " + response.data)
			console.error('Error occurred getting configs: ', response.status, response.data);
		});



		// Set the site language
		$rootScope.setSiteLanguage = function (user) {
			if (!user)
				$rootScope.siteLanguage = 'FR';
			else {
				$rootScope.siteLanguage = user.language;
			}
			$translate.use($rootScope.siteLanguage.toLowerCase());
		};
		$rootScope.setSiteLanguage($rootScope.currentUser);

		$scope.isAuthenticated = AuthService.isAuthenticated;

		// Function to close idle modal
		function closeIdleModal() {
			if ($scope.warning) {
				$scope.warning.close();
				$scope.warning = null;
			}
		}

		$scope.build = null;
		// Call our collection service to get the appliciaton build type
		applicationCollectionService.getApplicationBuild().then(function (response) {
			// Assign value
			$scope.build = response.data;

		}).catch(function(response) {
			console.error('Error occured getting application build: ', response.status, response.data);
		});

		$scope.inAuthLoginModal = false;

		$scope.isIndexPage = () => $state.current.name === 'login';

		var pagesToIgnore = ['login'];

		// Trigger on idle start
		$scope.$on('IdleStart', function () {
			if ($scope.isAuthenticated() && (pagesToIgnore.indexOf($state.current.name) === -1) && !$scope.inAuthLoginModal) {
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

			let isAuthenticated = $scope.isAuthenticated();
			LogoutService.logLogout(); // send logout report to backend
			Session.destroy(); // destroy session
			if (isAuthenticated && (pagesToIgnore.indexOf($state.current.name) === -1) && !$scope.inAuthLoginModal) {
				$scope.inAuthLoginModal = true;
				loginModal() // open login modal
					.then(function () {
						$scope.startIdleWatch(); // Start idle watch again
					})
					.catch(function () {
						return $state.go('login'); // Failed go to login
					})
					.finally(function () {
						$scope.inAuthLoginModal = false;
					});
			}
		});

		// Trigger on non-authentication
		$scope.$on(AUTH_EVENTS.notAuthorized, function () {
			$scope.warning = $uibModal.open({
				templateUrl: 'templates/error-handler/access.denied.html',
				backdrop: 'static',
				controller: 'errorHandler.accessDenied',
			});

		});

		// Trigger on non-authentication
		$scope.$on(AUTH_EVENTS.notAuthenticated, function () {
			if ((pagesToIgnore.indexOf($state.current.name) === -1) && !$scope.inAuthLoginModal) {
				$scope.inAuthLoginModal = true;
				loginModal() // open login modal
					.then(function () {
						$scope.startIdleWatch(); // Start idle watch again
						return $state.go('home'); // Go to home page
					})
					.catch(function () {
						return $state.go('login'); // Failed go to login
					})
					.finally(function () {
						$scope.inAuthLoginModal = false;
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
