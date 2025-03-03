// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.user.add', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	 * Controller for user registration
	 *******************************************************************************/
	controller('user.add', function ($scope, $rootScope, userCollectionService, $state, $filter, $timeout, Session, ErrorHandler) {
		var OAUserId = Session.retrieveObject('user').id;

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.userType = [
			{
				ID: "1",
				name_display: $filter('translate')('USERS.ADD.HUMAN')
			},
			{
				ID: "2",
				name_display: $filter('translate')('USERS.ADD.SYSTEM')
			},
		];

		// Initialize new user object
		$scope.newUser = {
			type: $scope.userType[0].ID,
			username: null,
			password: null,
			confirmPassword: null,
			role: null,
			role_display: null,
			additionalprivileges: null,
			language: null,
			language_display: null
		};

		// Function to check whether a password is required for the user
		function isPasswordRequired() {
			// if AD is enabled the password is only not required if it is a human user
			return !($rootScope.isADEnabled && $scope.newUser.type === '1');
		}

		// default booleans
		// isPasswordRequired is updated in the watch function for newUser.type
		$scope.isPasswordRequired = true;
		$scope.passwordSection = {open:false, show:false};
		$scope.roleSection = {open:false, show:false};
		$scope.languageSection = {open:false, show:false};
		$scope.language = Session.retrieveObject('user').language;

		$scope.type_name = $scope.userType[0].name_display;

		// Initialize a list of languages available
		$scope.languages = [{
			name: $filter('translate')('USERS.ADD.ENGLISH'),
			id: 'EN'
		}, {
			name: $filter('translate')('USERS.ADD.FRENCH'),
			id: 'FR'
		}];

		// completed registration steps in object notation
		var steps = {
			username: { completed: false },
			password: { completed: false },
			role: { completed: false },
			language: { completed: false },
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps
		// adjusted in the watch function for newUser.type
		$scope.stepTotal = 4;

		// Progress bar based on default completed steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Function to calculate / return step progress
		function trackProgress(value, total) {
			return Math.round(100 * value / total);
		}

		// Function to return number of steps completed
		function stepsCompleted(steps) {

			var numberOfTrues = 0;
			for (var step in steps) {
				if (steps[step].completed === true) {
					numberOfTrues++;
				}
			}

			return numberOfTrues;
		}

		// Function to check if user is already in use
		function checkUserAlreadyInUse(username){
			userCollectionService.usernameAlreadyInUse(username).then(function (response) {
					if (response.data.count) {
						// Handle the case where the username is already in use
						$scope.validUsername.status = 'invalid';
						$scope.validUsername.message = $filter('translate')('USERS.ADD.ERROR_USERNAME_USED');
						$scope.usernameUpdate();
					} else {
						// Handle the case where the username is not in use
						$scope.validUsername.status = 'valid';
						$scope.validUsername.message = null;
						$scope.usernameUpdate();
					}
				});
		}

		// Call our API service to get the list of possible roles
		$scope.roles = [];
		userCollectionService.getRoles(OAUserId).then(function (response) {
			response.data.forEach(function(row) {
				if($scope.language.toUpperCase() === "FR")
					row.name_display = row.name_FR;
				else
					row.name_display = row.name_EN;
			});
			$scope.roles = response.data;
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('USERS.ADD.ERROR_ROLES'));
		});

		// Call our API service to get the list of possible additional privileges
		$scope.additionalprivileges = [];
		userCollectionService.getAdditionalPrivileges().then(
			function(response){
				$scope.additionalprivileges = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('USERS.ADD.ERROR_ADDITIONAL_PRIVILEGES'));
			});

		// Function to validate username
		$scope.validUsername = { status: null, message: null };
		$scope.validateUsername = function (username) {

			if (!username) {
				$scope.validUsername.status = null;
				$scope.usernameUpdate();
				return;
			}

			// if AD is enabled
			if(!$scope.isPasswordRequired) {
				// check if user exists as an ADFS user
				userCollectionService.isUserExist(username).then(function (response) {
					// disable password section
					$scope.disablePassword = true;
					// if ADFS user exists
					if (response.data.is_exist) {
						// check if user is already added to opaladmin database
						 checkUserAlreadyInUse(username);

					} else { //if ADFS user does not exist
						// Handle the case where the username doesn't exist in AD (response.data.is_exist is false)
						$scope.validUsername.status = 'invalid';
						$scope.validUsername.message = $filter('translate')('USERS.ADD.ERROR_AD_USERNAME_NOT_EXIST');
						$scope.usernameUpdate();
					}
				}).catch(function (error) {
					// Handle communication errors for request
					alert("An error occurred in fedauth request", error);
				});
			}else{// if AD is disabled check only opaladmin database
				checkUserAlreadyInUse(username);
			}
		};

		// Function to validate password
		$scope.validPassword = { status: null, message: null };
		$scope.validatePassword = function (password) {

			if (!password) {
				$scope.validPassword.status = null;
				$scope.passwordUpdate();
				return;
			}

			//Password validation
			//minimum 12 characters, 1 number, 1 lower case letter, 1 upper case letter and 1 special character
			var validationPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{12,}$/;
			if(!password.match(validationPassword)) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = $filter('translate')('USERS.ADD.ERROR_PASSWORD_FORMAT');
				$scope.passwordUpdate();
				return;
			} else {
				$scope.validPassword.status = 'valid';
				$scope.validPassword.message = null;
				$scope.passwordUpdate();
			}
		};

		// Function to validate confirm password
		$scope.validConfirmPassword = { status: null, message: null };
		$scope.validateConfirmPassword = function (confirmPassword) {
			$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
				$scope.passwordUpdate();
			if (!confirmPassword) {
				$scope.validConfirmPassword.status = null;
				$scope.passwordUpdate();
				return;
			}

			if ($scope.validPassword.status != 'valid' || $scope.newUser.password != $scope.newUser.confirmPassword) {
				$scope.validConfirmPassword.status = 'invalid';
				$scope.validConfirmPassword.message = $filter('translate')('USERS.ADD.ERROR_PASSWORD_INVALID');
				$scope.passwordUpdate();
				return;
			} else {
				$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
				$scope.passwordUpdate();
			}
		};

		$scope.$watch('newUser.type', function() {
			if($scope.newUser.type !== "1") {
				alert($filter('translate')('USERS.ADD.WARNING_USER'));
				$scope.type_name = $scope.userType[1].name_display;
			} else {
				$scope.type_name = $scope.userType[0].name_display;
			}

			$timeout(() => {
				$scope.isPasswordRequired = isPasswordRequired();
				$scope.stepTotal = $scope.isPasswordRequired ? 4 : 3;
			});
		});

		// Function to toggle steps when updating the username field
		$scope.usernameUpdate = function () {
			if ($scope.validUsername.status == 'valid') {
				steps.username.completed = true;
				$scope.roleSection.show = true;
			}
			else {
				$scope.roleSection.show = false;
				steps.username.completed = false;
			}

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle steps when updating the password field
		$scope.passwordUpdate = function () {
			if ($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid') {
				steps.password.completed = true;
				$scope.roleSection.show = true;
			}
			else
				steps.password.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle steps when updating the role field
		$scope.roleUpdate = function () {
			$scope.roleSection.open = true;
			if ($scope.newUser.role) {
				steps.role.completed = true;
				$scope.newUser.role_display = $scope.newUser.role.name_display;
				$scope.languageSection.show = true;
			}
			else
				steps.role.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};

		// Function to toggle steps when updating the additional privileges field
		$scope.additionalPrivilegesUpdate = function () {
			$scope.roleSection.open = true;
			if ($scope.newUser.additionalprivileges.length == 0) {
				$scope.newUser.additionalprivileges =null;
			}

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle steps when updating the language field
		$scope.languageUpdate = function () {
			$scope.languageSection.open = true;
			if ($scope.newUser.language) {
				steps.language.completed = true;
				$scope.newUser.language_display = ($scope.newUser.language === "FR"?$filter('translate')('USERS.ADD.FRENCH'):$filter('translate')('USERS.ADD.ENGLISH'));
			}
			else
				steps.language.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};

		// Function to check registration form completion
		$scope.checkRegistrationForm = function () {

			if ($scope.stepProgress === 100)
				return true;
			else
				return false;
		};

		// Function to register user
		$scope.registerUser = function () {
			if ($scope.checkRegistrationForm()) {
				// set the additional privileges to the form `{'groups': [group_1.pk,...]}`
				var additionalprivileges_map = {'groups':[]};
				if ($scope.newUser.additionalprivileges && $scope.newUser.additionalprivileges.length > 0) {
					for (const item in $scope.newUser.additionalprivileges) {
						additionalprivileges_map.groups.push($scope.newUser.additionalprivileges[item].pk);
					}
				}
				$scope.newUser.additionalprivileges_map = additionalprivileges_map;
				var data = {
					OAUserId: Session.retrieveObject('user').id,
					type: $scope.newUser.type,
					username: $scope.newUser.username,
					password: $scope.newUser.password,
					confirmPassword: $scope.newUser.confirmPassword,
					language: $scope.newUser.language,
					roleId: $scope.newUser.role.ID,
					additionalprivileges: $scope.newUser.additionalprivileges_map,
				};
				$.ajax({
					type: "POST",
					url: 'user/insert/user',
					data: data,
					success: function () {},
					error: function(err) {
						ErrorHandler.onError(err, $filter('translate')('USERS.ADD.ERROR'));
					},
					complete: function() {
						$state.go('users');
					}
				});
			}
		};

		var fixmeTop = $('.summary-fix').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixmeTop) {
				$('.summary-fix').css({
					position: 'fixed',
					top: '0',
					width: '15%'
				});
			} else {
				$('.summary-fix').css({
					position: 'static',
					width: ''
				});
			}
		});

		var fixMeMobile = $('.mobile-side-panel-menu').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixMeMobile) {
				$('.mobile-side-panel-menu').css({
					position: 'fixed',
					top: '50px',
					width: '100%',
					zIndex: '100',
					background: '#6f5499',
					boxShadow: 'rgba(93, 93, 93, 0.6) 0px 3px 8px -3px'

				});
				$('.mobile-summary .summary-title').css({
					color: 'white'
				});
			} else {
				$('.mobile-side-panel-menu').css({
					position: 'static',
					width: '',
					background: '',
					boxShadow: ''
				});
				$('.mobile-summary .summary-title').css({
					color: '#6f5499'
				});
			}
		});
	});

