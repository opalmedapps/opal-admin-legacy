// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.login', ['ngAnimate', 'ui.bootstrap']).


/******************************************************************************
 * Login controller
 *******************************************************************************/
controller('login', function ($scope, $rootScope, $state, $filter, $translate, $window, AUTH_EVENTS, HTTP_CODE, AuthService, Idle, Session, $timeout) {

	// Initialize login object
	$scope.credentials = {
		username: "",
		password: ""
	};

	$scope.loginPage = true;

	$scope.language = {
		main: "fr",
		alt: "en",
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


	// Function to return boolean on completed login form
	$scope.loginFormComplete = function () {

		if (($scope.credentials.username && $scope.credentials.password))
			return true;
		else
			return false;
	};

	$scope.changeLanguage = function () {
		var temp = $scope.language.main;
		$translate.use($scope.language.alt);
		$scope.language.main = $scope.language.alt;
		$scope.language.alt = temp;
	};

	// Function to "shake" form container if fields are incorrect
	$scope.shakeForm = function () {
		$scope.formLoaded = true;
		$('.form-box-shake').addClass('shake');
		setTimeout(function () {
			$('.form-box-shake').removeClass('shake');
		}, 1000);
	};

	$scope.submitLogin = function (credentials) {
		if ($scope.loginFormComplete()) {
			AuthService.login(credentials.username, credentials.password).then(function (response) {
				var accessLevel = [];
				angular.forEach(response.data.access, function (row) {
					accessLevel[row["ID"]] = row["access"];
				});
				response.data.access = accessLevel;
				Session.create(response.data);
				$rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
				$rootScope.currentUser = response.data.user;
				$rootScope.setSiteLanguage(response.data.user);

				// Handle users who only have access to ORMS (Clinician Dashboard)
				// If such a user logs in, redirect them directly to ORMS.
				const access = response.data.access;
				const countAccess = access.filter(x => x >= 1).length;
				// the Clinician Dashboard module ID is 25
				const ormsAccess = access[25];

				if (countAccess == 1 && ormsAccess >= 1) {
					$window.location.href = $rootScope.ormsHost;
				} else {
					$state.go('home');
					Idle.watch();
				}								
			}).catch(function(err) {
				$rootScope.$broadcast(AUTH_EVENTS.loginFailed);
				switch(err.status) {
				case HTTP_CODE.notAuthenticatedError:
					$errMsg = $filter('translate')('LOGIN.ERROR_401');
					break;
				case HTTP_CODE.forbiddenAccessError:
					$errMsg = $filter('translate')('LOGIN.ERROR_403');
					break;
				case HTTP_CODE.notFoundError:
					$errMsg = $filter('translate')('LOGIN.ERROR_404');
					break;
				case HTTP_CODE.sessionTimeoutError:
					$errMsg = $filter('translate')('LOGIN.ERROR_419');
					break;
				case HTTP_CODE.loginTimeoutError:
					$errMsg = $filter('translate')('LOGIN.ERROR_440');
					break;
				case HTTP_CODE.httpToHttpsError:
					$errMsg = $filter('translate')('LOGIN.ERROR_497');
					break;
				case HTTP_CODE.internalServerError:
					$errMsg = $filter('translate')('LOGIN.ERROR_500');
					break;
				default:
					$errMsg = $filter('translate')('LOGIN.UNKNOWN_ERROR');
				}

				$scope.bannerMessage = $errMsg;
				$scope.setBannerClass('danger');
				$timeout(function(){
					$scope.showBanner();
				});
				$scope.shakeForm();
			});
		}
	};
});

