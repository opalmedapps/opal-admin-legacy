angular.module('opalAdmin.controllers.notification.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


/******************************************************************************
 * Controller for the Add Notification page
 *******************************************************************************/
controller('notification.add', function ($scope, $uibModal, $state, $filter, $sce, notificationCollectionService, Session, ErrorHandler) {

	// Function to go to previous page
	$scope.goBack = function () {
		window.history.back();
	};
	$scope.language = Session.retrieveObject('user').language;

	// default boolean
	$scope.typeSection = {open: false, show: true};
	$scope.titleMessageSection = {open: false, show: false};

	// completed steps boolean object; used for progress bar
	var steps = {
		title_message: { completed: false },
		type: { completed: false }
	};

	// Default count of completed steps
	$scope.numOfCompletedSteps = 0;

	// Default total number of steps
	$scope.stepTotal = 2;

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

	// Initialize the new notification object
	$scope.newNotification = {
		name_EN: "",
		name_FR: "",
		description_EN: "",
		description_FR: "",
		type: ""
	};


	// Call our API to get the list of notification types
	$scope.notificationTypes = [];
	notificationCollectionService.getNotificationTypes().then(function (response) {

		response.data.forEach(function(entry) {
			switch (entry.id) {
			case "Document":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.DOCUMENT');
				break;
			case "TxTeamMessage":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.TREATMENT_TEAM');
				break;
			case "Announcement":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.ANNOUNCEMENT');
				break;
			case "EducationalMaterial":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.EDUCATION_MATERIAL');
				break;
			case "NextAppointment":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.NEXT_APPOINTMENT');
				break;
			case "AppointmentTimeChange":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.APPOINTMENT_TIME_CHANGE');
				break;
			case "NewMessage":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.NEW_MESSAGE');
				break;
			case "NewLabResult":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.NEW_LAB_RESULT');
				break;
			case "UpdDocument":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.UPDATED_DOCUMENT');
				break;
			case "RoomAssignment":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.ROOM_ASSIGNMENT');
				break;
			case "PatientsForPatients":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.PATIENTS_FOR_PATIENTS');
				break;
			case "Questionnaire":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.QUESTIONNAIRE');
				break;
			case "LegacyQuestionnaire":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.LEGACY_QUESTIONNAIRE');
				break;
			case "CheckInNotification":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.CHECKIN');
				break;
			case "CheckInError":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.CHECKINERROR');
				break;
			case "AppointmentCancelled":
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.CANCELLED');
				break;
			default:
				entry.name_display = $filter('translate')('NOTIFICATIONS.ADD.NOT_TRANSLATED');
			}
		});
		$scope.notificationTypes = response.data;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.ADD.ERROR_TYPES'));
	});

	// Function to toggle necessary changes when updating titles
	$scope.titleMessageUpdate = function () {

		$scope.titleMessageSection.open = true;

		if (!$scope.newNotification.name_EN && !$scope.newNotification.name_FR &&
			!$scope.newNotification.description_EN && !$scope.newNotification.description_FR) {
			$scope.titleMessageSection.open = false;
		}

		if ($scope.newNotification.name_EN && $scope.newNotification.name_FR &&
			$scope.newNotification.description_EN && $scope.newNotification.description_FR) {

			// Toggle step completion
			steps.title_message.completed = true;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		} else {
			// Toggle step completion
			steps.title_message.completed = false;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	// Function to toggle necessary changes when updating type
	$scope.typeUpdate = function (type) {
		$scope.newNotification.type = type;
		$scope.typeSection.open = true;

		if ($scope.newNotification.type) {
			$scope.titleMessageSection.show = true;
			steps.type.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		} else {
			steps.type.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	// Function to submit the new notification
	$scope.submitNotification = function () {
		if ($scope.checkForm()) {
			// Log who created notification
			var currentUser = Session.retrieveObject('user');
			$scope.newNotification.user = currentUser;
			// Submit
			$.ajax({
				type: "POST",
				url: "notification/insert/notification",
				data: $scope.newNotification,
				success: function () {},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.ADD.ERROR_ADD'));
				},
				complete: function(err) {
					$state.go('notification');
				},
			});
		}
	};

	// Function to return boolean for form completion
	$scope.checkForm = function () {
		return (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100);
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
