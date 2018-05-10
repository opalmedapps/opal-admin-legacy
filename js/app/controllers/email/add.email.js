angular.module('opalAdmin.controllers.email.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'textAngular']).

	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).

	/******************************************************************************
	* Add Email Template Page controller 
	*******************************************************************************/
	controller('email.add', function ($scope, $filter, $state, $sce, $uibModal, emailCollectionService, Session) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// default boolean
		$scope.typeSection = { open: false, show: true };
		$scope.titleSection = { open: false, show: false };
		$scope.bodySection = { open: false, show: false };

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
		emailCollectionService.getEmailTypes().then(function (response) {
			$scope.emailTypes = response.data;
		}).catch(function (response) {
			console.error('Error occurred getting email types:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating titles
		$scope.titleUpdate = function () {

			$scope.titleSection.open = true;

			if ($scope.newEmail.subject_EN && $scope.newEmail.subject_FR) {

				$scope.bodySection.show = true;
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

			$scope.bodySection.open = true;

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

			$scope.typeSection.open = true;
			$scope.titleSection.show = true;

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

				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.newEmail.body_EN = $scope.newEmail.body_EN.replace(/\u200B/g,'');
				$scope.newEmail.body_FR = $scope.newEmail.body_FR.replace(/\u200B/g,'');

				// Log who created email
				var currentUser = Session.retrieveObject('user');
				$scope.newEmail.user = currentUser;
				// Submit 
				$.ajax({
					type: "POST",
					url: "php/email/insert.email.php",
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
		$(window).scroll(function () {
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
		$(window).scroll(function () {
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