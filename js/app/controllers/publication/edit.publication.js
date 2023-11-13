angular.module('opalAdmin.controllers.publication.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).controller('publication.edit', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, publicationCollectionService, ScheduledTimeFilterService, FrequencyFilterService, Session, ErrorHandler) {

	// initialize default variables & lists
	$scope.toSubmit = {
		OAUserId: Session.retrieveObject('user').id,
		sessionid: Session.retrieveObject('user').sessionid,
		moduleId: {
			value: null,
		},
		materialId: {
			value: null,
			type: null,
		},
	};

	$scope.preview = {
		display: 1,
		publish_date: null,
		publish_time: null,
	};

	$scope.appointmentTime = $filter('translate')('PUBLICATION.EDIT.NO_FILTER');

	// Default boolean for showing frequency section details
	$scope.showFrequency = false;
	// Default boolean for showing scheduled time section
	$scope.showScheduledTimeDetails = false;

	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;
	$scope.frequencyChanged = false;

	$scope.validator = {
		triggers: {
			completed: true,
			mandatory: true,
		}
	};

	$scope.language = Session.retrieveObject('user').language;
	var firstYear = 2014;
	$scope.dateEntered = false;
	$scope.generalInfo = {
		name: null,
		module: null,
		description: null,
	};

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

	$scope.title = {available:false};
	$scope.publishDateTimeActive = false;
	$scope.publishDate = {available:false};
	$scope.publishFrequencySection = {available:false};
	$scope.triggerSection = {
		patient: {available:false, open:false, show:false},
		demo: {available:false, open:false, show:false},
		appointmentTime: {available:false,open:false, show:false, value: null},
		appointment: {available:false,open:false, show:false},
		doctor: {available:false,open:false, show:false},
		machine: {available:false,open:false, show:false},
		study: {available:false,open:false, show:false},
		diagnosis: {available:false,open:false, show:false}
	};

	$scope.toSubmit = {
		OAUserId: Session.retrieveObject('user').id,
		sessionid: Session.retrieveObject('user').sessionid,
		moduleId: {
			value: null,
		},
		materialId: {
			value: null,
			type: null,
		},
		triggers: [],
	};

	// Initialize to hold demographic triggers
	$scope.demoTrigger = {
		sex: null,
		sex_display:null,
		age: {
			min: 0,
			max: 130,
			valid: true,
		}
	};

	$scope.projectText = {
		buttonDefaultText: $filter('translate')('PUBLICATION.EDIT.SELECT'),
	};

	$scope.selectAll = {
		appointment: {all:false, checked:false},
		diagnosis: {all:false, checked:false},
		doctor: {all:false, checked:false},
		machine: {all:false, checked:false},
		study: {all:false, checked:false},
		patient: {all:false, checked:false}
	};

	// Initialize search field variables
	$scope.appointmentSearchField = "";
	$scope.dxSearchField = "";
	$scope.doctorSearchField = "";
	$scope.machineSearchField = "";
	$scope.studySearchField = "";
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
	$scope.searchStudy = function (field) {
		$scope.studySearchField = field;
		$scope.selectAll.study.all = false;
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
	$scope.searchStudyFilter = function (Filter) {
		var keyword = new RegExp($scope.studySearchField, 'i');
		return !$scope.studySearchField || keyword.test(Filter.name);
	};
	$scope.searchPatientFilter = function (Filter) {
		var keyword = new RegExp($scope.patientSearchField, 'i');
		return !$scope.patientSearchField || keyword.test(Filter.name);
	};

	// Initialize lists to hold triggers
	$scope.appointmentTriggerList = [];
	$scope.dxTriggerList = [];
	$scope.doctorTriggerList = [];
	$scope.studyTriggerList = [];
	$scope.patientTriggerList = [];
	$scope.appointmentTimeList = [];

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

	// Call our API service to get each trigger
	publicationCollectionService.getFilters(Session.retrieveObject('user').id).then(function (response) {
		response.data = angular.copy(response.data);
		response.data.appointments.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name;
		});

		$scope.appointmentTriggerList = response.data.appointments; // Assign value
		$scope.dxTriggerList = response.data.dx;
		$scope.dxTriggerList.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name;
		});

		$scope.doctorTriggerList = response.data.doctors;
		$scope.machineTriggerList = response.data.machines;
		$scope.studyTriggerList = response.data.studies;
		$scope.patientTriggerList = response.data.patients;
		$scope.appointmentTimeList = response.data.appointmentTimes;
		$scope.appointmentTimeList.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR") {

				switch(entry.name) {
					case "Scheduled Time":
						entry.name_display = "Temps prévu";
						break;
					case "Completed Time":
						entry.name_display = "Temps Complété";
						break;
					case "Cancelled Time":
						entry.name_display = "Temps Annulé";
						break;
					case "Checkin Time":
						entry.name_display = "Temps Enregistré";
						break;
					default:
						entry.name_display = "Non traduit";
				}
			}
			else
				entry.name_display = entry.name;
		});

		$scope.getThePublicationDetails();
		// Assign demographic triggers
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('PUBLICATION.EDIT.ERROR_FILTERS'));
		$uibModalInstance.close();
	}).finally(function () {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	$scope.getThePublicationDetails = function() {
		// Call our API service to get published publication details
		publicationCollectionService.getPublicationDetails($scope.currentPublication.ID, $scope.currentPublication.moduleId, OAUserId).then(function (response) {
			var ignore = ["publication", "publicationSettings"];
			for(var key in response.data) {
				if(ignore.indexOf(key) == -1)
					$scope.toSubmit[key] = response.data[key];
			}

			$scope.toSubmit.moduleId.value = $scope.currentPublication.moduleId;
			$scope.toSubmit.materialId.value = $scope.currentPublication.ID;
			$scope.toSubmit.materialId.type = response.data["publication"]["module"]["EN"];

			$scope.generalInfo.name = response.data["publication"]["name"][$scope.language.toUpperCase()];
			$scope.generalInfo.description = response.data["publication"]["description"][$scope.language.toUpperCase()];
			$scope.generalInfo.module = response.data["publication"]["module"][$scope.language.toUpperCase()];

			if (typeof $scope.toSubmit.occurrence !== 'undefined' ) {
				$scope.validator.occurrence = {
					completed: true,
					mandatory: true,
				};

				$scope.showFrequency = true;
				$scope.toSubmit.occurrence.start_date = new Date($scope.toSubmit.occurrence.start_date * 1000);
				if ($scope.toSubmit.occurrence.end_date) {
					$scope.toSubmit.occurrence.end_date = new Date($scope.toSubmit.occurrence.end_date * 1000);
					$scope.addEndDate = true;
				}
				var additionalMetaKeys = Object.keys($scope.toSubmit.occurrence.frequency.additionalMeta);

				angular.forEach(additionalMetaKeys, function (key) {
					$scope.additionalMeta[key] = $scope.toSubmit.occurrence.frequency.additionalMeta[key];
				});

				if ($scope.toSubmit.occurrence.frequency.custom) { // custom frequency
					$scope.customFrequency.meta_value = $scope.toSubmit.occurrence.frequency.meta_value;
					$scope.frequencySelected = $scope.presetFrequencies[$scope.presetFrequencies.length - 1];
					for (var i = 0; i < $scope.frequencyUnits.length; i++) {
						if ($scope.frequencyUnits[i].meta_key == $scope.toSubmit.occurrence.frequency.meta_key) {
							$scope.customFrequency.unit = $scope.frequencyUnits[i];
							break;
						}
					}
					$scope.setRepeatOptions($scope.toSubmit.occurrence.frequency.additionalMeta, $scope.customFrequency.unit.id);
				} else { // non-custom frequency (i.e preset frequency)
					for (var i = 0; i < $scope.presetFrequencies.length; i++) {
						if ($scope.presetFrequencies[i].meta_key == $scope.toSubmit.occurrence.frequency.meta_key
							&& $scope.presetFrequencies[i].meta_value == $scope.toSubmit.occurrence.frequency.meta_value) {
							$scope.frequencySelected = $scope.presetFrequencies[i];
							break;
						}
					}
				}
				$scope.toSubmit.occurrence.frequency.additionalMeta = [$scope.toSubmit.occurrence.frequency.additionalMeta];
			}

			if (typeof response.data["publication"]["subModule"] !== 'undefined' && response.data["publication"]["subModule"]["publishDateTime"])
				$scope.publishDateTimeActive = true;
			else
				$scope.publishDateTimeActive = false;

			if(typeof response.data["publication"]["subModule"] !== 'undefined') {
				if(response.data["publication"]["subModule"]["name_EN"] == "Announcement")
					$scope.preview.display = 3;
				else
					$scope.preview.display = 2;
			}
			$scope.publishFrequencySection.available = response.data["publicationSettings"].indexOf("1") !== -1 ? true: false;
			$scope.triggerSection.patient.available = response.data["publicationSettings"].indexOf("2") !== -1 ? true: false;
			$scope.triggerSection.demo.available = response.data["publicationSettings"].indexOf("3") !== -1 ? true: false;
			$scope.triggerSection.appointmentTime.available = response.data["publicationSettings"].indexOf("4") !== -1 ? true: false;
			$scope.triggerSection.appointment.available = response.data["publicationSettings"].indexOf("5") !== -1 ? true: false;
			$scope.triggerSection.diagnosis.available = response.data["publicationSettings"].indexOf("6") !== -1 ? true: false;
			$scope.triggerSection.doctor.available = response.data["publicationSettings"].indexOf("7") !== -1 ? true: false;
			$scope.triggerSection.machine.available = response.data["publicationSettings"].indexOf("8") !== -1 ? true: false;
			$scope.triggerSection.study.available = response.data["publicationSettings"].indexOf("10") !== -1 ? true: false;
			$scope.publishDate.available = (response.data["publicationSettings"].indexOf("9") !== -1) ? true: false;
			$scope.title.available = response.data["publication"]["unique"] !== "1" ? true: false; // Assign value

			checkAdded($scope.appointmentTriggerList, $scope.selectAll.appointment); // Assign value
			checkAdded($scope.dxTriggerList, $scope.selectAll.diagnosis);
			checkAdded($scope.doctorTriggerList, $scope.selectAll.doctor);
			checkAdded($scope.machineTriggerList, $scope.selectAll.machine);
			checkAdded($scope.studyTriggerList, $scope.selectAll.study);
			checkAdded($scope.patientTriggerList, $scope.selectAll.patient);
			checkAdded($scope.appointmentTimeList);

			$scope.appointmentTime = $filter('translate')('PUBLICATION.EDIT.NO_FILTER');
			angular.forEach($scope.appointmentTimeList, function (item){
				if(item.added) {
					$scope.appointmentTime = item.name_display;
					if ($scope.apptSelected != item.id) {
						$scope.apptSelected = item.id;
						// Open trigger by appointment time if already selected
						if(item.id == "Scheduled Time"){
							$scope.toSubmit.scheduledtime = {
								unit: $scope.presetUnits.find(unit => unit.id === item.ScheduledTimeUnit),
								direction: $scope.presetDirections.find(direction => direction.id === item.ScheduledTimeDirection),
								offset: parseInt(item.ScheduledTimeOffset)
							}
							$scope.selectedOffset = $scope.toSubmit.scheduledtime.offset;
							$scope.selectedDirection = $scope.toSubmit.scheduledtime.direction;
							$scope.selectedUnit = $scope.toSubmit.scheduledtime.unit;
							$scope.addScheduledTime();
						}
					}else
						$scope.apptSelected = null;
				}
			});

			if(response.data["publication"]["unique"] !== "1") {
				$scope.validator.name = {
					completed: true,
					mandatory: true,
				};
			}

			if ($scope.toSubmit.publishDateTime) {
				if($scope.toSubmit.publishDateTime !== "0000-00-00 00:00:00")
					$scope.dateEntered = true;
				else {
					$scope.dateEntered = false;
					$scope.preview.display = 1;
				}

				var publishDateTime = $scope.toSubmit.publishDateTime.split(" ");
				delete $scope.toSubmit.publishDateTime;
				$scope.toSubmit.publishDateTime = {
					publish_date: publishDateTime[0],
					publish_time: publishDateTime[1],
				};

				// Split the hours and minutes to display them in their respective text boxes
				var hours = $scope.toSubmit.publishDateTime.publish_time.split(":")[0];
				var minutes = $scope.toSubmit.publishDateTime.publish_time.split(":")[1];
				var d = new Date();
				d.setHours(hours);
				d.setMinutes(minutes);
				$scope.toSubmit.publishDateTime.publish_time = d;

				var year = $scope.toSubmit.publishDateTime.publish_date.split("-")[0];
				var month = parseInt($scope.toSubmit.publishDateTime.publish_date.split("-")[1]) - 1;
				var day = parseInt($scope.toSubmit.publishDateTime.publish_date.split("-")[2]);
				$scope.toSubmit.publishDateTime.publish_date = new Date(year, month, day);
				$scope.preview.publish_date = $scope.toSubmit.publishDateTime.publish_date;
				$scope.preview.publish_time = $scope.toSubmit.publishDateTime.publish_time;
			}
			if(!$scope.dateEntered || !$scope.publishDateTimeActive) {
				delete $scope.toSubmit.publishDateTime;
			}
			else {
				$scope.validator.publishDateTime = {
					completed: true,
					mandatory: true,
				};
			}

			checkDemographicTriggers();
			$scope.toSubmit.triggers = $scope.toSubmit.triggers;
			$scope.changesDetected = false;
			$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
		}).catch(function (response) {
			ErrorHandler.onError(err, $filter('translate')('PUBLICATION.EDIT.ERROR_DETAILS'));
			$uibModalInstance.close();
		});
	}

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
				$scope.selectedDatesInMonth.push(moment().year(firstYear).month("January").date(date));
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
	$scope.selectTrigger = function (trigger, selectAll, menu) {
		$scope.toSubmit.triggers_updated = 1;
		selectAll.all = false;
		selectAll.checked = false;
	};

	$scope.updateFrequency = function() {
		if ($scope.toSubmit.occurrence.frequency.custom) {
			$scope.toSubmit.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
			$scope.toSubmit.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
			$scope.toSubmit.occurrence.frequency.additionalMeta = [];
			angular.forEach(Object.keys($scope.additionalMeta), function(meta_key){
				if ($scope.additionalMeta[meta_key].length) {
					var metaDetails = {
						meta_key: meta_key,
						meta_value: $scope.additionalMeta[meta_key]
					};
					$scope.toSubmit.occurrence.frequency.additionalMeta.push(metaDetails);
				}
			});
		}
		else {
			$scope.toSubmit.occurrence.frequency.additionalMeta = [];
		}
	};

	//////////////////////////////////////////////////////////
	//WATCHERS SECTION
	//////////////////////////////////////////////////////////
	$scope.$watch('patientTriggerList', function (nv) {$scope.changeTriggers(nv);}, true);
	$scope.$watch('appointmentTriggerList', function (nv) {$scope.changeTriggers(nv);}, true);
	$scope.$watch('dxTriggerList', function (nv) {$scope.changeTriggers(nv);}, true);
	$scope.$watch('doctorTriggerList', function (nv) {$scope.changeTriggers(nv);}, true);
	$scope.$watch('machineTriggerList', function (nv) {$scope.changeTriggers(nv);}, true);
	$scope.$watch('studyTriggerList', function (nv) {$scope.changeTriggers(nv);}, true);

	$scope.$watch('toSubmit', function() {
		$scope.changesDetected = JSON.stringify($scope.toSubmit) != JSON.stringify($scope.oldData);
		$scope.validator.triggers.completed = ($scope.toSubmit.triggers.length > 0);
		try {
			$scope.validator.occurrence.completed = $scope.checkFrequencyTrigger();
		} catch(e) {}
	}, true);

	$scope.$watch('validator', function() {
		var totalsteps = 0;
		var completedSteps = 0;
		var nonMandatoryTotal = 0;
		var nonMandatoryCompleted = 0;
		angular.forEach($scope.validator, function(value) {
			if(value.mandatory)
				totalsteps++;
			else
				nonMandatoryTotal++;
			if(value.mandatory && value.completed)
				completedSteps++;
			else if(!value.mandatory && value.completed)
				nonMandatoryCompleted++;
		});
		$scope.formReady = (completedSteps >= totalsteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
	}, true);

	// Watch to restrict the end calendar to not choose an earlier date than the start date
	$scope.$watch('publication.occurrence.start_date', function(startDate){
		if (startDate !== undefined && startDate !== "")
			$scope.dateOptionsEnd.minDate = startDate;
		else
			$scope.dateOptionsEnd.minDate = null;
	});
	// Watch to restrict the start calendar to not choose a start after the end date
	$scope.$watch('publication.occurrence.end_date', function(endDate){
		if (endDate !== undefined && endDate !== "")
			$scope.dateOptionsStart.maxDate = endDate;
		else
			$scope.dateOptionsStart.maxDate = null;
	});

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

	//////////////////////////////////////////////////////////
	//SCOPE FUNCTIONS SECTION
	//////////////////////////////////////////////////////////
	$scope.changeTriggers = function(triggerList) {
		triggerList = angular.copy(triggerList);
		var pos = -1;
		angular.forEach(triggerList, function (item) {
			pos = $scope.toSubmit.triggers.findIndex(x => x.id === item.id && x.type === item.type);
			if(item.added) {
				if (pos == -1) {
					$scope.toSubmit.triggers.push({id: item.id, type: item.type});
				}
			}
			else {
				if (pos != -1) {
					$scope.toSubmit.triggers.splice(pos, 1);
				}
			}
		});
	}

	// Function for selecting all triggers in a trigger list
	$scope.toggleAllTriggers = function(triggerList,searchField,selectAll,menu) {
		$scope.toSubmit.triggers_updated = 1;
		var type = triggerList[0].type;
		var filtered = $scope.filter(triggerList,searchField);
		if (filtered.length === triggerList.length) { // search field wasn't used
			angular.forEach($scope.toSubmit.triggers, function(item) {
				if(item.type === type) {
					pos = $scope.toSubmit.triggers.findIndex(x => x.id === item.id && x.type === item.type);
					if (pos != -1) {
						$scope.toSubmit.triggers.splice(pos, 1);
					}
				}
			});
			angular.forEach(filtered, function (trigger) {trigger.added = false;});
			if (selectAll.checked) {
				selectAll.checked = false; // toggle off
				selectAll.all = false;
				menu.open = false;
			}
			else {
				selectAll.checked = true; // toggle on
				selectAll.all = true;
				menu.open = true;
				$scope.toSubmit.triggers.push({id: 'ALL', type: type});
			}
		} else {
			if (selectAll.checked) {
				selectAll.checked = false; // toggle off
				selectAll.all = false;
				menu.open = false;
				angular.forEach(filtered, function (trigger) {
					if ($scope.toSubmit.triggers.findIndex(x => x.id === trigger.id && x.type === trigger.type) === -1)
						$scope.toSubmit.triggers.splice($scope.toSubmit.triggers.findIndex(x => x.id === trigger.id && x.type === trigger.type), 1);
					trigger.added = false;
				});
			}
			else {
				selectAll.checked = true; // toggle on
				menu.open = true;
				angular.forEach(filtered, function (trigger) {
					if ($scope.toSubmit.triggers.findIndex(x => x.id === trigger.id && x.type === trigger.type) === -1) {
						$scope.toSubmit.triggers.push({id: trigger.id, type: trigger.type});
					}
					trigger.added = true;
				});
			}
		}
	};

	// Function to toggle appointment time trigger
	$scope.appointmentTimeUpdate = function (entrySelected) {
		$scope.toSubmit.triggers_updated = 1;
		var entryFound = false;
		angular.forEach($scope.appointmentTimeList, function(item){
			if (($scope.toSubmit.triggers.findIndex(x => x.id === item.id)) != -1) {
				// if trigger by appointment time is closed, reset its related variables
				if(item.id == "Scheduled Time"){
						$scope.removeScheduledTime();
					}
				$scope.toSubmit.triggers.splice($scope.toSubmit.triggers.findIndex(x => x.id === item.id), 1);
			} else {
				if (item.id == entrySelected.id) {
					// if the selected time is scheduled time show relevant fields
					if(item.id == "Scheduled Time"){
						$scope.addScheduledTime();
					}

					$scope.toSubmit.triggers.push({type: item.type, id: item.id});
					$scope.appointmentTime = entrySelected.name_display;
					entryFound = true;
				}
			}
		});

		if(!entryFound) {
			$scope.appointmentTime = $filter('translate')('PUBLICATION.EDIT.NO_FILTER');
		}


		if ($scope.apptSelected != entrySelected.id) {
			$scope.apptSelected = entrySelected.id;

		}
		else
			$scope.apptSelected = null;
	};

	// `Trigger by Appointment Time` functionality

	// function to be called when fields related to scheduled time are updated
	// takes type (field name) and the value of the field passed by the template
	$scope.appointmentScheduledTime = function (type, selectedValue) {
		$scope.toSubmit.triggers_updated = 1;
		if(type == "unit"){
			$scope.toSubmit.scheduledtime.unit = selectedValue;
			$scope.selectedUnit = selectedValue;
		}else if(type == "direction"){
			$scope.toSubmit.scheduledtime.direction = selectedValue;
			$scope.selectedDirection = selectedValue;
		} else{
			$scope.toSubmit.scheduledtime.offset = selectedValue;
			$scope.selectedOffset = selectedValue;
		}
		// only allow save when all fields are valid
		$scope.validator.scheduledtime.completed =($scope.selectedUnit && $scope.selectedOffset && $scope.selectedDirection);
	}

	// Initialize list of preset publishing scheduled time variables
	$scope.presetDirections = ScheduledTimeFilterService.presetDirections;
	$scope.presetUnits = ScheduledTimeFilterService.presetUnits;

	$scope.addScheduledTime = function () {
		$scope.prepareScheduledTime();
		$scope.showScheduledTimeDetails = true;
	};

	// Function for removing new scheduled time
	$scope.removeScheduledTime = function () {
		$scope.showScheduledTimeDetails = false; // Hide form
		$scope.selectedDirection = $scope.presetDirections[1];
		$scope.selectedUnit = '';
		$scope.selectedOffset = 0;
		// reset variable
		$scope.toSubmit.scheduledtime = {
			unit: '',
			direction: $scope.presetDirections[1],
			offset: 0
		};
		// delete validator when not selecting appointment scheduled time option
		delete $scope.validator.scheduledtime;

	};

	// function for initializing variables related to scheduled time
	$scope.prepareScheduledTime = function() {
		if (!$scope.toSubmit.scheduledtime) {
			// default it to `after` option.
			$scope.selectedDirection = $scope.presetDirections[1];
			$scope.selectedUnit = '';
			$scope.selectedOffset = 0;
			$scope.toSubmit.scheduledtime = {
				unit: '',
				direction: $scope.presetDirections[1],
				offset: 0
			};
		} else {
			$scope.toSubmit.scheduledtime = {
				unit: $scope.selectedUnit,
				direction: $scope.selectedDirection,
				offset: $scope.selectedOffset
			};
		}
		$scope.validator.scheduledtime = {
			completed: false,
			mandatory: false,
		};
	};

	// function to get the matching object of a key in a list of dictionaries
	function getObjectFromDictionary(key, list){
		for (var i = 0; i < list.length; i++){
			if (list[i].id ==key){
				return list[i];
			}
		}
	}

	// Function to close edit modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	// Function to check frequency trigger forms are complete
	$scope.checkFrequencyTrigger = function () {
		if ($scope.showFrequency) {
			if (!$scope.toSubmit.occurrence.start_date ||
				($scope.addEndDate && !$scope.toSubmit.occurrence.end_date) ) {
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

	// Function to toggle necessary changes when updating publication name
	$scope.nameUpdate = function () {
		$scope.validator.name.completed = ($scope.toSubmit.name.name_EN != "" && $scope.toSubmit.name.name_FR != "");
	};

	$scope.publishDateUpdate = function () {
		$scope.validator.publishDateTime.completed = $scope.toSubmit.publishDateTime.publish_date && $scope.toSubmit.publishDateTime.publish_time;
	};

	// Function to toggle necessary changes when updating the sex
	$scope.sexUpdate = function (sex) {
		$scope.toSubmit.triggers_updated = 1;
		if (!$scope.demoTrigger.sex) {
			$scope.demoTrigger.sex = sex.name;
			$scope.demoTrigger.sex_display = sex.display;
		} else if ($scope.demoTrigger.sex == sex.name) {
			$scope.demoTrigger.sex = null; // Toggle off
			$scope.demoTrigger.sex_display = null;
		} else {
			$scope.demoTrigger.sex = sex.name;
			$scope.demoTrigger.sex_display = sex.display;
		}

		if($scope.demoTrigger.sex) {
			var sexPresent = false;
			angular.forEach($scope.toSubmit.triggers, function (item) {
				if (item["type"] === "Sex") {
					sexPresent = true;
					item["id"] = $scope.demoTrigger.sex;
				}
			});
			if(!sexPresent) {
				$scope.toSubmit.triggers.push({id: $scope.demoTrigger.sex, type: 'Sex'});
			}
		} else {
			angular.forEach($scope.toSubmit.triggers, function (item, index, object) {
				if (item["type"] === "Sex") {
					object.splice(index, 1);
				}
			});
		}
	};

	// Function to toggle necessary changes when updating the age
	$scope.ageUpdate = function () {
		$scope.toSubmit.triggers_updated = 1;
		if($scope.demoTrigger.age.min == undefined || $scope.demoTrigger.age.max == undefined || $scope.demoTrigger.age.min > $scope.demoTrigger.age.max || $scope.demoTrigger.age.min < 0 || $scope.demoTrigger.age.max > 130)
			$scope.demoTrigger.age.valid = false;
		else
			$scope.demoTrigger.age.valid = true;

		if (($scope.demoTrigger.age.min === 0 && $scope.demoTrigger.age.max === 130) || !$scope.demoTrigger.age.valid) {
			if ($scope.toSubmit.triggers.findIndex(x => x.type === "Age") !== -1)
				$scope.toSubmit.triggers.splice($scope.toSubmit.triggers.findIndex(x => x.type === "Age"), 1);
		} else {
			var agePresent = false;
			angular.forEach($scope.toSubmit.triggers, function (item) {
				if (item["type"] === "Age") {
					agePresent = true;
					item["id"] = String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max));
				}
			});
			if(!agePresent) {
				$scope.toSubmit.triggers.push({
					id: String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max)),
					type: 'Age'
				});
			}
		}
	};

	//////////////////////////////////////////////////////////
	//REGULAR FUNCTIONS SECTION
	//////////////////////////////////////////////////////////
	// Function to assign 1 to existing triggers
	function checkAdded(triggerList, selectAll) {
		angular.forEach($scope.toSubmit.triggers, function (selectedTrigger) {
			angular.forEach(triggerList, function (trigger) {
				if(trigger.added == "1")
					trigger.added = true;
				else
					trigger.added = false;
				if (trigger.type == selectedTrigger.type) {
					if (selectedTrigger.id == 'ALL') {
						selectAll.all = true;
						selectAll.checked = true;
					}
					else if (trigger.id == selectedTrigger.id)
						trigger.added = true;
						// append the new variables offset, unit, and direction to the trigger object
						trigger.ScheduledTimeOffset = selectedTrigger.ScheduledTimeOffset;
						trigger.ScheduledTimeUnit = selectedTrigger.ScheduledTimeUnit;
						trigger.ScheduledTimeDirection = selectedTrigger.ScheduledTimeDirection;
				}
			});
		});
	}

	// Function to check demographic triggers
	function checkDemographicTriggers() {
		angular.forEach($scope.toSubmit.triggers, function (selectedTrigger) {
			if (selectedTrigger.type == 'Sex') {
				$scope.demoTrigger.sex = selectedTrigger.id;

				angular.forEach($scope.sexes, function (aSex) {
					if(aSex.name == selectedTrigger.id)
						$scope.demoTrigger.sex_display = aSex.display;
				});
			}
			else if (selectedTrigger.type == 'Age') {
				$scope.demoTrigger.age.min = parseInt(selectedTrigger.id.split(',')[0]);
				$scope.demoTrigger.age.max = parseInt(selectedTrigger.id.split(',')[1]);
			}
		});
	}

	// Function to return triggers that have been checked
	function addTriggers(triggerList, selectAll) {
		if (selectAll) {
			$scope.toSubmit.triggers.push({id: 'ALL', type: triggerList[0].type});
		}
		else {
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					$scope.toSubmit.triggers.push({ id: trigger.id, type: trigger.type });

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

	$scope.prepareFrequencyOccurrence = function() {
		if ($scope.toSubmit.occurrence != "undefined") {
			$scope.toSubmit.occurrence = {
				start_date: null,
				end_date: null,
				set: 0,
				frequency: {
					custom: 0,
					meta_key: null,
					meta_value: null,
					additionalMeta: []
				}
			};
			$scope.validator.occurrence = {
				completed: false,
				mandatory: false,
			};
		}
	};

	// Function for adding new frequency filter
	$scope.addFrequencyFilter = function () {
		$scope.prepareFrequencyOccurrence();
		$scope.showFrequency = true;
		$scope.toSubmit.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
		$scope.toSubmit.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
	};

	// Function for removing new frequency filter
	$scope.removeFrequencyFilter = function () {
		$scope.showFrequency = false; // Hide form
		$scope.toSubmit.occurrence.set = 0; // Not set anymore
		$scope.flushAllFrequencyFilters();
		$scope.deleteFrequencyOccurrence();
	};

	$scope.deleteFrequencyOccurrence = function() {
		delete $scope.toSubmit.occurrence;
		delete $scope.validator.occurrence;
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
		$scope.toSubmit.occurrence.start_date = null;
		$scope.toSubmit.occurrence.end_date = null;
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
			$scope.toSubmit.occurrence.end_date = null;
		}
	};

	// Initialize list of preset publishing frequencies
	$scope.presetFrequencies = FrequencyFilterService.presetFrequencies;
	$scope.frequencySelected = $scope.presetFrequencies[0]; // Default "Once"

	$scope.selectFrequency = function (frequency) {
		$scope.frequencySelected = frequency;
		if (frequency.id != 'custom') {
			$scope.toSubmit.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
			$scope.toSubmit.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
			$scope.toSubmit.occurrence.frequency.custom = 0;
			$scope.flushRepeatInterval();
			$scope.flushRepeatTypes();
		}
		else {
			$scope.toSubmit.occurrence.frequency.custom = 1;
		}
	};

	// Initialize object for repeat interval
	$scope.customFrequency = FrequencyFilterService.customFrequency;
	$scope.frequencyUnits = FrequencyFilterService.frequencyUnits;
	$scope.customFrequency.unit = $scope.frequencyUnits[0]; // Default "1 Day"

	$scope.resetAgeRange = function() {
		$scope.demoTrigger.age.max = 130;
		$scope.demoTrigger.age.min = 0;
		$scope.demoTrigger.age.valid = true;
		if ($scope.toSubmit.triggers.findIndex(x => x.type === "Age") !== -1)
			$scope.toSubmit.triggers.splice($scope.toSubmit.triggers.findIndex(x => x.type === "Age"), 1);
	}

	// Default
	$scope.selectedDaysInWeek = [];
	$scope.selectedDaysInWeekText = "";

	$scope.popup = {
		opened: false
	};

	// Open popup calendar
	$scope.open = function () {
		$scope.popup.opened = true;
	};

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
				return $filter('translate')('PUBLICATION.EDIT.1_DAY_SELECTED');
			}
			return selectionArray.length + $filter('translate')('PUBLICATION.EDIT.DAYS_SELECTED');
		}
	};
	// event options for week dropdown menu
	$scope.weekDropdownEvents = {
		onItemSelect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);},
		onItemDeselect: function (dayInWeek) {$scope.selectDayInWeek(dayInWeek, $scope.customFrequency.unit.id);}
	};
	// Function when selecting the days on the week
	$scope.selectDayInWeek = function (day, unit) {
		$scope.setFrequencyChangesMade();
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
				$scope.selectedDaysInWeekText = $scope.selectedDaysInWeekText.slice(0,-2) + $filter('translate')('PUBLICATION.EDIT.AND') +
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
				$scope.selectedMonthsInYearText = $scope.selectedMonthsInYearText.slice(0,-2) + $filter('translate')('PUBLICATION.EDIT.AND')
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
				$scope.selectedDatesInMonthText = $scope.selectedDatesInMonthText.slice(0,-2) + $filter('translate')('PUBLICATION.EDIT.AND') + dateNumber;
		});
	};

	// Function when a repeat interval is selected
	$scope.selectRepeatInterval = function (unit) {
		$scope.setFrequencyChangesMade();
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
	};

	$scope.repeatSub = null;
	// Function to set the tab options for repeats onDate or onWeek
	$scope.setRepeatSub = function(repeatSub) {
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

	// initialize list of weeks in month
	$scope.weekNumbersInMonth = FrequencyFilterService.weekNumbersInMonth;
	$scope.selectedWeekNumberInMonth = $scope.weekNumbersInMonth[0]; // Default null
	$scope.selectedWeekNumberInMonthText = "";

	// Function to set week of the month
	$scope.selectWeekInMonth = function (week) {
		$scope.setFrequencyChangesMade();
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
	$scope.staticMonth = moment().set({'year':firstYear, 'month': 0});

	// settings for month dropdown menu
	$scope.monthDropdownSettings = {
		displayProp: 'name',
		showCheckAll: false,
		showUncheckAll: false,
		styleActive: true,
		buttonClasses: 'btn btn-default btn-frequency-select',
		smartButtonTextProvider: function (selectionArray) {
			if (selectionArray.length === 1) {
				return $filter('translate')('PUBLICATION.EDIT.1_MONTH_SELECTED');
			}
			return selectionArray.length + $filter('translate')('PUBLICATION.EDIT.MONTHS_SELECTED');
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

	$scope.setFrequencyChangesMade = function () {
		$scope.frequencyChanged = true;
	};

	// Initialize array holding additional meta data for custom repeats
	$scope.additionalMeta = FrequencyFilterService.additionalMeta;

	// Function for updating the published questionnaire
	$scope.updatePublication = function () {
		// if not defined initialize it with default values
		if (!$scope.toSubmit.scheduledtime) {
			$scope.removeScheduledTime();
		}
		if($scope.formReady && $scope.changesDetected) {
			var invalidDate = false;

			var oldOccurrenceStart_date = null;
			var oldOccurrenceEnd_date = null;
			var oldOccurrenceFrequencyMeta_key = null;
			var oldOccurrenceFrequencyMeta_value = null;
			var oldPublishDate = null;
			var oldPublishTime = null;

			if ($scope.showFrequency) {
				$scope.toSubmit.occurrence.set = 1;
				oldOccurrenceStart_date = $scope.toSubmit.occurrence.start_date;
				$scope.toSubmit.occurrence.start_date = moment($scope.toSubmit.occurrence.start_date).format('X');
				if ($scope.toSubmit.occurrence.end_date) {
					oldOccurrenceEnd_date = $scope.toSubmit.occurrence.end_date;
					$scope.toSubmit.occurrence.end_date = moment($scope.toSubmit.occurrence.end_date).format('X');
				}
				$scope.toSubmit.occurrence.frequency.additionalMeta = [];
				if ($scope.toSubmit.occurrence.frequency.custom) {
					oldOccurrenceFrequencyMeta_key = $scope.toSubmit.occurrence.frequency.meta_key;
					$scope.toSubmit.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
					oldOccurrenceFrequencyMeta_value = $scope.toSubmit.occurrence.frequency.meta_value;
					$scope.toSubmit.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
					angular.forEach(Object.keys($scope.additionalMeta), function (meta_key) {
						if ($scope.additionalMeta[meta_key].length) {
							var metaDetails = {
								meta_key: meta_key,
								meta_value: $scope.additionalMeta[meta_key]
							};
							$scope.toSubmit.occurrence.frequency.additionalMeta.push(metaDetails);
						}
					});
				}
			}

			if ($scope.publishDate.available) {
				if (typeof $scope.toSubmit.publishDateTime !== "undefined") {
					oldPublishTime = $scope.toSubmit.publishDateTime.publish_time;
					oldPublishDate = $scope.toSubmit.publishDateTime.publish_date;

					var tempDate = String(moment($scope.toSubmit.publishDateTime.publish_date).format("YYYY-MM-DD")) + " " +
						String(moment($scope.toSubmit.publishDateTime.publish_time).format("HH:mm"));
					delete $scope.toSubmit.publishDateTime;
					$scope.toSubmit.publishDateTime = tempDate;
					if (((new Date()).getTime() - Date.parse($scope.toSubmit.publishDateTime)) >= 0)
						invalidDate = true;
				}
			}

			if (invalidDate) {
				alert($filter('translate')('PUBLICATION.EDIT.ERROR_DATE'));
				if ($scope.showFrequency) {
					$scope.toSubmit.occurrence.start_date = oldOccurrenceStart_date;
					$scope.toSubmit.occurrence.end_date = oldOccurrenceEnd_date;
					$scope.toSubmit.occurrence.frequency.meta_key = oldOccurrenceFrequencyMeta_key;
					$scope.toSubmit.occurrence.frequency.meta_value = oldOccurrenceFrequencyMeta_value;
				}
				if ($scope.publishDate.available) {
					$scope.toSubmit.publishDateTime = {
						publish_date: oldPublishDate,
						publish_time: oldPublishTime,
					};
				}
			} else {
				$.ajax({
					type: "POST",
					url: "publication/update/publication",
					data: $scope.toSubmit,
					success: function () {
					},
					error: function (err) {
						ErrorHandler.onError(err, $filter('translate')('PUBLICATION.EDIT.ERROR_PUBLICATION'));
					},
					complete: function () {
						$uibModalInstance.close();
					}
				});
			}
		}
	};

});