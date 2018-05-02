angular.module('opalAdmin.controllers.user.add', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	* Controller for user registration
	*******************************************************************************/
	controller('user.add', function ($scope, userCollectionService, $state, Encrypt) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// default booleans
		$scope.passwordSection = {open:false, show:false};
		$scope.roleSection = {open:false, show:false};
		$scope.languageSection = {open:false, show:false};

		// Initialize a list of languages available
		$scope.languages = [{
			name: 'English',
			id: 'EN'
		}, {
			name: 'French',
			id: 'FR'
		}];

		// completed registration steps in object notation
		var steps = {
			username: { completed: false },
			password: { completed: false },
			role: { completed: false },
			language: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
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

		// Initialize new user object
		$scope.newUser = {
			username: null,
			password: null,
			confirmPassword: null,
			role: null,
			language: null
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
				$scope.passwordSection.show = true;
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
				$scope.languageSection.show = true;
			}
			else
				steps.role.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};

		// Function to toggle steps when updating the language field
		$scope.languageUpdate = function () {
			$scope.languageSection.open = true;
			if ($scope.newUser.language)
				steps.language.completed = true;
			else
				steps.language.completed = false;

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

				// duplicate new user
				var user = jQuery.extend(true, {}, $scope.newUser);
				// one-time pad using current time and rng
				var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103; 
				// encode passwords before request
				user.password = Encrypt.encode(user.password, cypher);
				user.confirmPassword = Encrypt.encode(user.confirmPassword, cypher);
				user.cypher = cypher;
				// submit form
				$.ajax({
					type: "POST",
					url: 'php/user/insert.user.php',
					data: user,
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

