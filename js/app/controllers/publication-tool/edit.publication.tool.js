angular.module('opalAdmin.controllers.publication.tool.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).controller('publication.tool.edit', function ($scope, $state, $filter, $uibModal, $uibModalInstance, $locale, questionnaireCollectionService, filterCollectionService, FrequencyFilterService, Session) {

	// initialize default variables & lists
	$scope.changesMade = false;
	$scope.publishedQuestionnaire = {};
	$scope.language = Session.retrieveObject('user').language;

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

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');

	// Initialize a list of sexes
	// Initialize a list of sexes
	if ($scope.language.toUpperCase() === "FR")
		$scope.sexes = [
			{
				name: 'Male',
				display: "Homme",
				icon: 'male'
			}, {
				name: 'Female',
				display: "Femme",
				icon: 'female'
			}
		];
	else
		$scope.sexes = [
			{
				name: 'Male',
				display: "Male",
				icon: 'male'
			}, {
				name: 'Female',
				display: "Female",
				icon: 'female'
			}
		];

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

	// Initialize to hold demographic triggers
	$scope.demoTrigger = {
		sex: null,
		sex_display:null,
		age: {
			min: 0,
			max: 130
		}
	};

	$scope.projectText = {
		buttonDefaultText: $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.SELECT'),
	};

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

	// Initialize lists to hold triggers
	$scope.appointmentTriggerList = [];
	$scope.dxTriggerList = [];
	$scope.doctorTriggerList = [];
	$scope.machineTriggerList = [];
	$scope.patientTriggerList = [];
	$scope.appointmentStatusList = [];

	// Call our API service to get published questionnaire details
	questionnaireCollectionService.getPublishedQuestionnaireDetails($scope.currentPublishedQuestionnaire.serial, OAUserId).then(function (response) {

		// Assign value
		$scope.publishedQuestionnaire = response.data;
		if ($scope.publishedQuestionnaire.occurrence.set) {
			$scope.showFrequency = true;
			$scope.publishedQuestionnaire.occurrence.start_date = new Date($scope.publishedQuestionnaire.occurrence.start_date*1000);
			if ($scope.publishedQuestionnaire.occurrence.end_date) {
				$scope.publishedQuestionnaire.occurrence.end_date = new Date($scope.publishedQuestionnaire.occurrence.end_date*1000);
				$scope.addEndDate = true;
			}
			var additionalMetaKeys = Object.keys($scope.publishedQuestionnaire.occurrence.frequency.additionalMeta);

			angular.forEach(additionalMetaKeys, function (key) {
				$scope.additionalMeta[key] = $scope.publishedQuestionnaire.occurrence.frequency.additionalMeta[key];
			});

			if ($scope.publishedQuestionnaire.occurrence.frequency.custom) { // custom frequency
				$scope.customFrequency.meta_value = $scope.publishedQuestionnaire.occurrence.frequency.meta_value;
				$scope.frequencySelected = $scope.presetFrequencies[$scope.presetFrequencies.length - 1];
				for (var i=0; i < $scope.frequencyUnits.length; i++) {
					if ($scope.frequencyUnits[i].meta_key == $scope.publishedQuestionnaire.occurrence.frequency.meta_key) {
						$scope.customFrequency.unit = $scope.frequencyUnits[i];
						break;
					}
				}
				$scope.setRepeatOptions($scope.publishedQuestionnaire.occurrence.frequency.additionalMeta, $scope.customFrequency.unit.id);
			}
			else { // non-custom frequency (i.e preset frequency)
				for (var i=0; i < $scope.presetFrequencies.length; i++) {
					if ($scope.presetFrequencies[i].meta_key == $scope.publishedQuestionnaire.occurrence.frequency.meta_key
						&& $scope.presetFrequencies[i].meta_value == $scope.publishedQuestionnaire.occurrence.frequency.meta_value) {
						$scope.frequencySelected = $scope.presetFrequencies[i];
						break;
					}
				}
			}
			$scope.publishedQuestionnaire.occurrence.frequency.additionalMeta = [$scope.publishedQuestionnaire.occurrence.frequency.additionalMeta];

		}

	}).catch(function (response) {
		alert($filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.ERROR_DETAILS') + response.status + " " + response.data);
		$uibModalInstance.close();
	}).finally(function () {

		// Assign demographic triggers
		checkDemographicTriggers();

		// Call our API service to get each trigger
		filterCollectionService.getFilters().then(function (response) {

			response.data.appointments.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name;
			});
			response.data.dx.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name;
			});
			response.data.appointmentStatuses.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR") {

					switch(entry.name) {
						case "Open":
							entry.name_display = "Ouvert";
							break;
						case "Completed":
							entry.name_display = "Complété";
							break;
						case "Cancelled":
							entry.name_display = "Annulé";
							break;
						case "Checked In":
							entry.name_display = "Enregistré";
							break;
						default:
							entry.name_display = "Non traduit";
					}
				}
				else
					entry.name_display = entry.name;
			});

			$scope.appointmentTriggerList = checkAdded(response.data.appointments, $scope.selectAll.appointment); // Assign value
			$scope.dxTriggerList = checkAdded(response.data.dx, $scope.selectAll.diagnosis);
			$scope.doctorTriggerList = checkAdded(response.data.doctors, $scope.selectAll.doctor);
			$scope.machineTriggerList = checkAdded(response.data.machines, $scope.selectAll.machine);
			$scope.patientTriggerList = checkAdded(response.data.patients, $scope.selectAll.patient);
			$scope.appointmentStatusList = checkAdded(response.data.appointmentStatuses);
		}).catch(function(err) {
			alert($filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.ERROR_FILTERS') + err.status + " " + err.data);
			$uibModalInstance.close();
		}).finally(function () {
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});
	});

	// Function to set repeat options in frequency filter
	$scope.setRepeatOptions = function (additionalMeta, unit) {
		if (Object.keys(additionalMeta).indexOf('repeat_day_iw') > -1) {
			if (unit == 'week') {
				angular.forEach(additionalMeta.repeat_day_iw, function (day){
					$scope.selectedDaysInWeek.push($scope.daysInWeek[day - 1]);
				});
				$scope.setSelectedDaysInWeekText($scope.selectedDaysInWeek);
			}
			else {
				$scope.selectedSingleDayInWeek = $scope.daysInWeek[additionalMeta.repeat_day_iw[0] - 1];
				$scope.setSelectedSingleDayInWeekText($scope.selectedSingleDayInWeek);
			}
		}
		if (Object.keys(additionalMeta).indexOf('repeat_date_im') > -1) {
			angular.forEach(additionalMeta.repeat_date_im, function (date) {
				$scope.selectedDatesInMonth.push(moment().year(2018).month("January").date(date));
			});
			$scope.setRepeatSub('onDate');
			$scope.setSelectedDatesInMonthText($scope.selectedDatesInMonth);
		}
		if (Object.keys(additionalMeta).indexOf('repeat_week_im') > -1) {
			$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[additionalMeta.repeat_week_im[0]];
			if (unit == 'month') {
				$scope.setRepeatSub('onWeek');
			}
			$scope.setSelectedWeekNumberInMonthText($scope.selectedWeekNumberInMonth);
		}
		if (Object.keys(additionalMeta).indexOf('repeat_month_iy') > -1) {
			angular.forEach(additionalMeta.repeat_month_iy, function(month){
				$scope.selectedMonthsInYear.push($scope.monthsInYear[month-1]);
			});
			$scope.setSelectedMonthsInYearText($scope.selectedMonthsInYear);
		}

	};

	// Function to toggle trigger in a list on/off
	$scope.selectTrigger = function (trigger, selectAll) {
		$scope.changesMade = true;
		selectAll.all = false;
		selectAll.checked = false;
		$scope.publishedQuestionnaire.triggers_updated = 1;
		if (trigger.added)
			trigger.added = 0;
		else
			trigger.added = 1;
	};

	// Function for selecting all triggers in a trigger list
	$scope.selectAllTriggers = function (triggerList,triggerFilter,selectAll) {

		var filtered = $scope.filter(triggerList,triggerFilter);
		$scope.publishedQuestionnaire.triggers_updated = 1;
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


	// Function to toggle appointment status trigger 
	$scope.appointmentStatusUpdate = function (index) {
		$scope.setChangesMade();
		$scope.publishedQuestionnaire.triggers_updated = 1;
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

	// Function to assign 1 to existing triggers
	function checkAdded(triggerList, selectAll) {
		angular.forEach($scope.publishedQuestionnaire.triggers, function (selectedTrigger) {
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

	// Function to check demographic triggers
	function checkDemographicTriggers() {

		angular.forEach($scope.publishedQuestionnaire.triggers, function (selectedTrigger) {
			if (selectedTrigger.type == 'Sex')
				$scope.demoTrigger.sex = selectedTrigger.id;
			if (selectedTrigger.type == 'Age') {
				$scope.demoTrigger.age.min = parseInt(selectedTrigger.id.split(',')[0]);
				$scope.demoTrigger.age.max = parseInt(selectedTrigger.id.split(',')[1]);
			}
		});
	}

	// Function called whenever there has been a change in the form
	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	// Function to close edit modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		if ($scope.publishedQuestionnaire.name_EN && $scope.publishedQuestionnaire.name_FR && $scope.changesMade
			&& $scope.checkFrequencyTrigger())
			return true;
		else
			return false;
	};

	// Function to check frequency trigger forms are complete
	$scope.checkFrequencyTrigger = function () {
		if ($scope.showFrequency) {
			if (!$scope.publishedQuestionnaire.occurrence.start_date ||
				($scope.addEndDate && !$scope.publishedQuestionnaire.occurrence.end_date) ) {
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

	// Function to toggle necessary changes when updating the sex
	$scope.sexUpdate = function (sex) {

		if (!$scope.demoTrigger.sex) {
			$scope.demoTrigger.sex = sex.name;
			$scope.demoTrigger.sex_display = sex.display;
		} else if ($scope.demoTrigger.sex == sex.name) {
			$scope.demoTrigger.sex = null; // Toggle off
			$scope.demoTrigger.sex_display = null; // Toggle off
		} else {
			$scope.demoTrigger.sex = sex.name;
			$scope.demoTrigger.sex_display = sex.display;
		}
		$scope.setChangesMade();
		$scope.publishedQuestionnaire.triggers_updated = 1;

	};

	// Function to toggle necessary changes when updating the age 
	$scope.ageUpdate = function () {

		$scope.setChangesMade();
		$scope.publishedQuestionnaire.triggers_updated = 1;

	};

	$scope.detailsUpdated = function () {
		$scope.publishedQuestionnaire.details_updated = 1;
		$scope.setChangesMade();
	};

	// Function to return triggers that have been checked
	function addTriggers(triggerList, selectAll) {
		if (selectAll) {
			$scope.publishedQuestionnaire.triggers.push({id: 'ALL', type: triggerList[0].type});
		}
		else {
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					$scope.publishedQuestionnaire.triggers.push({ id: trigger.id, type: trigger.type });

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

	// Default boolean for showing frequency section details
	$scope.showFrequency = false;

	// Function for adding new frequency filter
	$scope.addFrequencyFilter = function () {
		$scope.showFrequency = true;
		$scope.setChangesMade();
		$scope.publishedQuestionnaire.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
		$scope.publishedQuestionnaire.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
	};

	// Function for removing new frequency filter
	$scope.removeFrequencyFilter = function () {
		$scope.showFrequency = false; // Hide form
		$scope.publishedQuestionnaire.occurrence.set = 0; // Not set anymore
		$scope.flushAllFrequencyFilters();
		$scope.setChangesMade();
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
		$scope.publishedQuestionnaire.occurrence.start_date = null;
		$scope.publishedQuestionnaire.occurrence.end_date = null;
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
	$scope.$watch('publishedQuestionnaire.occurrence.start_date', function(startDate){
		if (startDate !== undefined) {
			$scope.dateOptionsEnd.minDate = startDate;
		}
	});
	// Watch to restrict the start calendar to not choose a start after the end date
	$scope.$watch('publishedQuestionnaire.occurrence.end_date', function(endDate){
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
			$scope.publishedQuestionnaire.occurrence.end_date = null;
		}
		$scope.setChangesMade();
	};

	// Initialize list of preset publishing frequencies
	$scope.presetFrequencies = FrequencyFilterService.presetFrequencies;
	$scope.frequencySelected = $scope.presetFrequencies[0]; // Default "Once"

	$scope.selectFrequency = function (frequency) {
		$scope.frequencySelected = frequency;
		$scope.setChangesMade();
		if (frequency.id != 'custom') {
			$scope.publishedQuestionnaire.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
			$scope.publishedQuestionnaire.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
			$scope.publishedQuestionnaire.occurrence.frequency.custom = 0;
			$scope.flushRepeatInterval();
			$scope.flushRepeatTypes();
		}
		else {
			$scope.publishedQuestionnaire.occurrence.frequency.custom = 1;
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
					if (unit.name !== "Mois")
						unit.name = unit.name.slice(0,-1); // remove plural 's'
				});
			}
			else if (newValue > 1 && oldValue === 1) { // Was singular now plural
				angular.forEach($scope.frequencyUnits, function (unit) {
					if (unit.name !== "Mois")
						unit.name = unit.name + 's'; // pluralize words
				});
			}
		} else {
			if (newValue <= 1 && oldValue > 1) {
				angular.forEach($scope.frequencyUnits, function (unit) {
					if (unit.name !== "Mois")
						unit.name = unit.name.slice(0,-1); // remove plural 's'
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
				return $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.1_DAY_SELECTED');
			}
			return selectionArray.length + $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.DAYS_SELECTED');
		}
	};
	// event options for week dropdown menu
	$scope.weekDropdownEvents = {
		onItemSelect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);},
		onItemDeselect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);}
	};
	// Function when selecting the days on the week
	$scope.selectDayInWeek = function (day, unit) {
		$scope.setChangesMade();
		$scope.selectedSingleDayInWeek = day;
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
			if (days.length === 1) {
				// Eg. Sunday
				$scope.selectedDaysInWeekText = ($scope.language.toUpperCase() === "FR" ? days[i].name.toLowerCase() : days[i].name);
			}
			else if (i < days.length-1) {
				// Eg. Sunday, Monday, etc.
				$scope.selectedDaysInWeekText += ($scope.language.toUpperCase() === "FR" ? days[i].name.toLowerCase() : days[i].name) + ", ";
			}
			else {
				// Remove last comma and replace with "and"
				// Eg. Sunday, Monday and Tuesday
				$scope.selectedDaysInWeekText = $scope.selectedDaysInWeekText.slice(0,-2) + $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.AND') +
					($scope.language.toUpperCase() === "FR" ? $scope.selectedDaysInWeek[i].name.toLowerCase() : $scope.selectedDaysInWeek[i].name);
			}
		}
	};

	$scope.setSelectedWeekNumberInMonthText = function (week) {
		$scope.selectedWeekNumberInMonthText = week.name;

	};

	$scope.setSelectedSingleDayInWeekText = function (day) {
		$scope.selectedSingleDayInWeekText = ($scope.language.toUpperCase() === "FR" ? day.name.toLowerCase() : day.name);
	};

	$scope.setSelectedMonthsInYearText = function (months) {
		// Construct text for display of selected months
		for (var i = 0; i < months.length; i++) {
			// Single month
			// Eg. January
			if (months.length === 1) {
				$scope.selectedMonthsInYearText = ($scope.language.toUpperCase() === "FR" ? months[i].name.toLowerCase() : months[i].name);
			}
			// Concat months with commas
			// Eg. January, March, April
			else if (i < months.length-1) {
				$scope.selectedMonthsInYearText += ($scope.language.toUpperCase() === "FR" ? months[i].name.toLowerCase() : months[i].name) + ", ";
			}

			// Replace last comma with "and"
			// Eg. January, March and April
			else {
				$scope.selectedMonthsInYearText = $scope.selectedMonthsInYearText.slice(0,-2) + $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_ADD.AND')
					+ ($scope.language.toUpperCase() === "FR" ? $scope.selectedMonthsInYear[i].name.toLowerCase() : $scope.selectedMonthsInYear[i].name);
			}
		}

	};

	$scope.setSelectedDatesInMonthText = function (dates) {
		// Construct text for display of selected dates
		angular.forEach(dates, function (dateNumber,index) {
			// Conditionals for proper suffix
			if (dateNumber === 1)
				dateNumber += ($scope.language.toUpperCase() === "FR"?"er":"st");
			else if (dateNumber % 10 === 1 && dateNumber !== 11)
				dateNumber += ($scope.language.toUpperCase() !== "FR"?"st":"");
			else if (dateNumber % 10 === 2 && dateNumber !== 12)
				dateNumber += ($scope.language.toUpperCase() !== "FR"?"nd":"");
			else if (dateNumber % 10 === 3 && dateNumber !== 13)
				dateNumber += ($scope.language.toUpperCase() !== "FR"?"rd":"");
			else
				dateNumber += ($scope.language.toUpperCase() !== "FR"?"th":"");
			// Single date chosen
			// Eg. 4th
			if (dates.length === 1) {
				$scope.selectedDatesInMonthText = dateNumber;
			}
			// Concat commas 
			// Eg. 4th, 5th
			else if (index < dates.length-1)
				$scope.selectedDatesInMonthText += dateNumber + ", ";
			// Replace last comma with an "and"
			// Eg. 1st, 2nd and 4th
			else
				$scope.selectedDatesInMonthText = $scope.selectedDatesInMonthText.slice(0,-2) + $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.AND') + dateNumber;
		});
	};

	// Function when a repeat interval is selected
	$scope.selectRepeatInterval = function (unit) {
		if (unit.name !== 'week') { // Week wasn't selected
			// Remove week-related meta data
			$scope.selectedDaysInWeek = [];
			$scope.selectedDaysInWeekText = "";
			$scope.additionalMeta.repeat_day_iw = [];
		}
		if (unit.name !== 'month') { // Month wasn't selected
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
		if(unit.name !== 'year') { // Year wasn't selected
			// Remove year-related meta data
			$scope.additionalMeta.repeat_month_iy = [];
			$scope.selectedMonthsInYear = [];
			$scope.selectedMonthsInYearText = "";
		}
		$scope.setChangesMade();
	};

	$scope.repeatSub = null;
	// Function to set the tab options for repeats onDate or onWeek
	$scope.setRepeatSub = function(repeatSub) {
		$scope.setChangesMade();

		if ($scope.repeatSub !== repeatSub) {
			$scope.repeatSub = repeatSub; // set tab active
		}
		else
			$scope.repeatSub = null; // remove reference/active

		if ($scope.repeatSub !== 'onDate') { // date tab wasn't selected
			// Remove date-related meta data
			$scope.additionalMeta.repeat_date_im = [];
			$scope.selectedDatesInMonthText = "";
			$scope.selectedDatesInMonth = [];
		}
		if ($scope.repeatSub !== 'onWeek') { // week tab wasn't selected
			// Remove week-related meta data
			$scope.additionalMeta.repeat_day_iw = [];
			$scope.additionalMeta.repeat_week_im = [];
			$scope.selectedWeekNumberInMonthText = "";
			$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[0];
			$scope.selectedSingleDayInWeek = null;
		}

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
				dateNumber = moment(date).get('date');
				$scope.additionalMeta.repeat_date_im.push(dateNumber);
			});

			// Sort array
			$scope.additionalMeta.repeat_date_im.sort(function(a, b){return a - b});

			$scope.setSelectedDatesInMonthText($scope.additionalMeta.repeat_date_im);
		}
	}, true);

	// initialize list of weeks in month
	$scope.weekNumbersInMonth = FrequencyFilterService.weekNumbersInMonth;
	$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[0]; // Default null
	$scope.selectedWeekNumberInMonthText = "";

	// Function to set week of the month
	$scope.selectWeekInMonth = function (week) {
		$scope.setChangesMade();
		$scope.selectedWeekNumberInMonth = week;
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
			if (selectionArray.length === 1) {
				return $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.1_MONTH_SELECTED');
			}
			return selectionArray.length + $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.MONTHS_SELECTED');
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
		if (month) {
			$scope.setChangesMade();
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

	// Function for updating the published questionnaire
	$scope.updatePublishedQuestionnaire = function () {

		if ($scope.checkForm()) {
			// Initialize filter
			$scope.publishedQuestionnaire.triggers = [];

			// Add demographic triggers, if defined
			if ($scope.demoTrigger.sex)
				$scope.publishedQuestionnaire.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
			if ($scope.demoTrigger.age.min >= 0 && $scope.demoTrigger.age.max <= 130) { // i.e. not empty
				if ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max != 130) { // triggers were changed
					$scope.publishedQuestionnaire.triggers.push({
						id: String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max)),
						type: 'Age'
					});
				}
			}

			// Add triggers to published questionnaire
			addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
			addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
			addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
			addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
			addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);
			addTriggers($scope.appointmentStatusList);

			// Add frequency filter if exists
			if ($scope.showFrequency) {
				$scope.publishedQuestionnaire.occurrence.set = 1;
				// convert dates to timestamps
				$scope.publishedQuestionnaire.occurrence.start_date = moment($scope.publishedQuestionnaire.occurrence.start_date).format('X');
				if ($scope.publishedQuestionnaire.occurrence.end_date) {
					$scope.publishedQuestionnaire.occurrence.end_date = moment($scope.publishedQuestionnaire.occurrence.end_date).format('X');
				}
				if ($scope.publishedQuestionnaire.occurrence.frequency.custom) {
					$scope.publishedQuestionnaire.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
					$scope.publishedQuestionnaire.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
					$scope.publishedQuestionnaire.occurrence.frequency.additionalMeta = [];
					angular.forEach(Object.keys($scope.additionalMeta), function(meta_key){
						if ($scope.additionalMeta[meta_key].length) {
							var metaDetails = {
								meta_key: meta_key,
								meta_value: $scope.additionalMeta[meta_key]
							};
							$scope.publishedQuestionnaire.occurrence.frequency.additionalMeta.push(metaDetails);
						}
					});
				}
				else {
					$scope.publishedQuestionnaire.occurrence.frequency.additionalMeta = [];
				}
			}

			// Log who updated published questionnaire
			var currentUser = Session.retrieveObject('user');
			$scope.publishedQuestionnaire.OAUserId = currentUser.id;
			$scope.publishedQuestionnaire.sessionId = currentUser.sessionid;

			$.ajax({
				type: "POST",
				url: "publication-tool/update/published-questionnaire",
				data: $scope.publishedQuestionnaire,
				success: function (response) {
					response = JSON.parse(response);
					if (response.code === 200) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.SUCCESS_UPDATE');
						$scope.showBanner();
					}
					else
						alert($filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.ERROR_PUBLICATION'));
				},
				error: function () {
					alert($filter('translate')('QUESTIONNAIRE_MODULE.PUBLICATION_TOOL_EDIT.ERROR_PUBLICATION'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
	};

});