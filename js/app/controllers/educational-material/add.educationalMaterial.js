angular.module('opalAdmin.controllers.educationalMaterial.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


/******************************************************************************
 * New Educational Material Page controller
 *******************************************************************************/
controller('educationalMaterial.add', function ($scope, $filter, $state, $sce, $uibModal, educationalMaterialCollectionService, filterCollectionService, Session) {

	// Function to go to previous page
	$scope.goBack = function () {
		window.history.back();
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

	// Default boolean variables
	$scope.titleSection = {open:false, show:true};
	$scope.typeSection = {open:false, show:false};
	$scope.phaseSection = {open:false, show:false};
	$scope.urlSection = {open:false, show:false};
	$scope.tocsSection = {open:false, show:false};
	$scope.shareUrlSection = {open:false, show:false};
	$scope.demoSection = {open:false, show:false};
	$scope.triggerSection = {
		show:false,
		patient: {open:false},
		appointment: {open:false},
		doctor: {open:false},
		machine: {open:false},
		diagnosis: {open:false}
	};

	// completed steps boolean object; used for progress bar
	var steps = {
		title: { completed: false },
		url: { completed: false },
		type: { completed: false },
		phase: { completed: false },
		tocs: { completed: false }
	};

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');

	$scope.language = Session.retrieveObject('user').language;

	// Initialize search field variables
	$scope.appointmentSearchField = "";
	$scope.dxSearchField = "";
	$scope.doctorSearchField = "";
	$scope.machineSearchField = "";
	$scope.patientSearchField = "";

	// Default count of completed steps
	$scope.numOfCompletedSteps = 0;

	// Default total number of steps
	$scope.stepTotal = 5;

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

	// Initialize a list of "phase in treatment" types
	$scope.phaseInTxs = [];

	// Initialize the new edu material object
	$scope.newEduMat = {
		name_EN: null,
		name_FR: null,
		url_EN: null,
		url_FR: null,
		share_url_EN: null,
		share_url_FR: null,
		type_EN: "",
		type_FR: "",
		phase_in_tx: null,
		tocs: [],
		triggers: []
	};

	// Initialize a list of sexes
	$scope.sexes = [
		{
			name: 'Male',
			name_display: $filter('translate')('EDUCATION.ADD.MALE'),
			icon: 'male'
		}, {
			name: 'Female',
			name_display: $filter('translate')('EDUCATION.ADD.FEMALE'),
			icon: 'female'
		}
	];

	// Initialize lists to hold triggers
	$scope.demoTrigger = {
		sex: null,
		age: {
			min: 0,
			max: 130
		}
	};
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
	}

	// Initialize lists to hold the distinct edu material types
	$scope.EduMatTypes = [];


	/* Function for the "Processing..." dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};
	$scope.showProcessingModal(); // Calling function

	$scope.formLoaded = false;
	// Function to load form as animations
	$scope.loadForm = function () {
		$('.form-box-left').addClass('fadeInDown');
		$('.form-box-right').addClass('fadeInRight');
	};

	// Call our API service to get each trigger
	filterCollectionService.getFilters().then(function (response) {
		response.data.appointments.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name;
		});
		$scope.appointmentTriggerList = response.data.appointments;

		$scope.dxTriggerList = response.data.dx;
		$scope.dxTriggerList.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name;
		});
		$scope.doctorTriggerList = response.data.doctors;
		$scope.machineTriggerList = response.data.machines;
		$scope.patientTriggerList = response.data.patients;

		processingModal.close(); // hide modal
		processingModal = null; // remove reference

		$scope.formLoaded = true;
		$scope.loadForm();

	}).catch(function(response) {
		alert($filter('translate')('EDUCATION.ADD.ERROR_TRIGGERS') + "\r\n\r\n" + response.status + " - " + response.data);
	});

	// Call our API to get the list of edu material types
	educationalMaterialCollectionService.getEducationalMaterialTypes().then(function (response) {
		$scope.EduMatTypes = response.data;
	}).catch(function(response) {
		alert($filter('translate')('EDUCATION.ADD.ERROR_TYPES') + "\r\n\r\n" + response.status + " - " + response.data);
	});

	// Call our API to get the list of phase-in-treatments
	educationalMaterialCollectionService.getPhasesInTreatment().then(function (response) {
		$scope.phaseInTxs = response.data;
		$scope.phaseInTxs.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name;
		});
	}).catch(function(response) {
		alert($filter('translate')('EDUCATION.ADD.ERROR_PHASES') + "\r\n\r\n" + response.status + " - " + response.data);
	});

	// Function to toggle necessary changes when updating titles
	$scope.titleUpdate = function () {

		$scope.titleSection.open = true;

		if ($scope.newEduMat.name_EN && $scope.newEduMat.name_FR) {

			$scope.typeSection.show = true;

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

	// Function to toggle necessary changes when updating the urls
	$scope.urlUpdate = function () {

		$scope.urlSection.open = true;

		if ($scope.newEduMat.url_EN || $scope.newEduMat.url_FR) {
			steps.tocs.completed = true; // Since it will be hidden
			$scope.tocsSection.show = false;
		}

		else {
			$scope.tocsSection.show = true;
		}

		if ($scope.newEduMat.url_EN && $scope.newEduMat.url_FR) {

			// Toggle booleans
			$scope.shareUrlSection.show = true;
			$scope.triggerSection.show = true;
			$scope.demoSection.show = true;

			// Toggle step completion
			steps.url.completed = true;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
		else {

			steps.tocs.completed = false; // No longer hidden
			// Toggle step completion
			steps.url.completed = false;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	// Function to toggle necessary changes when updating the types
	$scope.typeUpdate = function (type, language) {

		$scope.typeSection.open = true;

		if (type) {
			// Perform a string comparison to auto complete the other language field
			type = type.toLowerCase();
			for (var i=0; i < $scope.EduMatTypes.length; i++) {
				if (language === 'EN') {
					typeCompare = $scope.EduMatTypes[i].EN.toLowerCase();
					if (type === typeCompare) {
						// set the french to be the same
						$scope.newEduMat.type_FR = $scope.EduMatTypes[i].FR;
						break;
					}
				}
				else if (language === 'FR') {
					typeCompare = $scope.EduMatTypes[i].FR.toLowerCase();
					if (type === typeCompare) {
						// set the english to be the same
						$scope.newEduMat.type_EN = $scope.EduMatTypes[i].EN;
						break;
					}
				}
			}
		}

		if ($scope.newEduMat.type_EN && $scope.newEduMat.type_FR) {

			$scope.phaseSection.show = true;

			// Toggle step completion
			steps.type.completed = true;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		} else {
			// Toggle step completion
			steps.type.completed = false;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	// Function to toggle necessary changes when updating the phase in treatment
	$scope.phaseUpdate = function (phase) {

		$scope.newEduMat.phase_in_tx = phase;

		$scope.phaseSection.open = true;

		$scope.urlSection.show = true;
		$scope.tocsSection.show = true;

		// Toggle boolean
		steps.phase.completed = true;
		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// Function to toggle necessary changes when updating the share URL
	$scope.shareURLUpdate = function () {

		$scope.shareUrlSection.open = true;

	};

	// Function to toggle necessary changes when updating the sex
	$scope.sexUpdate = function (sex) {

		$scope.demoSection.open = true;

		if (!$scope.demoTrigger.sex) {
			$scope.demoTrigger.sex = sex.name;
			$scope.demoTrigger.sex_display = sex.name_display;
		} else if ($scope.demoTrigger.sex == sex.name) {
			$scope.demoTrigger.sex = null; // Toggle off
		} else {
			$scope.demoTrigger.sex = sex.name;
		}

	};

	// Function to toggle necessary changes when updating the age
	$scope.ageUpdate = function () {

		$scope.demoSection.open = true;

	};

	$scope.tocsComplete = false;
	// Function to toggle necessary changes when updating the table of contents
	$scope.tocUpdate = function () {

		$scope.tocsSection.open = true;

		steps.tocs.completed = true;
		$scope.tocsComplete = true;

		if (!$scope.newEduMat.tocs.length) {
			steps.url.completed = false; // Since it will be hidden
			$scope.tocsComplete = false;
			steps.tocs.completed = false;

			$scope.urlSection.show = true;

		} else {

			steps.url.completed = true; // Since it will be hidden
			$scope.urlSection.show = false;

			angular.forEach($scope.newEduMat.tocs, function (toc) {
				if (!toc.name_EN || !toc.name_FR || !toc.url_EN
					|| !toc.url_FR || !toc.type_EN || !toc.type_FR) {
					$scope.tocsComplete = false;
					steps.tocs.completed = false;
					steps.url.completed = false;
				}
			});

			if ($scope.tocsComplete) {
				$scope.shareUrlSection.show = true;
				$scope.triggerSection.show = true;
				$scope.demoSection.show = true;
			}

		}

		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// Function to add table of contents to newEduMat object
	$scope.addTOC = function () {
		var newOrder = $scope.newEduMat.tocs.length + 1;
		$scope.newEduMat.tocs.push({
			name_EN: "",
			name_FR: "",
			url_EN: "",
			url_FR: "",
			type_EN: "",
			type_FR: "",
			order: newOrder
		});
		$scope.tocUpdate();
	};

	// Function to remove table of contents from newEduMat object
	$scope.removeTOC = function (order) {
		$scope.newEduMat.tocs.splice(order - 1, 1);
		// Decrement orders for content after the one just removed
		for (var index = order - 1; index < $scope.newEduMat.tocs.length; index++) {
			$scope.newEduMat.tocs[index].order -= 1;
		}
		$scope.tocUpdate();
	};

	// Function to submit the new edu material
	$scope.submitEduMat = function () {
		if ($scope.checkForm()) {

			// Add demographic triggers, if defined
			if ($scope.demoTrigger.sex)
				$scope.newEduMat.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
			if ($scope.demoTrigger.age.min >= 0 && $scope.demoTrigger.age.max <= 130) { // i.e. not empty
				if ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max != 130) { // Filters were changed
					$scope.newEduMat.triggers.push({
						id: String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max)),
						type: 'Age'
					});
				}
			}
			// Add other triggers to new edu material object
			addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
			addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
			addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
			addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
			addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);

			// Log who created educational material
			var currentUser = Session.retrieveObject('user');
			$scope.newEduMat.user = currentUser;

			$.ajax({
				type: "POST",
				url: "educational-material/insert/educational-material",
				data: $scope.newEduMat,
				success: function (response) {
					response = JSON.parse(response);
					if (response.value)
						$state.go('educational-material');
					else
						alert($filter('translate')('EDUCATION.ADD.ERROR_INSERT') + "\r\n\r\n" + response.message);
				},
				error: function (err) {
					alert($filter('translate')('EDUCATION.ADD.ERROR_INSERT') + "\r\n\r\n" + err.status + " - " + err.statusText);
				}
			});
		}
	};

	// Function to toggle trigger in a list on/off
	$scope.selectTrigger = function (trigger, selectAll) {
		selectAll.all = false;
		selectAll.checked = false;
		if (trigger.added)
			trigger.added = 0;
		else
			trigger.added = 1;
	};

	// Function for selecting all triggers in a trigger list
	$scope.selectAllTriggers = function (triggerList,triggerFilter,selectAll) {

		var filtered = $scope.filter(triggerList,triggerFilter);

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

	// Function to assign search fields when textbox changes
	$scope.searchAppointment = function (field) {
		$scope.appointmentSearchField = field;
		$scope.selectAll.appointment.all = false;
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

	// Function for search through the triggers
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

	// Function to return triggers that have been checked
	function addTriggers(triggerList, selectAll) {
		if (selectAll) {
			$scope.newEduMat.triggers.push({id: 'ALL', type: triggerList[0].type});
		}
		else {
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					$scope.newEduMat.triggers.push({ id: trigger.id, type: trigger.type });
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
