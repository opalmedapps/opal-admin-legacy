angular.module('opalAdmin.controllers.newUserController', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	* Controller for user registration
	*******************************************************************************/
	controller('newUserController', function ($scope, userCollectionService, $state) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// default booleans
		$scope.password = {open:false, show:false};
		$scope.role = {open:false, show:false};

		// completed registration steps in object notation
		var steps = {
			username: { completed: false },
			password: { completed: false },
			role: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 3;

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

		// Initialize new user object
		$scope.newUser = {
			username: null,
			password: null,
			confirmPassword: null,
			role: null
		};

		// Call our API service to get the list of possible roles
		$scope.roles = [];
		userCollectionService.getRoles().then(function (response) {
			$scope.roles = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting roles:', response.status, response.data);
		});

		// Function to validate username 
		$scope.validUsername = { status: null, message: null };
		$scope.validateUsername = function (username) {

			if (!username) {
				$scope.validUsername.status = null;
				$scope.usernameUpdate();
				return;
			}

			// Make request to check if username already in use
			userCollectionService.usernameAlreadyInUse(username).then(function (response) {
				if (response.data == 'TRUE') {
					$scope.validUsername.status = 'warning';
					$scope.validUsername.message = 'Username already in use';
					$scope.usernameUpdate();
					return;
				} else if (response.data == 'FALSE') {
					$scope.validUsername.status = 'valid';
					$scope.validUsername.message = null;
					$scope.usernameUpdate();
					return;
				} else {
					$scope.validUsername.status = 'invalid';
					$scope.validUsername.message = 'Something went wrong';
					$scope.usernameUpdate();
				}
			}).catch(function(response) {
				console.error('Error occurred verifying username:', response.status, response.data);
			});

		};

		// Function to validate password 
		$scope.validPassword = { status: null, message: null };
		$scope.validatePassword = function (password) {

			if (!password) {
				$scope.validPassword.status = null;
				$scope.passwordUpdate();
				return;
			}

			if (password.length < 6) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = 'Use greater than 6 characters';
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

			if (!confirmPassword) {
				$scope.validConfirmPassword.status = null;
				$scope.passwordUpdate();
				return;
			}

			if ($scope.validPassword.status != 'valid' || $scope.newUser.password != $scope.newUser.confirmPassword) {
				$scope.validConfirmPassword.status = 'invalid';
				$scope.validConfirmPassword.message = 'Enter same valid password';
				$scope.passwordUpdate();
				return;
			} else {
				$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
				$scope.passwordUpdate();
			}
		};

		// Function to toggle steps when updating the username field
		$scope.usernameUpdate = function () {
			if ($scope.validUsername.status == 'valid') {
				steps.username.completed = true;
				$scope.password.show = true;
			}
			else
				steps.username.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle steps when updating the password field
		$scope.passwordUpdate = function () {
			if ($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid') {
				steps.password.completed = true;
				$scope.role.show = true;
			}
			else
				steps.password.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle steps when updating the role field
		$scope.roleUpdate = function () {
			$scope.role.open = true;
			if ($scope.newUser.role)
				steps.role.completed = true;
			else
				steps.role.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};

		// Function to check registration form completion 
		$scope.checkRegistrationForm = function () {

			if ($scope.stepProgress == 100)
				return true;
			else
				return false;
		};

		// Function to register user
		$scope.registerUser = function () {

			if ($scope.checkRegistrationForm()) {

				// submit form
				$.ajax({
					type: "POST",
					url: 'php/user/insert.user.php',
					data: $scope.newUser,
					success: function () {
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

	});

