angular.module('opalAdmin.controllers.publication.tool.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).controller('publication.tool.add', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, FrequencyFilterService, Session) {
	// navigation function
	$scope.goBack = function () {
		$state.go('questionnaire');
	};

	$scope.goBack = function () {
		window.history.back();
	};

	// Default booleans
	$scope.titleSection = { open: false, show: true };
	$scope.questionnaireSection = { open: false, show: false };
	$scope.demoSection = {open:false, show:false};
	$scope.publishFrequencySection = {open: false, show:false};
	$scope.triggerSection = {
		show:false,
		patient: {open:false},
		appointment: {open:false},
		appointmentStatus: {open:false},
		doctor: {open:false},
		machine: {open:false},
		diagnosis: {open:false}
	};

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	// step bar
	var steps = {
		name: { completed: false },
		questionnaire: { completed: false },
	};

	$scope.numOfCompletedSteps = 0;
	$scope.preview = [];
	$scope.atEntered = '';
	$scope.stepTotal = 2;
	$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	// Initialize search field variables
	$scope.appointmentSearchField = null;
	$scope.dxSearchField = null;
	$scope.doctorSearchField = null;
	$scope.machineSearchField = null;
	$scope.patientSearchField = null;

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};

	// Function to calculate / return step progress
	function trackProgress(value, total) {
		var result = Math.round(100 * value / total);
		if (result > 100) result = 100;
		return result;
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

	// Initialize the new answer type object
	$scope.newQuestionnaireToPublish = {
		name_EN: "",
		name_FR: "",
		questionnaireId: undefined,
		OAUserId: OAUserId,
		triggers: [],
		occurrence: {
			start_date: null,
			end_date: null,
			set: 0,
			frequency: {
				custom: 0,
				meta_key: null,
				meta_value: null,
				additionalMeta: []
			}
		}
	};

	// Initialize a list of sexes
	$scope.sexes = [
		{
			name: 'Male',
			icon: 'male'
		}, {
			name: 'Female',
			icon: 'female'
		}
	];

	// Initialize lists to hold triggers
	$scope.demoTrigger = {
		sex: null,
		age: {
			min: 0,
			max: 100
		}
	};
	// Initialize lists to hold triggers
	$scope.appointmentTriggerList = [];
	$scope.dxTriggerList = [];
	$scope.doctorTriggerList = [];
	$scope.machineTriggerList = [];
	$scope.patientTriggerList = [];
	$scope.appointmentStatusList = [];

	$scope.selectAll = {
		appointment: {all:false, checked:false},
		diagnosis: {all:false, checked:false},
		doctor: {all:false, checked:false},
		machine: {all:false, checked:false},
		patient: {all:false, checked:false}
	};

	// Initialize variables for holding selected answer type & group
	$scope.selectedAt = null;

	// Filter lists initialized
	$scope.questionnaireList = [];

	$scope.searchAt = function (field) {
		$scope.atEntered = field;
	};

	$scope.updateAt = function (selectedAt) {
		$scope.questionnaireSection.open = true;

		if ($scope.newQuestionnaireToPublish.questionnaireId) {
			$scope.publishFrequencySection.show = true;
			$scope.triggerSection.show = true;
			$scope.demoSection.show = true;
			$scope.selectedAt = selectedAt;
			steps.questionnaire.completed = true;
		}
		else {
			$scope.triggerSection.show = false;
			$scope.demoSection.show = false;
			$scope.publishFrequencySection.show = false;
			steps.questionnaire.completed = false;
		}
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	//search function
	$scope.searchAtFilter = function (Filter) {
		var keyword = new RegExp($scope.atEntered, 'i');
		return !$scope.atEntered || keyword.test(Filter.name_EN);
	};

	// Call our API service to get each trigger
	filterCollectionService.getFilters().then(function (response) {
		$scope.appointmentTriggerList = response.data.appointments; // Assign value
		$scope.dxTriggerList = response.data.dx;
		$scope.doctorTriggerList = response.data.doctors;
		$scope.machineTriggerList = response.data.machines;
		$scope.patientTriggerList = response.data.patients;
		$scope.appointmentStatusList = response.data.appointmentStatuses;
	}).catch(function(response) {
		alert('Error occurred getting filter list.\r\n' + response.status + " " + response.data);
	});

	$scope.submitTemplateQuestion = function () {

		if ($scope.checkForm()) {


			// Add demographic triggers, if defined
			if ($scope.demoTrigger.sex)
				$scope.newQuestionnaireToPublish.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
			if ($scope.demoTrigger.age.min >= 0 && $scope.demoTrigger.age.max <= 100) { // i.e. not empty
				if ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max !== 100) { // triggers were changed
					$scope.newQuestionnaireToPublish.triggers.push({
						id: String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max)),
						type: 'Age'
					});
				}
			}
			// Add triggers to new legacy questionnaire object
			addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
			addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
			addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
			addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
			addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);
			addTriggers($scope.appointmentStatusList);

			// Add frequency trigger if exists
			if ($scope.showFrequency) {
				$scope.newQuestionnaireToPublish.occurrence.set = 1;
				// convert dates to timestamps
				console.log("1");
				$scope.newQuestionnaireToPublish.occurrence.start_date = moment($scope.newQuestionnaireToPublish.occurrence.start_date).format('X');
				console.log("2");
				if ($scope.newQuestionnaireToPublish.occurrence.end_date) {
					console.log("3");
					$scope.newQuestionnaireToPublish.occurrence.end_date = moment($scope.newQuestionnaireToPublish.occurrence.end_date).format('X');
					console.log("4");
				}
				if ($scope.newQuestionnaireToPublish.occurrence.frequency.custom) {
					$scope.newQuestionnaireToPublish.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
					$scope.newQuestionnaireToPublish.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
					$scope.newQuestionnaireToPublish.occurrence.frequency.additionalMeta = [];
					angular.forEach(Object.keys($scope.additionalMeta), function(meta_key){
						if ($scope.additionalMeta[meta_key].length) {
							var metaDetails = {
								meta_key: meta_key,
								meta_value: $scope.additionalMeta[meta_key]
							};
							$scope.newQuestionnaireToPublish.occurrence.frequency.additionalMeta.push(metaDetails);
						}
					});
				}
				else {
					$scope.newQuestionnaireToPublish.occurrence.frequency.additionalMeta = [];
				}
			}

			// Log who created legacy questionnaire
			var currentUser = Session.retrieveObject('user');
			$scope.newQuestionnaireToPublish.OAUserId = currentUser.id;
			$scope.newQuestionnaireToPublish.sessionId = currentUser.sessionid;

			// Submit
			$.ajax({
				type: "POST",
				url: "php/questionnaire/insert.published_questionnaire.php",
				data: $scope.newQuestionnaireToPublish,
				success: function (result) {
					result = JSON.parse(result);
					if (result.code === 200) {
						$state.go('publication-tool');
					} else {
						alert("Unable to create the questionnaire. Code " + result.code + ".\r\nError message: " + result.message);
					}
				},
				error: function () {
					alert("Something went wrong.");
				}
			});
		}
	};

	// Update values from form
	$scope.updateQuestionText = function () {
		$scope.titleSection.open = true;
		if (!$scope.newQuestionnaireToPublish.name_EN && !$scope.newQuestionnaireToPublish.name_FR) {
			$scope.titleSection.open = false;
		}
		if ($scope.newQuestionnaireToPublish.name_EN && $scope.newQuestionnaireToPublish.name_FR) {
			$scope.questionnaireSection.show = true;
			steps.name.completed = true;
		} else {
			steps.name.completed = false;
		}
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// questionnaire API: retrieve data
	questionnaireCollectionService.getFinalizedQuestionnaires(OAUserId).then(function (response) {
		$scope.questionnaireList = response.data;
	}).catch(function (response) {
		alert('Error occurred getting group list:' + response.status + response.data);
	});

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');


	// Call our API service to get each trigger
	filterCollectionService.getFilters().then(function (response) {

		$scope.appointmentTriggerList = response.data.appointments; // Assign value
		$scope.dxTriggerList = response.data.dx;
		$scope.doctorTriggerList = response.data.doctors;
		$scope.machineTriggerList = response.data.machines;
		$scope.patientTriggerList = response.data.patients;
		$scope.appointmentStatusList = response.data.appointmentStatuses;

		// processingModal.close(); // hide modal
		// processingModal = null; // remove reference
		//
		// $scope.formLoaded = true;
		// $scope.loadForm();

	}).catch(function(response) {
		alert('Error occurred getting filter list.\r\n' + response.status + " " + response.data);
	});

	// Function to toggle necessary changes when updating the sex
	$scope.sexUpdate = function (sex) {
		$scope.demoSection.open = true;
		if (!$scope.demoTrigger.sex) {
			$scope.demoTrigger.sex = sex.name;
		} else if ($scope.demoTrigger.sex == sex.name) {
			$scope.demoTrigger.sex = null; // Toggle off
			if ($scope.demoTrigger.age.min == 0 && $scope.demoTrigger.age.max == 100) {
				$scope.demoSection.open = false;
			}
		} else {
			$scope.demoTrigger.sex = sex.name;
		}

	};

	// Function to toggle necessary changes when updating the age
	$scope.ageUpdate = function () {

		$scope.demoSection.open = true;
		if ($scope.demoTrigger.age.min == 0 && $scope.demoTrigger.age.max == 100
			&& !$scope.demoTrigger.sex) {
			$scope.demoSection.open = false;
		}

	};

	// Function to toggle appointment status filter
	$scope.appointmentStatusUpdate = function (index) {
		angular.forEach($scope.appointmentStatusList, function (appointmentStatus, loopIndex) {
			if (index == loopIndex) {
				if (appointmentStatus.added)
					appointmentStatus.added = 0;
				else
					appointmentStatus.added = 1;
			}
			else {
				appointmentStatus.added = 0;
			}
		});
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

	// Function to assign legacy questionnaire when textbox is changing
	$scope.changeLegacyQuestionnaireFilter = function (legacyQuestionnaireFilter) {
		$scope.legacyQuestionnaireFilter = legacyQuestionnaireFilter;
	};

	// Function for searching through the educational material list
	$scope.searchEduMatsFilter = function (legacy_questionnaire) {
		var keyword = new RegExp($scope.legacyQuestionnaireFilter, 'i');
		return !$scope.legacyQuestionnaireFilter || keyword.test(legacy_questionnaire.name);
	};

	// Function for search through the triggers
	$scope.searchAppointmentFilter = function (Filter) {
		var keyword = new RegExp($scope.appointmentSearchField, 'i');
		return !$scope.appointmentSearchField || keyword.test(Filter.name);
	};
	$scope.searchDxFilter = function (Filter) {
		var keyword = new RegExp($scope.dxSearchField, 'i');
		return !$scope.dxSearchField || keyword.test(Filter.name);
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
		if(selectAll) {
			$scope.newQuestionnaireToPublish.triggers.push({id: 'ALL', type: triggerList[0].type});
		}
		else {
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					$scope.newQuestionnaireToPublish.triggers.push({ id: trigger.id, type: trigger.type });
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

	// Function to check frequency trigger forms are complete
	$scope.checkFrequencyTrigger = function () {
		if ($scope.showFrequency) {
			if (!$scope.newQuestionnaireToPublish.occurrence.start_date ||
				($scope.addEndDate && !$scope.newQuestionnaireToPublish.occurrence.end_date) ) {
				return false;
			} else {
				if ($scope.frequencySelected.id != 'custom') {
					return true;
				}
				else {
					if ($scope.customFrequency.unit.id == 'month') {
						if ($scope.repeatSub == 'onDate' && $scope.selectedDatesInMonth.length) {
							return true;
						}
						else if ($scope.repeatSub == 'onWeek' && $scope.selectedWeekNumberInMonth && $scope.selectedSingleDayInWeek) {
							return true;
						}
						else if (!$scope.repeatSub) {
							return true;
						}
						else
							return false;
					}
					else if ($scope.customFrequency.unit.id == 'year') {
						if ($scope.selectedWeekNumberInMonth.id && $scope.selectedSingleDayInWeek) {
							return true;
						}
						else if (!$scope.selectedWeekNumberInMonth.id && !$scope.selectedSingleDayInWeek) {
							return true;
						}
						else
							return false;
					}
					else
						return true;
				}
			}
		} else {
			return true;
		}
	};

	// Default boolean for showing frequency section details
	$scope.showFrequency = false;

	// Function for adding new frequency filter
	$scope.addFrequencyFilter = function () {
		$scope.showFrequency = true;
		$scope.newQuestionnaireToPublish.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
		$scope.newQuestionnaireToPublish.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
		$scope.publishFrequencySection.open = true;
	};

	// Function for removing new frequency filter
	$scope.removeFrequencyFilter = function () {
		$scope.showFrequency = false; // Hide form
		$scope.newQuestionnaireToPublish.occurrence.set = 0; // Not set anymore
		$scope.flushAllFrequencyFilters();
		$scope.publishFrequencySection.open = false;
	};

	// Function to reset all frequency filters
	$scope.flushAllFrequencyFilters = function () {
		$scope.flushPresetFrequency();
		$scope.flushRepeatDates();
		$scope.flushRepeatInterval();
		$scope.flushRepeatTypes();
	};

	// Function to reset the preset frequency
	$scope.flushPresetFrequency = function () {
		$scope.frequencySelected = $scope.presetFrequencies[0];
	};

	// Function to reset repeat dates
	$scope.flushRepeatDates = function () {
		$scope.newQuestionnaireToPublish.occurrence.start_date = null;
		$scope.newQuestionnaireToPublish.occurrence.end_date = null;
	};

	// Function to reset repeat interval
	$scope.flushRepeatInterval = function () {
		$scope.customFrequency = jQuery.extend(true, {}, FrequencyFilterService.customFrequency);
		$scope.customFrequency.unit = $scope.frequencyUnits[0];
		$scope.customFrequency.meta_value = 1;
	};

	// Function to reset repeat types
	$scope.flushRepeatTypes = function () {
		$scope.additionalMeta = jQuery.extend(true, {}, FrequencyFilterService.additionalMeta);
		$scope.repeatSub = null;
		$scope.selectedDaysInWeek = [];
		$scope.selectedDaysInWeekText = "";
		$scope.selectedSingleDayInWeek = null; // Default
		$scope.selectedDatesInMonthText = "";
		$scope.selectedDatesInMonth = [];
		$scope.selectedWeekNumberInMonthText = "";
		$scope.selectedMonthsInYear = [];
		$scope.selectedMonthsInYearText = "";
	};

	// Date format for start and end frequency dates
	$scope.format = 'yyyy-MM-dd';
	$scope.dateOptionsStart = {
		formatYear: "'yy'",
		startingDay: 0,
		minDate: new Date(),
		maxDate: null
	};
	$scope.dateOptionsEnd = {
		formatYear: "'yy'",
		startingDay: 0,
		minDate: new Date(),
		maxDate: null
	};
	// Watch to restrict the end calendar to not choose an earlier date than the start date
	$scope.$watch('newQuestionnaireToPublish.occurrence.start_date', function(startDate){
		if (startDate !== undefined) {
			$scope.dateOptionsEnd.minDate = startDate;
		}
	});
	// Watch to restrict the start calendar to not choose a start after the end date
	$scope.$watch('newQuestionnaireToPublish.occurrence.end_date', function(endDate){
		if (endDate !== undefined) {
			$scope.dateOptionsStart.maxDate = endDate;
		}
		else
			$scope.dateOptionsStart.maxDate = null;
	});

	// Open popup calendar
	$scope.popupStart = {};
	$scope.popupEnd = {};
	$scope.openStart = function ($event) {
		$event.preventDefault();
		$event.stopPropagation();

		$scope.popupStart['opened'] = true;
		$scope.popupEnd['opened'] = false;
	};
	$scope.openEnd = function ($event) {
		$event.preventDefault();
		$event.stopPropagation();
		$scope.popupStart['opened'] = false;
		$scope.popupEnd['opened'] = true;
	};

	// default hide end date
	$scope.addEndDate = false;
	$scope.toggleEndDate = function () {
		$scope.addEndDate = !$scope.addEndDate;
		if (!$scope.addEndDate) {
			$scope.newQuestionnaireToPublish.occurrence.end_date = null;
		}
		$scope.publishFrequencySection.open = true;
	};

	// Initialize list of preset publishing frequencies
	$scope.presetFrequencies = FrequencyFilterService.presetFrequencies;
	$scope.frequencySelected = $scope.presetFrequencies[0]; // Default "Once"

	$scope.selectFrequency = function (frequency) {
		$scope.frequencySelected = frequency;
		$scope.publishFrequencySection.open = true;
		if (frequency.id != 'custom') {
			$scope.newQuestionnaireToPublish.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
			$scope.newQuestionnaireToPublish.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
			$scope.newQuestionnaireToPublish.occurrence.frequency.custom = 0;
			$scope.flushRepeatInterval();
			$scope.flushRepeatTypes();
		}
		else {
			$scope.newQuestionnaireToPublish.occurrence.frequency.custom = 1;
		}
	};

	// Initialize object for repeat interval
	$scope.customFrequency = FrequencyFilterService.customFrequency;
	$scope.frequencyUnits = FrequencyFilterService.frequencyUnits;
	$scope.customFrequency.unit = $scope.frequencyUnits[0]; // Default "1 Day"

	// Custom watch to singularize/pluralize frequency unit names
	$scope.$watch('customFrequency.meta_value', function(newValue, oldValue){

		if ($scope.frequencySelected.id == 'custom') {
			if (newValue === 1) { // Singular
				angular.forEach($scope.frequencyUnits, function (unit) {
					unit.name = unit.name.slice(0,-1); // remove plural 's'
				});
			}
			else if (newValue > 1 && oldValue === 1) { // Was singular now plural
				angular.forEach($scope.frequencyUnits, function (unit) {
					unit.name = unit.name + 's'; // pluralize words
				});
			}
		}
	});

	// Default
	$scope.selectedDaysInWeek = [];
	$scope.selectedDaysInWeekText = "";


	// Initialize days of the week
	$scope.daysInWeek = FrequencyFilterService.daysInWeek;
	$scope.selectedSingleDayInWeek = null; // Default
	$scope.selectedSingleDayInWeekText = "";

	// settings for week dropdown menu
	$scope.weekDropdownSettings = {
		displayProp: 'name',
		showCheckAll: false,
		showUncheckAll: false,
		styleActive: true,
		buttonClasses: 'btn btn-default btn-frequency-select',
		smartButtonTextProvider: function (selectionArray) {
			if (selectionArray.length == 1) {
				return '1 Day Selected';
			}
			return selectionArray.length + " Days Selected";
		}
	};
	// event options for week dropdown menu
	$scope.weekDropdownEvents = {
		onItemSelect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);},
		onItemDeselect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);}
	};
	// Function when selecting the days on the week
	$scope.selectDayInWeek = function (day, unit) {
		$scope.selectedSingleDayInWeek = day;
		$scope.publishFrequencySection.open = true;
		if (day) {
			if (unit == 'week') { // Selecting multiple days from week repeat interval

				// Manipulate day in week meta data array
				var indexOfDay = $scope.additionalMeta.repeat_day_iw.indexOf(day.id);
				if (indexOfDay > -1) {
					$scope.additionalMeta.repeat_day_iw.splice(indexOfDay,1);
				} else {
					$scope.additionalMeta.repeat_day_iw.push(day.id);
				}

				$scope.setSelectedDaysInWeekText($scope.selectedDaysInWeek);
			}
			// Selecting a single day in the week from month or year repeat interval
			else if (unit == 'month' || unit == 'year') {
				// If a week number exists we are ready to add meta data
				if ($scope.selectedWeekNumberInMonth) {
					$scope.additionalMeta.repeat_day_iw = [day.id];
					$scope.additionalMeta.repeat_week_im = [$scope.selectedWeekNumberInMonth.id];
					$scope.setSelectedWeekNumberInMonthText($scope.selectedWeekNumberInMonth);
					$scope.setSelectedSingleDayInWeekText(day);

				}
				else { // Empty meta data array
					$scope.additionalMeta.repeat_day_iw = [];
					$scope.additionalMeta.repeat_week_im = [];
					$scope.selectedWeekNumberInMonthText = "";
				}
			}
		}
		else { // A day in week was not selected
			if (unit == 'month' || unit == 'year') {
				// Empty meta data array
				$scope.additionalMeta.repeat_day_iw = [];
				$scope.additionalMeta.repeat_week_im = [];
				$scope.selectedWeekNumberInMonthText = "";
			}
		}
	};

	$scope.setSelectedDaysInWeekText = function (days) {
		$scope.selectedDaysInWeekText = ""; // Destroy text

		// Construct text for display of selected days
		for (var i = 0; i < days.length; i++) {
			if (days.length == 1) {
				// Eg. Sunday
				$scope.selectedDaysInWeekText = days[i].name;
			}
			else if (i < days.length-1) {
				// Eg. Sunday, Monday, etc.
				$scope.selectedDaysInWeekText += days[i].name + ", ";
			}
			else {
				// Remove last comma and replace with "and"
				// Eg. Sunday, Monday and Tuesday
				$scope.selectedDaysInWeekText = $scope.selectedDaysInWeekText.slice(0,-2) + " and " + $scope.selectedDaysInWeek[i].name;
			}
		}
	};

	$scope.setSelectedWeekNumberInMonthText = function (week) {
		$scope.selectedWeekNumberInMonthText = week.name;

	};

	$scope.setSelectedSingleDayInWeekText = function (day) {
		$scope.selectedSingleDayInWeekText = day.name;
	};

	$scope.setSelectedMonthsInYearText = function (months) {
		// Construct text for display of selected months
		for (var i = 0; i < months.length; i++) {
			// Single month
			// Eg. January
			if (months.length == 1) {
				$scope.selectedMonthsInYearText = months[i].name;
			}
			// Concat months with commas
			// Eg. January, March, April
			else if (i < months.length-1) {
				$scope.selectedMonthsInYearText += months[i].name + ", ";
			}

			// Replace last comma with "and"
			// Eg. January, March and April
			else {
				$scope.selectedMonthsInYearText = $scope.selectedMonthsInYearText.slice(0,-2) + " and " + $scope.selectedMonthsInYear[i].name;
			}
		}

	};

	$scope.setSelectedDatesInMonthText = function (dates) {
		// Construct text for display of selected dates
		angular.forEach(dates, function (dateNumber,index) {
			// Conditionals for proper suffix
			if (dateNumber % 10 == 1 && dateNumber != 11) {
				dateNumber += "st";
			}
			else if (dateNumber % 10 == 2 && dateNumber != 12) {
				dateNumber += "nd";
			}
			else if (dateNumber % 10 == 3 && dateNumber != 13) {
				dateNumber += "rd";
			}
			else {
				dateNumber += "th";
			}
			// Single date chosen
			// Eg. 4th
			if (dates.length == 1) {
				$scope.selectedDatesInMonthText = dateNumber;
			}
			// Concat commas
			// Eg. 4th, 5th
			else if (index < dates.length-1) {
				$scope.selectedDatesInMonthText += dateNumber + ", ";
			}
			// Replace last comma with an "and"
			// Eg. 1st, 2nd and 4th
			else {
				$scope.selectedDatesInMonthText = $scope.selectedDatesInMonthText.slice(0,-2) + " and " + dateNumber;
			}
		});
	};

	// Function when a repeat interval is selected
	$scope.selectRepeatInterval = function (unit) {
		if (unit.name != 'week') { // Week wasn't selected
			// Remove week-related meta data
			$scope.selectedDaysInWeek = [];
			$scope.selectedDaysInWeekText = "";
			$scope.additionalMeta.repeat_day_iw = [];
		}
		if (unit.name != 'month') { // Month wasn't selected
			// Remove month-related meta data
			$scope.additionalMeta.repeat_date_im = [];
			$scope.selectedDatesInMonthText = "";
			$scope.selectedDatesInMonth = [];
			$scope.repeatSub = null;
			$scope.additionalMeta.repeat_day_iw = [];
			$scope.additionalMeta.repeat_week_im = [];
			$scope.selectedWeekNumberInMonthText = "";
			$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[0];
			$scope.selectedSingleDayInWeek = null;

		}
		if(unit.name != 'year') { // Year wasn't selected
			// Remove year-related meta data
			$scope.additionalMeta.repeat_month_iy = [];
			$scope.selectedMonthsInYear = [];
			$scope.selectedMonthsInYearText = "";
		}
		$scope.publishFrequencySection.open = true;
	};

	$scope.repeatSub = null;
	// Function to set the tab options for repeats onDate or onWeek
	$scope.setRepeatSub = function(repeatSub) {

		if ($scope.repeatSub != repeatSub) {
			$scope.repeatSub = repeatSub; // set tab active
		}
		else
			$scope.repeatSub = null; // remove reference/active

		if ($scope.repeatSub != 'onDate') { // date tab wasn't selected
			// Remove date-related meta data
			$scope.additionalMeta.repeat_date_im = [];
			$scope.selectedDatesInMonthText = "";
			$scope.selectedDatesInMonth = [];
		}
		if ($scope.repeatSub != 'onWeek') { // week tab wasn't selected
			// Remove week-related meta data
			$scope.additionalMeta.repeat_day_iw = [];
			$scope.additionalMeta.repeat_week_im = [];
			$scope.selectedWeekNumberInMonthText = "";
			$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[0];
			$scope.selectedSingleDayInWeek = null;
		}

		$scope.publishFrequencySection.open = true;
	};

	// Function watch to deal with selected dates in calendar
	$scope.selectedDatesInMonth = [];
	$scope.selectedDatesInMonthText = "";
	$scope.$watch('selectedDatesInMonth', function(newArray, oldArray){
		if(newArray){
			// Manage date metadata array
			$scope.additionalMeta.repeat_date_im = [];
			$scope.selectedDatesInMonthText = "";
			angular.forEach(newArray, function (date) {
				let dateNumber = moment(date).get('date');
				$scope.additionalMeta.repeat_date_im.push(dateNumber);
			});

			// Sort array
			$scope.additionalMeta.repeat_date_im.sort(function(a, b){return a - b;});

			$scope.setSelectedDatesInMonthText($scope.additionalMeta.repeat_date_im);
		}
	}, true);

	// initialize list of weeks in month
	$scope.weekNumbersInMonth = FrequencyFilterService.weekNumbersInMonth;
	$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[0]; // Default null
	$scope.selectedWeekNumberInMonthText = "";

	// Function to set week of the month
	$scope.selectWeekInMonth = function (week) {
		$scope.selectedWeekNumberInMonth = week;
		$scope.publishFrequencySection.open = true;
		// If a single day was chosen
		if (week.id && $scope.selectedSingleDayInWeek) {
			// Manage meta data
			$scope.additionalMeta.repeat_day_iw = [$scope.selectedSingleDayInWeek.id];
			$scope.additionalMeta.repeat_week_im = [week.id];
			$scope.setSelectedWeekNumberInMonthText(week);
			$scope.setSelectedSingleDayInWeekText($scope.selectedSingleDayInWeek);

		}
		else {
			// Erase meta data arrays
			$scope.additionalMeta.repeat_day_iw = [];
			$scope.additionalMeta.repeat_week_im = [];
			$scope.selectedWeekNumberInMonthText = "";
		}
	};

	// Set calendar to static date that starts on Sunday (Eg. Jan. 2018)
	// (For easy use when selecting dates on the calendar)
	$scope.staticMonth = moment().set({'year':2018, 'month': 0});

	// settings for month dropdown menu
	$scope.monthDropdownSettings = {
		displayProp: 'name',
		showCheckAll: false,
		showUncheckAll: false,
		styleActive: true,
		buttonClasses: 'btn btn-default btn-frequency-select',
		smartButtonTextProvider: function (selectionArray) {
			if (selectionArray.length == 1) {
				return '1 Month Selected';
			}
			return selectionArray.length + " Months Selected";
		}
	};
	// event options for month dropdown menu
	$scope.monthDropdownEvents = {
		onItemSelect: function (month) {$scope.selectMonthInYear(month);},
		onItemDeselect: function (month) {$scope.selectMonthInYear(month);}
	};

	// Initialize list of months in a year
	$scope.selectedMonthsInYear = [];
	$scope.selectedMonthsInYearText = "";
	$scope.monthsInYear = FrequencyFilterService.monthsInYear;

	// Function to place appropriate meta data from the month in the year
	$scope.selectMonthInYear = function (month) {
		$scope.publishFrequencySection.open = true;
		if (month) {
			$scope.selectedMonthsInYearText = ""; // Destroy text

			// Manage meta data
			var indexOfMonth = $scope.additionalMeta.repeat_month_iy.indexOf(month.id);
			if (indexOfMonth > -1) {
				$scope.additionalMeta.repeat_month_iy.splice(indexOfMonth,1);
			} else {
				$scope.additionalMeta.repeat_month_iy.push(month.id);
			}

			$scope.setSelectedMonthsInYearText($scope.selectedMonthsInYear);
		}
	};

	// Initialize array holding additional meta data for custom repeats
	$scope.additionalMeta = FrequencyFilterService.additionalMeta;


	// Function to return boolean for form completion
	$scope.checkForm = function () {
		if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100
			&& $scope.checkFrequencyTrigger())
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
