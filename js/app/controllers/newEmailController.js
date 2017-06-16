angular.module('opalAdmin.controllers.newEmailController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'textAngular']).

	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).

	/******************************************************************************
	* Add Email Template Page controller 
	*******************************************************************************/
	controller('newEmailController', function ($scope, $filter, $state, $sce, $uibModal, emailAPIservice) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// default boolean
		$scope.type = {open: false, show: true};
		$scope.title = {open: false, show: false};
		$scope.body = {open: false, show: false};

		// completed steps boolean object; used for progress bar
		var steps = {
			title: { completed: false },
			body: { completed: false },
			type: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 3;

		// Progress for progress bar on default steps and total
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

		// Initialize the new email object
		$scope.newEmail = {
			subject_EN: "",
			subject_FR: "",
			body_EN: "",
			body_FR: "",
			type: ""
		};

		// Call our API to get the list of email types 
		$scope.emailTypes = [];
		emailAPIservice.getEmailTypes().success(function (response) {
			$scope.emailTypes = response;
		});

		// Function to toggle necessary changes when updating titles
		$scope.titleUpdate = function () {

			$scope.title.open = true;

			if ($scope.newEmail.subject_EN && $scope.newEmail.subject_FR) {

				$scope.body.show = true;
				// Toggle step completion
				steps.title.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle step completion
				steps.title.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating the email body
		$scope.bodyUpdate = function () {
			
			$scope.body.open = true;

			if ($scope.newEmail.body_EN && $scope.newEmail.body_FR) {
				// Toggle boolean
				steps.body.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle boolean
				steps.body.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating the post type
		$scope.typeUpdate = function (type) {

			$scope.newEmail.type = type;

			$scope.type.open = true;
			$scope.title.show = true;

			// Toggle boolean
			steps.type.completed = true;

			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to submit the new email
		$scope.submitEmailTemplate = function () {
			if ($scope.checkForm()) {

				// Submit 
				$.ajax({
					type: "POST",
					url: "php/email/insert_email.php",
					data: $scope.newEmail,
					success: function () {
						$state.go('email');
					}
				});
			}
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {
			if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
				return true;
			else
				return false;
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