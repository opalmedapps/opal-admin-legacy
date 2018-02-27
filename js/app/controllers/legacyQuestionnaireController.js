angular.module('opalAdmin.controllers.legacyQuestionnaireController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular', 'multipleDatePicker', 'angularjs-dropdown-multiselect'])

.controller('legacyQuestionnaireController', function ($sce, $scope, $state, $filter, $timeout, $uibModal, legacyQuestionnaireCollectionService, filterCollectionService, uiGridConstants) {

	$scope.goToAddLegacyQuestionnaire = function () {
		$state.go('legacy-questionnaire-add');
	};

	$scope.changesMade = false;

		// Banner
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

		// Filter
		// search text-box param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN'].forEach(function (field) {
					if (row.entity[field].match(matcher)) {
						match = true;
					}
				});
				if (!match) {
					row.visible = false;
				}
			});
			return renderableRows;
		};

		// Function to filter questionnaires
		$scope.filterLegacyQuestionnaire = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		// Table
		// Templates
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
		'<strong><a href="" ng-click="grid.appScope.editLegacyQuestionnaire(row.entity)">Edit</a></strong> ' +
		'- <strong><a href="" ng-click="grid.appScope.deleteLegacyQuestionnaire(row.entity)">Delete</a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
		'ng-click="grid.appScope.editLegacyQuestionnaire(row.entity)">' +
		'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';
		var cellTemplatePublish = '<div style="text-align: center; cursor: pointer;" ' +
		'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
		'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
		'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'legacyQuestionnaireList',
			columnDefs: [
			{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '25%' },
			{
				field: 'publish', displayName: 'Publish', cellTemplate: cellTemplatePublish, width: '10%', filter: {
					type: uiGridConstants.filter.SELECT,
					selectOptions: [{ value: '1', label: 'Yes' }, { value: '0', label: 'No' }]
				}
			},
			{ field: 'expression', name: 'Legacy Questionnaire', filter: 'text'},
			{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize object for storing legacy questionnaires
		$scope.legacyQuestionnaireList = [];
		$scope.legacyQuestionnairePublishFlags = {
			flagList: []
		};

		// Call API to get the list of legacy questionnaires
		legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
			$scope.legacyQuestionnaireList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting legacy questionnaire list:', response.status, response.data);
		});	

		// When this function is called, we set the publish flags to checked 
		// or unchecked based on value in the argument
		$scope.updatePublishFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
		};


		// Function for when the publish checkbox has been modified
		$scope.checkPublishFlag = function (legacyQuestionnaire) {

			$scope.changesMade = true;
			legacyQuestionnaire.publish = parseInt(legacyQuestionnaire.publish);
			// If the "publish" column has been checked
			if (legacyQuestionnaire.publish) {
				legacyQuestionnaire.publish = 0; // set publish to "false"
			}

			// Else the "Publish" column was unchecked
			else {
				legacyQuestionnaire.publish = 1; // set publish to "true"
			}
		};


		// Function to submit changes when flags have been modified
		$scope.submitPublishFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.legacyQuestionnaireList, function (legacyQuestionnaire) {
					$scope.legacyQuestionnairePublishFlags.flagList.push({
						serial: legacyQuestionnaire.serial,
						publish: legacyQuestionnaire.publish
					});
				});
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/legacy-questionnaire/update.legacy_questionnaire_publish_flags.php",
					data: $scope.legacyQuestionnairePublishFlags,
					success: function (response) {
						// Call our API to get the list of existing legacy questionnaires
						legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
							// Assign value
							$scope.legacyQuestionnaireList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting legacy questionnaires:', response.status, response.data);
						});
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = "Flag(s) Successfully Saved!";
							$scope.legacyQuestionnairePublishFlags = {
								flagList: []
							};
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = response.message;
						}
						$scope.showBanner();
						$scope.changesMade = false;

					}
				});
}
};
		// Initialize the legacy questionnaire to be deleted
		$scope.legacyQuestionnaireToDelete = {};

		// Function to delete questionnaire
		$scope.deleteLegacyQuestionnaire = function (legacyQuestionnaire) {
			$scope.legacyQuestionnaireToDelete = legacyQuestionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'deleteLegacyQuestionnaireModalContent.htm',
				controller: DeleteLegacyQuestionnaireModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the legacy questionnaire list
			modalInstance.result.then(function () {
				legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
					$scope.legacyQuestionnaireList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting legacy questionnaire list after modal close:', response.status, response.data);
				});
			});
		};

		var DeleteLegacyQuestionnaireModalInstanceCtrl = function ($scope, $uibModalInstance) {
			
			// Submit delete
			$scope.deleteLegacyQuestionnaire = function () {
				$.ajax({
					type: "POST",
					url: "php/legacy-questionnaire/delete.legacy_questionnaire.php",
					data: $scope.legacyQuestionnaireToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.legacyQuestionnaireToDelete.name_EN + "/ " + $scope.legacyQuestionnaireToDelete.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}
						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			};

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};
		};

		// Initialize a scope variable for a selected legacy questionnaire
		$scope.currentLegacyQuestionnaire = {};

		// Function to edit legacy questionnaire
		$scope.editLegacyQuestionnaire = function (legacyQuestionnaire) {
			$scope.currentLegacyQuestionnaire = legacyQuestionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'editLegacyQuestionnaireModalContent.htm',
				controller: EditLegacyQuestionnaireModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the legacy questionnaire list
			modalInstance.result.then(function () {
				legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
					$scope.legacyQuestionnaireList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting legacy questionnaire list after modal close:', response.status, response.data);
				});
			});
		};

		// Controller for editModal
		var EditLegacyQuestionnaireModalInstanceCtrl = function ($scope, $uibModalInstance, $filter) {

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
				if ($scope.legacyQuestionnaire.name_EN && $scope.legacyQuestionnaire.name_FR && $scope.changesMade) {
					return true;
				}
				else
					return false;
			};

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
			$scope.showFrequency = true;

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
			$scope.$watch('legacyQuestionnaire.occurrence.start_date', function(startDate){
			    if (startDate !== undefined) { 
				    $scope.dateOptionsEnd.minDate = startDate;
				}
		  	});
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
			}

			// Initialize list of preset publishing frequencies
			$scope.presetFrequencies = [
			{
				name: 'Once',
				id: 'once',
				meta_key: 'repeat_day',
				meta_value: 0
			},{
				name: 'Every Day',
				id: 'every_day',
				meta_key: 'repeat_day',
				meta_value: 1
			},{
				name: 'Every Other Day',
				id: 'every_other_day',
				meta_key: 'repeat_day',
				meta_value: 2
			},{
				name: 'Every Week',
				id: 'every_week',
				meta_key: 'repeat_week',
				meta_value: 1
			},{
				name: 'Every 2 Weeks',
				id: 'every_2_weeks',
				meta_key: 'repeat_week',
				meta_value: 2
			},{
				name: 'Every Month',
				id: 'every_month',
				meta_key: 'repeat_month',
				meta_value: 1
			},{
				name: 'Custom',
				id: 'custom',
				meta_key: null,
				meta_value: null
			}];
			// Default
			$scope.frequencySelected = $scope.presetFrequencies[0];

			$scope.selectFrequency = function (frequency) {
				$scope.frequencySelected = frequency;
			}

			$scope.customFrequency = "Every";

			// Initialize object for repeat interval
			$scope.customMeta = {
				meta_value: 1,
				unit:null
			}
			$scope.frequencyUnits = [
			{
				name: 'Days',
				id: 'day',
				meta_key: 'repeat_day'
			},{
				name: 'Weeks',
				id: 'week',
				meta_key: 'repeat_week'
			},{
				name: 'Months',
				id: 'month',
				meta_key: 'repeat_month'
			},{
				name: 'Years',
				id: 'year',
				meta_key: 'repeat_year'
			}];
			// Default
			$scope.customMeta.unit = $scope.frequencyUnits[0];

			// Custom watch to singularize/pluralize frequency unit names
			$scope.$watch('customMeta.meta_value', function(newValue, oldValue){
			    if (newValue === 1) { 
				    angular.forEach($scope.frequencyUnits, function (unit) {
				    	unit.name = unit.name.slice(0,-1); // remove plural 's'
				    });
				}
				if (newValue > 1 && oldValue === 1) {
					angular.forEach($scope.frequencyUnits, function (unit) {
				    	unit.name = unit.name + 's'; // plural names
				    });
				}
		  	});

			// Default
			$scope.selectedDaysInWeek = [];
			$scope.selectedDaysInWeekText = "";
			// Initialize days of the week
			$scope.daysInWeek = [
			{
				name: 'Sunday',
				id: 1
			},{
				name: 'Monday',
				id: 2
			},{
				name: 'Tuesday',
				id: 3
			},{
				name: 'Wednesday',
				id: 4
			},{
				name: 'Thursday',
				id: 5
			},{
				name: 'Friday',
				id: 6
			},{
				name: 'Saturday',
				id: 7
			}];
			$scope.dayInWeek = null; // Default

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
				onItemSelect: function (day) {$scope.selectDayInWeek(day, $scope.customMeta.unit.id);},
				onItemDeselect: function (day) {$scope.selectDayInWeek(day, $scope.customMeta.unit.id);}
			}
			// Function when selecting the days on the week
			$scope.selectDayInWeek = function (day, unit) {
				$scope.dayInWeek = day;
				if (day) {
					$scope.selectedDaysInWeekText = "";
					if (unit == 'week') {
						var indexOfDay = $scope.additionalMeta.repeat_day_iw.indexOf(day.id);
						if (indexOfDay > -1) {
							$scope.additionalMeta.repeat_day_iw.splice(indexOfDay,1);
						} else {
							$scope.additionalMeta.repeat_day_iw.push(day.id);
						}
						for (var i = 0; i < $scope.selectedDaysInWeek.length; i++) {
							if ($scope.selectedDaysInWeek.length == 1) {
								$scope.selectedDaysInWeekText = $scope.selectedDaysInWeek[i].name;
							}
							else if (i < $scope.selectedDaysInWeek.length-1) {
								$scope.selectedDaysInWeekText += $scope.selectedDaysInWeek[i].name + ", "
							}
							else {
								$scope.selectedDaysInWeekText = $scope.selectedDaysInWeekText.slice(0,-2) + " and " + $scope.selectedDaysInWeek[i].name;
							}
						}
					}
					else if (unit == 'month' || unit == 'year') {
						
						if ($scope.weekInMonth) {
							$scope.additionalMeta.repeat_day_iw = [day.id];
							$scope.additionalMeta.repeat_week_im = [$scope.weekInMonth.id];	
							$scope.weekInMonthText = $scope.weekInMonth.name + " " + day.name;
						}
						else {
							$scope.additionalMeta.repeat_day_iw = [];
							$scope.additionalMeta.repeat_week_im = [];
							$scope.weekInMonthText = "";
						}
					}
				}
				else {
					if (unit == 'month' || unit == 'year') {
						$scope.additionalMeta.repeat_day_iw = [];
						$scope.additionalMeta.repeat_week_im = [];
						$scope.weekInMonthText = "";
					}
				}
			};

			$scope.selectRepeatInterval = function (unit) {
				if (unit.name != 'week') {
					$scope.selectedDaysInWeek = [];
					$scope.selectedDaysInWeekText = "";
					$scope.additionalMeta.repeat_day_iw = [];
				}
				if (unit.name != 'month') {
					$scope.additionalMeta.repeat_date_im = [];
					$scope.selectedDatesInMonthText = "";
					$scope.selectedDatesInMonth = [];
					$scope.repeatSub = null;
					$scope.additionalMeta.repeat_day_iw = [];
					$scope.additionalMeta.repeat_week_im = [];
					$scope.weekInMonthText = "";
					$scope.weekInMonth = $scope.weeksInMonth[0];
					$scope.dayInWeek = null;

				}
			}

			$scope.repeatSub = null;
			// Function to set the tab options for repeats onDate or onWeek
			$scope.setRepeatSub = function(repeatSub) {
				
				if ($scope.repeatSub != repeatSub) {
					$scope.repeatSub = repeatSub;
				} 
				else
					$scope.repeatSub = null; // remove reference/active

				if ($scope.repeatSub != 'onDate') {
					$scope.additionalMeta.repeat_date_im = [];
					$scope.selectedDatesInMonthText = "";
					$scope.selectedDatesInMonth = [];
				}
				if ($scope.repeatSub != 'onWeek') {
					$scope.additionalMeta.repeat_day_iw = [];
					$scope.additionalMeta.repeat_week_im = [];
					$scope.weekInMonthText = "";
					$scope.weekInMonth = $scope.weeksInMonth[0];
					$scope.dayInWeek = null;
				}
			}

			// Function watch to deal with selected dates
			$scope.selectedDatesInMonth = [];
			$scope.selectedDatesInMonthText = "";
			$scope.$watch('selectedDatesInMonth', function(newArray, oldArray){
			    if(newArray){
			    	$scope.additionalMeta.repeat_date_im = [];
					$scope.selectedDatesInMonthText = "";
			    	angular.forEach(newArray, function (date) {
			    		dateNumber = moment(date).get('date');
			    		$scope.additionalMeta.repeat_date_im.push(dateNumber);
			    	});

			    	$scope.additionalMeta.repeat_date_im.sort(function(a, b){return a - b});
			    	angular.forEach($scope.additionalMeta.repeat_date_im, function (dateNumber,index) {
			    		if (dateNumber % 10 == 1) {
			    			dateNumber += "st";
			    		}
			    		if (dateNumber % 10 == 2) {
			    			dateNumber += "nd";
			    		}
			    		if (dateNumber % 10 == 3) {
			    			dateNumber += "rd";
			    		}
			    		if (dateNumber % 10 > 3 || dateNumber % 10 == 0) {
			    			dateNumber += "th";
			    		}
			    		if (newArray.length == 1) {
								$scope.selectedDatesInMonthText = dateNumber;
						}
						else if (index < newArray.length-1) {
							$scope.selectedDatesInMonthText += dateNumber + ", "
						}
						else {
							$scope.selectedDatesInMonthText = $scope.selectedDatesInMonthText.slice(0,-2) + " and " + dateNumber;
						}
			    	});
			    }
			}, true);

			// initialize list of weeks in month
			$scope.weeksInMonth = [
			{
				name: '---',
				id: null
			},{
				name: '1st',
				id: 1
			},{
				name: '2nd',
				id: 2
			},{
				name: '3rd',
				id: 3
			},{
				name: '4th',
				id: 4
			},{
				name: '5th',
				id: 5
			},{
				name: 'Last',
				id: 6
			}];
			$scope.weekInMonth = $scope.weeksInMonth[0]; // Default
			$scope.weekInMonthText = "";

			// Function to set week of the month
			$scope.selectWeekInMonth = function (week) {
				$scope.weekInMonth = week;
				if (week.id && $scope.dayInWeek) {
					$scope.additionalMeta.repeat_day_iw = [$scope.dayInWeek.id];
					$scope.additionalMeta.repeat_week_im = [week.id];
					$scope.weekInMonthText = week.name + " " + $scope.dayInWeek.name;

				}
				else {
					$scope.additionalMeta.repeat_day_iw = [];
					$scope.additionalMeta.repeat_week_im = [];
					$scope.weekInMonthText = "";
				}
			}

			// Set month calendar to static date that starts on Sunday
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
			// event options for week dropdown menu
			$scope.monthDropdownEvents = {
				onItemSelect: function (month) {$scope.selectMonthInYear(month);},
				onItemDeselect: function (month) {$scope.selectMonthInYear(month);}
			}

			// Initialize list of months in a year
			$scope.selectedMonthsInYear = [];
			$scope.monthsInYear = [
			{
				name: 'January',
				id: 1
			},{
				name: 'February',
				id: 2
			},{
				name: 'March',
				id: 3
			},{
				name: 'April',
				id: 4
			},{
				name: 'May',
				id: 5
			},{
				name: 'June',
				id: 6
			},{
				name: 'July',
				id: 7
			},{
				name: 'August',
				id: 8
			},{
				name: 'September',
				id: 9
			},{ 
				name: 'October',
				id: 10
			},{
				name: 'November',
				id: 11
			},{
				name: 'December',
				id: 12
			}];

			// Function to place appropriate meta data from the month in the year
			$scope.selectMonthInYear = function (month) {
				if (month.id) {
					var indexOfMonth = $scope.additionalMeta.repeat_month_iy.indexOf(month.id);
					if (indexOfMonth > -1) {
						$scope.additionalMeta.repeat_month_iy.splice(indexOfMonth,1);
					} else {
						$scope.additionalMeta.repeat_month_iy.push(month.id);
					}
				}
			};

			$scope.additionalMeta = {
				repeat_day_iw: [],
				repeat_week_im: [],
				repeat_date_im: [],
				repeat_month_iy: []
			}

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


		};


	})

	.filter('range', function() {
	  	return function(input, total) {
	    	total = parseInt(total);

	    	for (var i=0; i<total; i++) {
	      		input.push(i);
	    	}

	    	return input;
		};
	});
