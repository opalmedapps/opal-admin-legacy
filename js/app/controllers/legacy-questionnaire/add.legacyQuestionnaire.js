angular.module('opalAdmin.controllers.legacyQuestionnaire.add', ['ngAnimate', 'ui.bootstrap', 'ngSanitize']).

	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).

	/******************************************************************************
	* New Legacy Questionnaire Page controller 
	*******************************************************************************/
	controller('legacyQuestionnaire.add', function($scope, $filter, $uibModal, $sce, legacyQuestionnaireCollectionService, $state, filterCollectionService, FrequencyFilterService, Session) {
       
       // Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.legacyQuestionnaireSection = {open:false, show:true};
		$scope.titleSection = {open:false, show:false};
		$scope.introSection = {open:false, show:false};
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

		// completed steps boolean object; used for progress bar
		var steps = {
			title: { completed: false },
			legacy_questionnaire: { completed: false },
			intro: {completed: false}
		};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize search field variables
		$scope.appointmentSearchField = null;
		$scope.dxSearchField = null;
		$scope.doctorSearchField = null;
		$scope.machineSearchField = null;
		$scope.patientSearchField = null;

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 3;

		// Progress for progress bar on default steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Initialize new legacy questionnaire object
		$scope.newLegacyQuestionnaire = {
			name_EN: null,
			name_FR: null,
			intro_EN: null,
			intro_FR: null,
			legacy_questionnaire: null,
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

		// Initialize list that will hold legacy questionnaires
		$scope.legacyQuestionnaireList = [];

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
		}

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

		// Call our API service to get the list of legacy questionnaires
		legacyQuestionnaireCollectionService.getLegacyQuestionnaireExpressions().then(function (response) {
			$scope.legacyQuestionnaireList = response.data; // Assign value
		}).catch(function(response) {
			console.error('Error occurred getting educational materials:', response.status, response.data);
		});

		// Call our API service to get each trigger
		filterCollectionService.getFilters().then(function (response) {

			$scope.appointmentTriggerList = response.data.appointments; // Assign value
			$scope.dxTriggerList = response.data.dx;
			$scope.doctorTriggerList = response.data.doctors;
			$scope.machineTriggerList = response.data.machines;
			$scope.patientTriggerList = response.data.patients;
			$scope.appointmentStatusList = response.data.appointmentStatuses;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

			$scope.formLoaded = true;
			$scope.loadForm();

		}).catch(function(response) {
			console.error('Error occurred getting filter list:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating the legacy questionnaire
		$scope.legacyQuestionnaireUpdate = function () {

			$scope.legacyQuestionnaireSection.open = true;

			$scope.titleSection.show = true;

			// Toggle step completion
			steps.legacy_questionnaire.completed = true;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};


		// Function to toggle necessary changes when updating post name
		$scope.titleUpdate = function () {

			$scope.titleSection.open = true;

			if ($scope.newLegacyQuestionnaire.name_EN && $scope.newLegacyQuestionnaire.name_FR) {

				$scope.introSection.show = true;

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

		// Function to toggle necessary changes when updating the intro section
		$scope.introUpdate = function () {

			$scope.introSection.open = true;

			if ($scope.newLegacyQuestionnaire.intro_EN && $scope.newLegacyQuestionnaire.intro_FR) {

				$scope.demoSection.show = true;
				$scope.triggerSection.show = true;
				$scope.publishFrequencySection.show = true;

				// Toggle step completion
				steps.intro.completed = true;
			}
			else {
				// Toggle step completion
				steps.intro.completed = false;
			}
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}

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

		// Function to return triggers that have been checked
		function addTriggers(triggerList, selectAll) {
			if(selectAll) {
				$scope.newLegacyQuestionnaire.triggers.push({id: 'ALL', type: triggerList[0].type});
			}
			else {
				angular.forEach(triggerList, function (trigger) {
					if (trigger.added)
						$scope.newLegacyQuestionnaire.triggers.push({ id: trigger.id, type: trigger.type });
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
				if (!$scope.newLegacyQuestionnaire.occurrence.start_date || 
					($scope.addEndDate && !$scope.newLegacyQuestionnaire.occurrence.end_date) ) {
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
		}

		// Default boolean for showing frequency section details
		$scope.showFrequency = false;

		// Function for adding new frequency filter
		$scope.addFrequencyFilter = function () {
			$scope.showFrequency = true;
			$scope.newLegacyQuestionnaire.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
			$scope.newLegacyQuestionnaire.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
			$scope.publishFrequencySection.open = true;
		}

		// Function for removing new frequency filter
		$scope.removeFrequencyFilter = function () {
			$scope.showFrequency = false; // Hide form
			$scope.newLegacyQuestionnaire.occurrence.set = 0; // Not set anymore
			$scope.flushAllFrequencyFilters();
			$scope.publishFrequencySection.open = false;
		}

		// Function to reset all frequency filters
		$scope.flushAllFrequencyFilters = function () {
			$scope.flushPresetFrequency();
			$scope.flushRepeatDates();
			$scope.flushRepeatInterval();
			$scope.flushRepeatTypes();
		}

		// Function to reset the preset frequency
		$scope.flushPresetFrequency = function () {
			$scope.frequencySelected = $scope.presetFrequencies[0];
		}

		// Function to reset repeat dates
		$scope.flushRepeatDates = function () {
			$scope.newLegacyQuestionnaire.occurrence.start_date = null;
			$scope.newLegacyQuestionnaire.occurrence.end_date = null;
		}

		// Function to reset repeat interval
		$scope.flushRepeatInterval = function () {
			$scope.customFrequency = jQuery.extend(true, {}, FrequencyFilterService.customFrequency);
			$scope.customFrequency.unit = $scope.frequencyUnits[0];
			$scope.customFrequency.meta_value = 1;
		}

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
		}

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
		$scope.$watch('newLegacyQuestionnaire.occurrence.start_date', function(startDate){
		    if (startDate !== undefined) { 
			    $scope.dateOptionsEnd.minDate = startDate;
			}
	  	});
	  	// Watch to restrict the start calendar to not choose a start after the end date
	  	$scope.$watch('newLegacyQuestionnaire.occurrence.end_date', function(endDate){
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
				$scope.newLegacyQuestionnaire.occurrence.end_date = null;
			}
			$scope.publishFrequencySection.open = true;
		}

		// Initialize list of preset publishing frequencies
		$scope.presetFrequencies = FrequencyFilterService.presetFrequencies;
		$scope.frequencySelected = $scope.presetFrequencies[0]; // Default "Once"

		$scope.selectFrequency = function (frequency) {
			$scope.frequencySelected = frequency;
			$scope.publishFrequencySection.open = true;
			if (frequency.id != 'custom') {
				$scope.newLegacyQuestionnaire.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
				$scope.newLegacyQuestionnaire.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
				$scope.newLegacyQuestionnaire.occurrence.frequency.custom = 0;
				$scope.flushRepeatInterval();
				$scope.flushRepeatTypes();
			}
			else {
				$scope.newLegacyQuestionnaire.occurrence.frequency.custom = 1;
			}
		}

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
		}
		// event options for week dropdown menu
		$scope.weekDropdownEvents = {
			onItemSelect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);},
			onItemDeselect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);}
		}
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
					$scope.selectedDaysInWeekText += days[i].name + ", "
				}
				else {
					// Remove last comma and replace with "and"
					// Eg. Sunday, Monday and Tuesday
					$scope.selectedDaysInWeekText = $scope.selectedDaysInWeekText.slice(0,-2) + " and " + $scope.selectedDaysInWeek[i].name;
				}
			}
		}

		$scope.setSelectedWeekNumberInMonthText = function (week) {
			$scope.selectedWeekNumberInMonthText = week.name;

		}

		$scope.setSelectedSingleDayInWeekText = function (day) {
			$scope.selectedSingleDayInWeekText = day.name;
		}

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
					$scope.selectedMonthsInYearText += months[i].name + ", "
				}

				// Replace last comma with "and"
				// Eg. January, March and April
				else {
					$scope.selectedMonthsInYearText = $scope.selectedMonthsInYearText.slice(0,-2) + " and " + $scope.selectedMonthsInYear[i].name;
				}
			}

		}

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
					$scope.selectedDatesInMonthText += dateNumber + ", "
				}
				// Replace last comma with an "and"
				// Eg. 1st, 2nd and 4th
				else {
					$scope.selectedDatesInMonthText = $scope.selectedDatesInMonthText.slice(0,-2) + " and " + dateNumber;
				}
	    	});
		}

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
				$scope.additionalMeta.repeat_month_iy = []
				$scope.selectedMonthsInYear = [];
				$scope.selectedMonthsInYearText = "";
			}
			$scope.publishFrequencySection.open = true;
		}

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
		}

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
		}

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
		}
		// event options for month dropdown menu
		$scope.monthDropdownEvents = {
			onItemSelect: function (month) {$scope.selectMonthInYear(month);},
			onItemDeselect: function (month) {$scope.selectMonthInYear(month);}
		}

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

		// Function to submit the new legacy questionnaire
		$scope.submitLegacyQuestionnaire = function () {
			if ($scope.checkForm()) {

				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.newLegacyQuestionnaire.intro_EN = $scope.newLegacyQuestionnaire.intro_EN.replace(/\u200B/g,'');
				$scope.newLegacyQuestionnaire.intro_FR = $scope.newLegacyQuestionnaire.intro_FR.replace(/\u200B/g,'');

				// Add demographic triggers, if defined
				if ($scope.demoTrigger.sex)
					$scope.newLegacyQuestionnaire.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
				if ($scope.demoTrigger.age.min >= 0 && $scope.demoTrigger.age.max <= 100) { // i.e. not empty
					if ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max != 100) { // triggers were changed
						$scope.newLegacyQuestionnaire.triggers.push({
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
					$scope.newLegacyQuestionnaire.occurrence.set = 1;
					// convert dates to timestamps
					$scope.newLegacyQuestionnaire.occurrence.start_date = moment($scope.newLegacyQuestionnaire.occurrence.start_date).format('X');
					if ($scope.newLegacyQuestionnaire.occurrence.end_date) {
						$scope.newLegacyQuestionnaire.occurrence.end_date = moment($scope.newLegacyQuestionnaire.occurrence.end_date).format('X');
					}
					if ($scope.newLegacyQuestionnaire.occurrence.frequency.custom) {
						$scope.newLegacyQuestionnaire.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
						$scope.newLegacyQuestionnaire.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
						$scope.newLegacyQuestionnaire.occurrence.frequency.additionalMeta = [];
						angular.forEach(Object.keys($scope.additionalMeta), function(meta_key){
							if ($scope.additionalMeta[meta_key].length) {
								var metaDetails = {
									meta_key: meta_key,
									meta_value: $scope.additionalMeta[meta_key]
								}
								$scope.newLegacyQuestionnaire.occurrence.frequency.additionalMeta.push(metaDetails);
							}
						});
					} 
					else {
						$scope.newLegacyQuestionnaire.occurrence.frequency.additionalMeta = [];
					}
				}

				// Log who created legacy questionnaire
				var currentUser = Session.retrieveObject('user');
				$scope.newLegacyQuestionnaire.user = currentUser;

				// Submit 
				$.ajax({
					type: "POST",
					url: "php/legacy-questionnaire/insert.legacy_questionnaire.php",
					data: $scope.newLegacyQuestionnaire,
					success: function () {
						$state.go('legacy-questionnaire');
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

