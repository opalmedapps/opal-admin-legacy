angular.module('opalAdmin.controllers.post.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).


// Function to accept/trust html (styles, classes, etc.)
filter('deliberatelyTrustAsHtml', function ($sce) {
	return function (text) {
		return $sce.trustAsHtml(text);
	};
}).
controller('post.edit', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, $locale, postCollectionService, filterCollectionService, uiGridConstants, Session) {

	// Default Booleans
	$scope.changesMade = false; // changes have been made?

	$scope.language = Session.retrieveObject('user').language;

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');

	$scope.post = {}; // initialize post object
	$scope.postModal = {}; // for deep copy

	// Initialize lists to hold triggers
	$scope.appointmentTriggerList = [];
	$scope.dxTriggerList = [];
	$scope.doctorTriggerList = [];
	$scope.machineTriggerList = [];
	$scope.patientTriggerList = [];

	$scope.selectAll = {
		appointment: {all:false, checked:false},
		diagnosis: {all:false, checked:false},
		doctor: {all:false, checked:false},
		machine: {all:false, checked:false},
		patient: {all:false, checked:false}
	};

	// Initialize search field variables
	$scope.appointmentSearchField = "";
	$scope.dxSearchField = "";
	$scope.doctorSearchField = "";
	$scope.machineSearchField = "";
	$scope.patientSearchField = "";

	// Function to assign search fields when textbox changes
	$scope.searchAppointment = function (field) {
		$scope.selectAll.appointment.all = false;
		$scope.appointmentSearchField = field;
	};
	$scope.searchDiagnosis = function (field) {
		$scope.dxSearchField = field;
		$scope.selectAll.diagnosis.all = false;
	};
	$scope.searchDoctor = function (field) {
		$scope.doctorSearchField = field;
		$scope.selectAll.doctor.all = false;
	};
	$scope.searchMachine = function (field) {
		$scope.machineSearchField = field;
		$scope.selectAll.machine.all = false;
	};
	$scope.searchPatient = function (field) {
		$scope.patientSearchField = field;
		$scope.selectAll.patient.all = false;
	};

	$locale["DATETIME_FORMATS"]["SHORTDAY"] = [
		$filter('translate')('DATEPICKER.SUNDAY_S'),
		$filter('translate')('DATEPICKER.MONDAY_S'),
		$filter('translate')('DATEPICKER.TUESDAY_S'),
		$filter('translate')('DATEPICKER.WEDNESDAY_S'),
		$filter('translate')('DATEPICKER.THURSDAY_S'),
		$filter('translate')('DATEPICKER.FRIDAY_S'),
		$filter('translate')('DATEPICKER.SATURDAY_S')
	];

	$locale["DATETIME_FORMATS"]["MONTH"] = [
		$filter('translate')('DATEPICKER.JANUARY'),
		$filter('translate')('DATEPICKER.FEBRUARY'),
		$filter('translate')('DATEPICKER.MARCH'),
		$filter('translate')('DATEPICKER.APRIL'),
		$filter('translate')('DATEPICKER.MAY'),
		$filter('translate')('DATEPICKER.JUNE'),
		$filter('translate')('DATEPICKER.JULY'),
		$filter('translate')('DATEPICKER.AUGUST'),
		$filter('translate')('DATEPICKER.SEPTEMBER'),
		$filter('translate')('DATEPICKER.OCTOBER'),
		$filter('translate')('DATEPICKER.NOVEMBER'),
		$filter('translate')('DATEPICKER.DECEMBER')
	];

	// Function for search through the filters
	$scope.searchAppointmentFilter = function (Filter) {
		var keyword = new RegExp($scope.appointmentSearchField, 'i');
		return !$scope.appointmentSearchField || keyword.test($scope.language.toUpperCase() === "FR"?Filter.name_FR:Filter.name);
	};
	$scope.searchDxFilter = function (Filter) {
		var keyword = new RegExp($scope.dxSearchField, 'i');
		return !$scope.dxSearchField || keyword.test($scope.language.toUpperCase() === "FR"?Filter.name_FR:Filter.name);
	};
	$scope.searchDoctorFilter = function (Filter) {
		var keyword = new RegExp($scope.doctorSearchField, 'i');
		return !$scope.doctorSearchField || keyword.test(Filter.name);
	};
	$scope.searchMachineFilter = function (Filter) {
		var keyword = new RegExp($scope.machineSearchField, 'i');
		return !$scope.machineSearchField || keyword.test(Filter.name);
	};
	$scope.searchPatientFilter = function (Filter) {
		var keyword = new RegExp($scope.patientSearchField, 'i');
		return !$scope.patientSearchField || keyword.test(Filter.name);
	};

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};
	// Show processing dialog
	$scope.showProcessingModal();

	// Call our API service to get the current post details
	postCollectionService.getPostDetails($scope.currentPost.serial).then(function (response) {

		// Assign value
		$scope.post = response.data;
		$scope.postModal = jQuery.extend(true, {}, $scope.post); // deep copy

		if ($scope.post.publish_date) {
			var publishDateTime = $scope.post.publish_date.split(" ");
			$scope.post.publish_date = publishDateTime[0];
			$scope.post.publish_time = publishDateTime[1];

			// Split the hours and minutes to display them in their respective text boxes
			var hours = $scope.post.publish_time.split(":")[0];
			var minutes = $scope.post.publish_time.split(":")[1];
			var d = new Date();
			d.setHours(hours);
			d.setMinutes(minutes);
			$scope.post.publish_time = d;

			var year = $scope.post.publish_date.split("-")[0];
			var month = parseInt($scope.post.publish_date.split("-")[1]) - 1;
			var day = parseInt($scope.post.publish_date.split("-")[2]);
			$scope.post.publish_date = new Date(year, month, day);
		}

		// Call our API service to get each filter
		filterCollectionService.getFilters().then(function (response) {
			response.data.appointments.forEach(function(entry) {
				if(Session.retrieveObject('user').language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name;
			});
			response.data.dx.forEach(function(entry) {
				if(Session.retrieveObject('user').language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name;
			});

			$scope.appointmentTriggerList = checkAdded(response.data.appointments, $scope.selectAll.appointment); // Assign value
			$scope.dxTriggerList = checkAdded(response.data.dx, $scope.selectAll.diagnosis);
			$scope.doctorTriggerList = checkAdded(response.data.doctors, $scope.selectAll.doctor);
			$scope.machineTriggerList = checkAdded(response.data.machines, $scope.selectAll.machine);
			$scope.patientTriggerList = checkAdded(response.data.patients, $scope.selectAll.patient);

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

		}).catch(function(response) {
			alert($filter('translate')('POSTS.EDIT.ERROR_TRIGGERS') + "\r\n\r\n" + response.status + " " + response.data);
		});
	}).catch(function(response) {
		alert($filter('translate')('POSTS.EDIT.ERROR_DETAILS') + "\r\n\r\n" +  response.status + " " + response.data);
	});

	// Function to toggle trigger in a list on/off
	$scope.selectTrigger = function (trigger, selectAll) {
		$scope.changesMade = true;
		selectAll.all = false;
		selectAll.checked = false;
		$scope.post.triggers_updated = 1;
		if (trigger.added)
			trigger.added = 0;
		else
			trigger.added = 1;
	};

	// Function for selecting all triggers in a trigger list
	$scope.selectAllTriggers = function (triggerList,triggerFilter,selectAll) {

		var filtered = $scope.filter(triggerList,triggerFilter);
		$scope.post.triggers_updated = 1;
		$scope.changesMade = true;

		if (filtered.length == triggerList.length) { // search field wasn't used
			if (selectAll.checked) {
				angular.forEach(filtered, function (trigger) {
					trigger.added = 0;
				});
				selectAll.checked = false; // toggle off
				selectAll.all = false;
			}
			else {
				angular.forEach(filtered, function (trigger) {
					trigger.added = 1;
				});

				selectAll.checked = true; // toggle on
				selectAll.all = true;
			}
		}
		else {
			if (selectAll.checked) { // was checked
				angular.forEach(filtered, function (trigger) {
					trigger.added = 0;
				});
				selectAll.checked = false; // toggle off
				selectAll.all = false;
			}
			else { // was not checked
				angular.forEach(filtered, function (trigger) {
					trigger.added = 1;
				});

				selectAll.checked = true; // toggle on

			}
		}

	};

	// Function to assign 1 to existing triggers
	function checkAdded(triggerList, selectAll) {
		angular.forEach($scope.post.triggers, function (selectedTrigger) {
			var selectedTriggerId = selectedTrigger.id;
			var selectedTriggerType = selectedTrigger.type;
			angular.forEach(triggerList, function (trigger) {
				var triggerId = trigger.id;
				var triggerType = trigger.type;
				if (triggerType == selectedTriggerType) {
					if (selectedTriggerId == 'ALL') {
						selectAll.all = true;
						selectAll.checked = true;
						trigger.added = 1;
					}
					else if (triggerId == selectedTriggerId) {
						trigger.added = 1;
					}
				}
			});
		});

		return triggerList;
	}

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		if ($scope.post.name_EN && $scope.post.name_FR && $scope.post.body_EN && $scope.post.body_FR
			&& ($scope.post.type != 'Announcement' || ($scope.post.publish_date && $scope.post.publish_time))
			&& $scope.changesMade) {
			return true;
		}
		else
			return false;
	};

	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	$scope.detailsUpdated = function () {
		$scope.post.details_updated = 1;
		$scope.setChangesMade();
	}

	// Submit changes
	$scope.updatePost = function () {

		if ($scope.checkForm()) {
			// For some reason the HTML text fields add a zero-width-space
			// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
			$scope.post.body_EN = $scope.post.body_EN.replace(/\u200B/g,'');
			$scope.post.body_FR = $scope.post.body_FR.replace(/\u200B/g,'');

			$scope.post.triggers = []; // Empty triggers
			// Add triggers to post
			addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
			addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
			addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
			addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
			addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);
			if ($scope.post.publish_date) {
				// Concat date and time
				$scope.post.publish_date = String(moment($scope.post.publish_date).format("YYYY-MM-DD")) + " " +
					String(moment($scope.post.publish_time).format("HH:mm"));
			}

			// Log who updated post
			var currentUser = Session.retrieveObject('user');
			$scope.post.user = currentUser;
			// Submit form
			$.ajax({
				type: "POST",
				url: "post/update/post",
				data: $scope.post,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('POSTS.EDIT.SUCCESS_EDIT') ;
						$scope.showBanner();
					}
					else {
						alert($filter('translate')('POSTS.EDIT.ERROR_EDIT'));
					}
					$uibModalInstance.close();
				},
				error: function(err) {
					alert($filter('translate')('POSTS.EDIT.ERROR_EDIT') + "\r\n\r\n" + err.status + " - " + err.statusText);
					$uibModalInstance.close();
				}
			});
		}
	};

	// Function to return triggers that have been checked
	function addTriggers(triggerList, selectAll) {
		if (selectAll) {
			$scope.post.triggers.push({id: 'ALL', type: triggerList[0].type});
		}
		else {
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					$scope.post.triggers.push({ id: trigger.id, type: trigger.type });

			});
		}
	}

	// Function to check if triggers are added
	$scope.checkTriggers = function (triggerList) {
		var triggersAdded = false;
		angular.forEach(triggerList, function (trigger) {
			if (trigger.added)
				triggersAdded = true;
		});
		return triggersAdded;
	};

	$scope.showWeeks = true; // show weeks sidebar
	$scope.toggleWeeks = function () {
		$scope.showWeeks = !$scope.showWeeks;
	};

	// set minimum date (today's date)
	$scope.toggleMin = function () {
		$scope.minDate = ($scope.minDate) ? null : new Date();
	};
	$scope.toggleMin();

	$scope.popup = {
		opened: false
	};

	// Open popup calendar
	$scope.open = function () {
		$scope.popup.opened = true;
	};

	$scope.dateOptions = {
		'year-format': "'yy'",
		'starting-day': 1
	};

	// Date format
	$scope.format = 'yyyy-MM-dd';

	// object for cron repeat units
	$scope.repeatUnits = [
		'Minutes',
		'Hours'
	];

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});