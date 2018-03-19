angular.module('opalAdmin.controllers.legacyQuestionnaire.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular', 'multipleDatePicker', 'angularjs-dropdown-multiselect']).

	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).
	controller('legacyQuestionnaire.edit', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, legacyQuestionnaireCollectionService, filterCollectionService, uiGridConstants, FrequencyFilterService, Session) {

		// initialize default variables & lists
		$scope.changesMade = false;
		$scope.legacyQuestionnaire = {};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize a list of sexes
		$scope.sexes = [
		{ name: 'Male' },
		{ name: 'Female' }
		];

		// Initialize to hold demographic filters
		$scope.demoFilter = {
			sex: null,
			age: {
				min: 0,
				max: 100
			}
		};

		// Initialize search field variables
		$scope.appointmentSearchField = "";
		$scope.dxSearchField = "";
		$scope.doctorSearchField = "";
		$scope.resourceSearchField = "";
		$scope.patientSearchField = "";

		// Function to assign search fields when textbox changes
		$scope.searchAppointment = function (field) {
			$scope.appointmentSearchField = field;
		};
		$scope.searchDiagnosis = function (field) {
			$scope.dxSearchField = field;
		};
		$scope.searchDoctor = function (field) {
			$scope.doctorSearchField = field;
		};
		$scope.searchResource = function (field) {
			$scope.resourceSearchField = field;
		};
		$scope.searchPatient = function (field) {
			$scope.patientSearchField = field;
		};

		// Function for search through the filters
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
		$scope.searchResourceFilter = function (Filter) {
			var keyword = new RegExp($scope.resourceSearchField, 'i');
			return !$scope.resourceSearchField || keyword.test(Filter.name);
		};
		$scope.searchPatientFilter = function (Filter) {
			var keyword = new RegExp($scope.patientSearchField, 'i');
			return !$scope.patientSearchField || keyword.test(Filter.name);
		};

		// Initialize lists to hold filters
		$scope.appointmentList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];
		$scope.appointmentStatusList = [];

		// Call our API service to get legacy questionnaire details
		legacyQuestionnaireCollectionService.getLegacyQuestionnaireDetails($scope.currentLegacyQuestionnaire.serial).then(function (response) {

			// Assign value
			$scope.legacyQuestionnaire = response.data;
			if ($scope.legacyQuestionnaire.occurrence.set) {

				$scope.showFrequency = true;

				$scope.legacyQuestionnaire.occurrence.start_date = new Date($scope.legacyQuestionnaire.occurrence.start_date*1000);
				if ($scope.legacyQuestionnaire.occurrence.end_date) {
					$scope.legacyQuestionnaire.occurrence.end_date = new Date($scope.legacyQuestionnaire.occurrence.end_date*1000);
					$scope.addEndDate = true;
				}
				var additionalMetaKeys = Object.keys($scope.legacyQuestionnaire.occurrence.frequency.additionalMeta);

				angular.forEach(additionalMetaKeys, function (key) {
					$scope.additionalMeta[key] = $scope.legacyQuestionnaire.occurrence.frequency.additionalMeta[key];
				});

				if ($scope.legacyQuestionnaire.occurrence.frequency.custom) { // custom frequency
					$scope.customFrequency.meta_value = $scope.legacyQuestionnaire.occurrence.frequency.meta_value;
					$scope.frequencySelected = $scope.presetFrequencies[$scope.presetFrequencies.length - 1];
					for (var i=0; i < $scope.frequencyUnits.length; i++) {
						if ($scope.frequencyUnits[i].meta_key == $scope.legacyQuestionnaire.occurrence.frequency.meta_key) {
							$scope.customFrequency.unit = $scope.frequencyUnits[i]; 
							break;
						}
					}
					$scope.setRepeatOptions($scope.legacyQuestionnaire.occurrence.frequency.additionalMeta, $scope.customFrequency.unit.id);
				} 
				else { // non-custom frequency (i.e preset frequency)
					for (var i=0; i < $scope.presetFrequencies.length; i++) {
						if ($scope.presetFrequencies[i].meta_key == $scope.legacyQuestionnaire.occurrence.frequency.meta_key
							&& $scope.presetFrequencies[i].meta_value == $scope.legacyQuestionnaire.occurrence.frequency.meta_value) {
							$scope.frequencySelected = $scope.presetFrequencies[i];
							break;
						}
					}
				}
				$scope.legacyQuestionnaire.occurrence.frequency.additionalMeta = [$scope.legacyQuestionnaire.occurrence.frequency.additionalMeta];

			}

		}).catch(function (response) {
			console.error('Error occurred getting legacy questionnaire details after modal open:', response.status, response.data);
			
		}).finally(function () {

			// Assign demographic filters
			checkDemographicFilters();

			// Call our API service to get each filter
			filterCollectionService.getFilters().then(function (response) {

				$scope.appointmentList = checkAddedFilter(response.data.appointments); // Assign value
				$scope.dxFilterList = checkAddedFilter(response.data.dx);
				$scope.doctorFilterList = checkAddedFilter(response.data.doctors);
				$scope.resourceFilterList = checkAddedFilter(response.data.resources);
				$scope.patientFilterList = checkAddedFilter(response.data.patients);
				$scope.appointmentStatusList = checkAddedFilter(response.data.appointmentStatuses);

			}).catch(function(response) {
				console.error('Error occurred getting filter list:', response.status, response.data);
			});

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
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

		}

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			$scope.changesMade = true;
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		// Function to toggle appointment status filter 
		$scope.appointmentStatusUpdate = function (index) {
			$scope.setChangesMade();
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

		// Function to assign '1' to existing filters 
		function checkAddedFilter(filterList) {
			angular.forEach($scope.legacyQuestionnaire.filters, function (selectedFilter) {
				var selectedFilterId = selectedFilter.id;
				var selectedFilterType = selectedFilter.type;
				angular.forEach(filterList, function (filter) {
					var filterId = filter.id;
					var filterType = filter.type;
					if (filterId == selectedFilterId && filterType == selectedFilterType) {
						filter.added = 1;
					}
				});
			});

			return filterList;
		}

		// Function to check demographic filters
		function checkDemographicFilters() {

			angular.forEach($scope.legacyQuestionnaire.filters, function (selectedFilter) {
				if (selectedFilter.type == 'Sex')
					$scope.demoFilter.sex = selectedFilter.id;
				if (selectedFilter.type == 'Age') {
					$scope.demoFilter.age.min = parseInt(selectedFilter.id.split(',')[0]);
					$scope.demoFilter.age.max = parseInt(selectedFilter.id.split(',')[1]);
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
			if ($scope.legacyQuestionnaire.name_EN && $scope.legacyQuestionnaire.name_FR && $scope.changesMade
				&& $scope.legacyQuestionnaire.intro_EN && $scope.legacyQuestionnaire.intro_FR
				&& $scope.checkFrequencyFilter()) {
				return true;
			}
			else
				return false;
		};

		// Function to check frequency filter forms are complete
		$scope.checkFrequencyFilter = function () {
			if ($scope.showFrequency) {
				if (!$scope.legacyQuestionnaire.occurrence.start_date || 
					($scope.addEndDate && !$scope.legacyQuestionnaire.occurrence.end_date) ) {
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

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.legacyQuestionnaire.filters.push({ id: Filter.id, type: Filter.type });
			});
		}

		// Function to check if all filters are added
		$scope.allFilters = function (filterList) {
			var allFiltersAdded = true;
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					allFiltersAdded = false;
			});
			return allFiltersAdded;
		};

		// Default boolean for showing frequency section details
		$scope.showFrequency = false;

		// Function for adding new frequency filter
		$scope.addFrequencyFilter = function () {
			$scope.showFrequency = true;
			$scope.setChangesMade();
			$scope.legacyQuestionnaire.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
			$scope.legacyQuestionnaire.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
		}

		// Function for removing new frequency filter
		$scope.removeFrequencyFilter = function () {
			$scope.showFrequency = false; // Hide form
			$scope.legacyQuestionnaire.occurrence.set = 0; // Not set anymore
			$scope.flushAllFrequencyFilters();
			$scope.setChangesMade();
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
			$scope.legacyQuestionnaire.occurrence.start_date = null;
			$scope.legacyQuestionnaire.occurrence.end_date = null;
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
		$scope.$watch('legacyQuestionnaire.occurrence.start_date', function(startDate){
		    if (startDate !== undefined) { 
			    $scope.dateOptionsEnd.minDate = startDate;
			}
	  	});
	  	// Watch to restrict the start calendar to not choose a start after the end date
	  	$scope.$watch('legacyQuestionnaire.occurrence.end_date', function(endDate){
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
				$scope.legacyQuestionnaire.occurrence.end_date = null;
			}
			$scope.setChangesMade();
		}

		// Initialize list of preset publishing frequencies
		$scope.presetFrequencies = FrequencyFilterService.presetFrequencies;
		$scope.frequencySelected = $scope.presetFrequencies[0]; // Default "Once"

		$scope.selectFrequency = function (frequency) {
			$scope.frequencySelected = frequency;
			$scope.setChangesMade();
			if (frequency.id != 'custom') {
				$scope.legacyQuestionnaire.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
				$scope.legacyQuestionnaire.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
				$scope.legacyQuestionnaire.occurrence.frequency.custom = 0;
				$scope.flushRepeatInterval();
				$scope.flushRepeatTypes();
			}
			else {
				$scope.legacyQuestionnaire.occurrence.frequency.custom = 1;
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
			$scope.setChangesMade();
		}

		$scope.repeatSub = null;
		// Function to set the tab options for repeats onDate or onWeek
		$scope.setRepeatSub = function(repeatSub) {
			$scope.setChangesMade();
			
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

		// Function for updating the legacy questionnaire 
		$scope.updateLegacyQuestionnaire = function () {

			if ($scope.checkForm()) {

				// Initialize filter
				$scope.legacyQuestionnaire.filters = [];

				// Add demographic filters, if defined
				if ($scope.demoFilter.sex)
					$scope.legacyQuestionnaire.filters.push({ id: $scope.demoFilter.sex, type: 'Sex' });
				if ($scope.demoFilter.age.min >= 0 && $scope.demoFilter.age.max <= 100) { // i.e. not empty
					if ($scope.demoFilter.age.min !== 0 || $scope.demoFilter.age.max != 100) { // Filters were changed
						$scope.legacyQuestionnaire.filters.push({
							id: String($scope.demoFilter.age.min).concat(',', String($scope.demoFilter.age.max)),
							type: 'Age'
						});
					}
				}

				// Add filters to legacy questionnaire
				addFilters($scope.appointmentList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);
				addFilters($scope.appointmentStatusList);

				// Add frequency filter if exists
				if ($scope.showFrequency) {
					$scope.legacyQuestionnaire.occurrence.set = 1;
					// convert dates to timestamps
					$scope.legacyQuestionnaire.occurrence.start_date = moment($scope.legacyQuestionnaire.occurrence.start_date).format('X');
					if ($scope.legacyQuestionnaire.occurrence.end_date) {
						$scope.legacyQuestionnaire.occurrence.end_date = moment($scope.legacyQuestionnaire.occurrence.end_date).format('X');
					}
					if ($scope.legacyQuestionnaire.occurrence.frequency.custom) {
						$scope.legacyQuestionnaire.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
						$scope.legacyQuestionnaire.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
						$scope.legacyQuestionnaire.occurrence.frequency.additionalMeta = [];
						angular.forEach(Object.keys($scope.additionalMeta), function(meta_key){
							if ($scope.additionalMeta[meta_key].length) {
								var metaDetails = {
									meta_key: meta_key,
									meta_value: $scope.additionalMeta[meta_key]
								}
								$scope.legacyQuestionnaire.occurrence.frequency.additionalMeta.push(metaDetails);
							}
						});
					} 
					else {
						$scope.legacyQuestionnaire.occurrence.frequency.additionalMeta = [];
					}
				}

				// Log who updated legacy questionnaire
				var currentUser = Session.retrieveObject('user');
				$scope.legacyQuestionnaire.user = currentUser;

				// ajax POST
				$.ajax({
					type: "POST",
					url: "php/legacy-questionnaire/update.legacy_questionnaire.php",
					data: $scope.legacyQuestionnaire,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.legacyQuestionnaire.name_EN + "/ " + $scope.legacyQuestionnaire.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}

						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			}
		};

	});