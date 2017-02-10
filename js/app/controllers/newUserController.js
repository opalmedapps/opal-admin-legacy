angular.module('opalAdmin.controllers.newUserController', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	* Controller for user registration
	*******************************************************************************/
	controller('newUserController', function($scope, userAPIservice, $state) {

		// Function to go to previous page
        $scope.goBack = function() {
            window.history.back();
        }

        // completed registration steps in object notation
        var steps = {
           	username: {completed: false},
            password: {completed: false},
            role: {completed: false}
        }

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
                if (steps[step].completed == true) {
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
        }

        // Call our API service to get the list of possible roles
        $scope.roles = [];
        userAPIservice.getRoles().success(function(response) {
            $scope.roles = response;
        });

        // Function to validate username 
        $scope.validUsername = {status:null,message:null};
        $scope.validateUsername = function (username) {

        	if (!username) {
        		$scope.validUsername.status = null;
        		$scope.usernameUpdate();
        		return;
        	}

        	// Make request to check if username already in use
        	userAPIservice.usernameAlreadyInUse(username).success(function(response) {
        		if(response == 'TRUE') {
        			$scope.validUsername.status = 'warning';
        			$scope.validUsername.message = 'Username aleady in use';
        			$scope.usernameUpdate();
        			return;
        		} else if (response == 'FALSE') {
        			$scope.validUsername.status = 'valid';
        			$scope.validUsername.message = null;
        			$scope.usernameUpdate();
        			return;
        		} else {
        			$scope.validUsername.status = 'invalid';
        			$scope.validUsername.message = 'Something went wrong';
        			$scope.usernameUpdate();
        		}
        	});

        }

        // Function to validate password 
        $scope.validPassword = {status:null,message:null};
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
        }

        // Function to validate confirm password
        $scope.validConfirmPassword = {status:null,message:null};
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
        }
			
		// Function to toggle steps when updating the username field
		$scope.usernameUpdate = function () {
			if($scope.validUsername.status == 'valid')
				steps.username.completed = true;
			else
				steps.username.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}	

		// Function to toggle steps when updating the password field
        $scope.passwordUpdate = function() {
            if($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid')
                steps.password.completed = true;
            else
                steps.password.completed = false;
            
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }	

        // Function to toggle steps when updating the role field
        $scope.roleUpdate = function () {
            if($scope.newUser.role)
                steps.role.completed = true;
            else 
                steps.role.completed = false;

            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        
        }

		// Function to check registration form completion 
        $scope.checkRegistrationForm = function() {

            if($scope.stepProgress == 100) 
                return true
            else
                return false
        }

        // Function to register user
        $scope.registerUser = function() {

        	if($scope.checkRegistrationForm()) {

        		// submit form
        		$.ajax({
        			type: "POST",
        			url: 'php/user/register_user.php',
        			data: $scope.newUser,
        			success: function () {
        				$state.go('users');
        			}
        		});
        	}
        }

	});

