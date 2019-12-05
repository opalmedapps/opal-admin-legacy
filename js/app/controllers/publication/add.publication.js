angular.module('opalAdmin.controllers.publication.add', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker', 'textAngular', 'multipleDatePicker', 'angularjs-dropdown-multiselect']).

	/******************************************************************************
	 * Add Publication Page controller
	 *******************************************************************************/
	controller('publication.add', function ($scope, $filter, $uibModal, $state, $locale, publicationCollectionService, Session, filterCollectionService, FrequencyFilterService ) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};


		// Default boolean variables
		// $scope.selectAll = false; // select All button checked?

		$scope.selectAll = {
			appointment: {all:false, checked:false},
			diagnosis: {all:false, checked:false},
			doctor: {all:false, checked:false},
			machine: {all:false, checked:false},
			patient: {all:false, checked:false}
		};

		$scope.moduleSection = {open:false, show:true};
		$scope.materialSection = {open:false, show:false};
		$scope.publicationNameSections = {open:false, show:false};
		$scope.publishDate = {available:false, open:false, show:false, mandatory:false};
		$scope.publishFrequencySection = {available:false, open: false, show:false};
		$scope.triggerSection = {
			show:false,
			patient: {available:false,open:false, show:false},
			demo: {available:false, open:false, show:false},
			appointmentStatus: {available:false,open:false, show:false, value: null},
			appointment: {available:false,open:false, show:false},
			doctor: {available:false,open:false, show:false},
			machine: {available:false,open:false, show:false},
			diagnosis: {available:false,open:false, show:false}
		};

		$scope.language = Session.retrieveObject('user').language;

		$scope.showAssigned = false;
		$scope.hideAssigned = false;

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

		// Initialize lists to hold triggers
		// Initialize search field variables
		$scope.appointmentSearchField = null;
		$scope.dxSearchField = null;
		$scope.doctorSearchField = null;
		$scope.machineSearchField = null;
		$scope.patientSearchField = null;

		$scope.patientTriggerList = [];

		$scope.appointmentStatusList = [];
		$scope.appointmentTriggerList = [];
		$scope.dxTriggerList = [];
		$scope.doctorTriggerList = [];
		$scope.machineTriggerList = [];

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

		// Initialize search field variables
		$scope.atEntered = '';

		// Default toolbar for wysiwyg
		$scope.toolbar = [
			['h1', 'h2', 'h3', 'p'],
			['bold', 'italics', 'underline', 'ul', 'ol'],
			['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
			['html', 'insertLink']
		];

		// completed steps in object notation
		var steps = {
			type: { completed: false },
			material: { completed: false },
			publicationName: { completed: false },
		};

		$scope.filter = $filter('filter');

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps
		$scope.stepTotal = 3;

		// Progress bar based on default completed steps and total
		//$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		$scope.stepProgress = 0;

		$scope.publicationList = [];

		// Initialize list that will hold source databases
		$scope.moduleList = [];

		/* Function for the "Processing" dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};

		// Call our API service to get each trigger
		filterCollectionService.getFilters().then(function (response) {

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
			$scope.patientTriggerList = response.data.patients;
			$scope.appointmentStatusList = response.data.appointmentStatuses;
			$scope.appointmentStatusList.forEach(function(entry) {
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
		}).catch(function(err) {
			alert($filter('translate')('PUBLICATION.ADD.ERROR_FILTERS') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.data));
			$state.go('publication');
		});

		// Call our API service to get the list of source databases
		publicationCollectionService.getPublicationModules(Session.retrieveObject('user').id).then(function (response) {
			response.data.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name_EN;
			});
			$scope.moduleList = response.data; // Assign value
		}).catch(function(err) {
			alert($filter('translate')('PUBLICATION.ADD.ERROR_DATABASE') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.data));
			$state.go('publication');
		});

		function initialization() {
			$scope.unique = false;
			$scope.apptSelected = null;

			$scope.demoTrigger = {
				sex: null,
				age: {
					min: 0,
					max: 130
				}
			};

			$scope.toSubmit = {
				moduleId: {
					value: null,
				},
				materialId: {
					value: null,
					type: null,
				},
				triggers: []
			};

			$scope.validator = {
				moduleId: {
					completed: false,
					mandatory: true,
				},
				materialId: {
					completed: false,
					mandatory: true,
				},
				triggers: {
					completed: false,
					mandatory: true,
				}
			};

			$scope.leftMenu = {
				moduleId: {
					display: true,
					open: false,
					preview: false,
				},
				materialId: {
					display: false,
					open: false,
					preview: false,
				},
				publishDateTime: {
					display: false,
					open: false,
					preview: false,
				},
				name: {
					display: false,
					open: false,
					preview: false,
				},
				publishFrequency: {
					display: false,
					open: false,
					preview: false,
				},
				patient: {
					display: false,
					open: false,
					preview: [],
				},
				demo: {
					display: false,
					open: false,
					preview: false,
				},
				appointmentStatus: {
					display: false,
					open: false,
					preview: false,
				},
				appointment: {
					display: false,
					open: false,
					preview: false,
				},
				diagnosis: {
					display: false,
					open: false,
					preview: false,
				},
				doctor: {
					display: false,
					open: false,
					preview: false,
				},
				machine: {
					display: false,
					open: false,
					preview: false,
				},


			};

			$scope.subModule = null;
			$scope.triggerSection.show = false;
			$scope.totalSteps = 0;
			$scope.completedSteps = 0;
			$scope.formReady = false;
			$scope.moduleName = null;
			$scope.showFrequency = false;
		}

		initialization();

		$scope.$watch('toSubmit', function(newValue, oldValue, scope) {
		}, true);

		$scope.$watch('toSubmit.triggers', function() {
			$scope.validator.triggers.completed = ($scope.toSubmit.triggers.length > 0);
		}, true);

		$scope.$watch('toSubmit.occurrence', function() {
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

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.triggerSection.show = (($scope.completedSteps + 1) >= $scope.totalSteps);
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
		}, true);

		// Function to toggle necessary changes when click on module buttons
		$scope.moduleUpdate = function (moduleSelected) {
			initialization();
			if(moduleSelected.ID !== $scope.toSubmit.moduleId.value) {
				publicationCollectionService.getPublicationsPerModule(Session.retrieveObject('user').id, moduleSelected.ID).then(function (response) {
					response.data["publications"].forEach(function(entry) {
						if($scope.language.toUpperCase() === "FR")
							entry.name_display = entry.name_FR;
						else
							entry.name_display = entry.name_EN;
					});

					$scope.subModule = JSON.parse(response.data["subModule"]);

					if(response.data.unique === "0") {
						$scope.unique = false;
						if ($scope.toSubmit.name != "undefined") {
							$scope.toSubmit.name = {
								name_EN: null,
								name_FR: null,
							};
						}
						if ($scope.validator.name != "undefined") {
							$scope.validator.name = {
								completed: false,
								mandatory: true,
							};
						}
					} else {
						$scope.unique = true;
						delete $scope.toSubmit.name;
						delete $scope.validator.name;
					}

					$scope.publishFrequencySection.available = response.data["triggers"].indexOf("1") !== -1 ? true: false;
					$scope.triggerSection.patient.available = response.data["triggers"].indexOf("2") !== -1 ? true: false;
					$scope.triggerSection.demo.available = response.data["triggers"].indexOf("3") !== -1 ? true: false;
					$scope.triggerSection.appointmentStatus.available = response.data["triggers"].indexOf("4") !== -1 ? true: false;
					$scope.triggerSection.appointment.available = response.data["triggers"].indexOf("5") !== -1 ? true: false;
					$scope.triggerSection.diagnosis.available = response.data["triggers"].indexOf("6") !== -1 ? true: false;
					$scope.triggerSection.doctor.available = response.data["triggers"].indexOf("7") !== -1 ? true: false;
					$scope.triggerSection.machine.available = response.data["triggers"].indexOf("8") !== -1 ? true: false;
					$scope.publishDate.available = response.data["triggers"].indexOf("9") !== -1 ? true: false;
					$scope.publicationList = response.data["publications"]; // Assign value
				}).catch(function(err) {
					alert($filter('translate')('PUBLICATION.ADD.ERROR_MODULE') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.data));
					$state.go('publication');
				}).finally(function() {
					processingModal.close(); // hide modal
					processingModal = null; // remove reference
				});
			}

			$scope.toSubmit.moduleId.value = moduleSelected.ID;
			$scope.validator.moduleId.completed = true;
			$scope.leftMenu.moduleId.preview = moduleSelected.name_display;
			$scope.leftMenu.moduleId.open = true;
		};

		$scope.prepareFrequencyOccurence = function() {
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

		$scope.deleteFrequencyOccurence = function() {
			delete $scope.toSubmit.occurrence;
			delete $scope.validator.occurrence;
		};

		$scope.updateMaterial = function (selectedAt) {
			//leftMenu.name.display
			$scope.leftMenu.materialId.display = (selectedAt.ID != null);
			$scope.toSubmit.materialId.type = selectedAt.type_EN;
			$scope.leftMenu.materialId.preview = selectedAt.name_display;

			if ($scope.subModule !== null) {
				if ($scope.toSubmit.publishDateTime != "undefined") {
					$scope.toSubmit.publishDateTime = {
						publish_date: null,
						publish_time: null,
					};
				}
				if ($scope.validator.publishDateTime != "undefined") {
					$scope.validator.publishDateTime = {
						completed: false,
						mandatory: false,
					};
				}
				angular.forEach($scope.subModule, function (sub) {
					if (sub.name_EN === $scope.toSubmit.materialId.type) {
						if (sub.publicationDate === 1) {
							$scope.validator.publishDateTime.mandatory = true;
						}
						else {
							$scope.validator.publishDateTime.mandatory = false;
						}
					}
				});
			} else {
				delete $scope.toSubmit.publishDateTime;
				delete $scope.validator.publishDateTime;
			}

			$scope.leftMenu.materialId.open = true;

			$scope.validator.materialId.completed = true;
		};

		// Function to toggle necessary changes when updating publication name
		$scope.nameUpdate = function () {
			$scope.validator.name.completed = ($scope.toSubmit.name.name_EN != null && $scope.toSubmit.name.name_FR != null);
			$scope.leftMenu.name.open = ($scope.toSubmit.name.name_EN != null || $scope.toSubmit.name.name_FR != null);
			$scope.leftMenu.name.display = ($scope.toSubmit.name.name_EN != null || $scope.toSubmit.name.name_FR != null);
		};

		$scope.publishDateUpdate = function () {
			$scope.validator.publishDateTime.completed = $scope.toSubmit.publishDateTime.publish_date && $scope.toSubmit.publishDateTime.publish_time;
			$scope.leftMenu.publishDateTime.display = $scope.toSubmit.publishDateTime.publish_date || $scope.toSubmit.publishDateTime.publish_time;
			$scope.leftMenu.publishDateTime.open = $scope.leftMenu.publishDateTime.display;
		};

		// Function to toggle necessary changes when updating the sex
		$scope.sexUpdate = function (sex) {
			$scope.triggerSection.demo.open = true;
			if (!$scope.demoTrigger.sex) {
				$scope.demoTrigger.sex = sex.name;
				$scope.demoTrigger.sex_display = sex.display;
			} else if ($scope.demoTrigger.sex == sex.name) {
				$scope.demoTrigger.sex = null; // Toggle off
				$scope.demoTrigger.sex_display = null;
				if ($scope.demoTrigger.age.min == 0 && $scope.demoTrigger.age.max == 130) {
					$scope.triggerSection.demo.open = false;
				}
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
				if(!sexPresent)
					$scope.toSubmit.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
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

			$scope.triggerSection.demo.open = ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max !== 130 || $scope.demoTrigger.sex);

			if ($scope.demoTrigger.age.min === 0 && $scope.demoTrigger.age.max === 130) {
				angular.forEach($scope.toSubmit.triggers, function (item, index, object) {
					if (item["type"] === "Age") {
						object.splice(index, 1);
					}
				});
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


		// Function to toggle appointment status filter
		$scope.appointmentStatusUpdate = function (entrySelected) {
			var entryFound = false;
			angular.forEach($scope.appointmentStatusList, function(item){
				if (($scope.toSubmit.triggers.findIndex(x => x.id === item.id)) != -1)
				{
					$scope.toSubmit.triggers.splice($scope.toSubmit.triggers.findIndex(x => x.id === item.id), 1);
				} else {
					if (item.id == entrySelected.id) {
						$scope.toSubmit.triggers.push({id: item.id, type: item.type});
						$scope.leftMenu.appointmentStatus.open = true;
						$scope.leftMenu.appointmentStatus.show = true;
						$scope.leftMenu.appointmentStatus.value = entrySelected.name_display;
						entryFound = true;
					}
				}
			});

			if(!entryFound) {
				$scope.leftMenu.appointmentStatus.open = false;
				$scope.leftMenu.appointmentStatus.show = false;
				$scope.leftMenu.appointmentStatus.value = null;
			}


			if ($scope.apptSelected != entrySelected.id) {
				$scope.apptSelected = entrySelected.id;

			}
			else
				$scope.apptSelected = null;



			console.log("tracking");
			console.log($scope.validator);
			console.log($scope.toSubmit);


		};

		// Submit new publication
		$scope.submitPublication = function () {
			// if ($scope.checkForm()) {
			// if ($scope.demoTrigger.sex)
			// 	$scope.toSubmit.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
			// if ($scope.demoTrigger.age.min >= 0 && $scope.demoTrigger.age.max <= 130) { // i.e. not empty
			// 	if ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max !== 130) { // triggers were changed
			// 		$scope.toSubmit.triggers.push({
			// 			id: String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max)),
			// 			type: 'Age'
			// 		});
			// 	}
			// }

			// addTriggers($scope.appointmentStatusList);
			// addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
			// addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
			// addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
			// addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
			// addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);
			/*			if ($scope.showFrequency) {
							$scope.toSubmit.occurrence.set = 1;
							// convert dates to timestamps
							$scope.toSubmit.occurrence.start_date = moment($scope.toSubmit.occurrence.start_date).format('X');
							if ($scope.toSubmit.occurrence.end_date) {
								$scope.toSubmit.occurrence.end_date = moment($scope.toSubmit.occurrence.end_date).format('X');
							}
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
						}

						// For some reason the HTML text fields add a zero-width-space
						// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
						$scope.toSubmit.name_EN = $scope.toSubmit.name_EN.replace(/\u200B/g,'');
						$scope.toSubmit.name_FR = $scope.toSubmit.name_FR.replace(/\u200B/g,'');

						angular.forEach($scope.termList, function (term) {
							if (term.added)
								$scope.toSubmit.terms.push(term);
						});

						var currentUser = Session.retrieveObject('user');

						if (!$scope.publishDate.available) {
							delete $scope.toSubmit["publish_date"];
							delete $scope.toSubmit["publish_time"];
						}

						if (!$scope.publishFrequencySection.available) {
							delete $scope.toSubmit["occurrence"];
						}

						//console.log($scope.toSubmit["triggers"]);

						if(typeof $scope.toSubmit["triggers"] === 'undefined' || $scope.toSubmit["triggers"].length <= 0)
							alert($filter('translate')('PUBLICATION.ADD.TRIGGER_REQUIRED'));
						else {*/


			if ($scope.showFrequency) {
				$scope.toSubmit.occurrence.set = 1;
				$scope.toSubmit.occurrence.start_date = moment($scope.toSubmit.occurrence.start_date).format('X');
				if ($scope.toSubmit.occurrence.end_date) {
					$scope.toSubmit.occurrence.end_date = moment($scope.toSubmit.occurrence.end_date).format('X');
				}
				$scope.toSubmit.occurrence.frequency.additionalMeta = [];
				if ($scope.toSubmit.occurrence.frequency.custom) {
					$scope.toSubmit.occurrence.frequency.meta_key = $scope.customFrequency.unit.meta_key;
					$scope.toSubmit.occurrence.frequency.meta_value = $scope.customFrequency.meta_value;
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
			}


			$scope.toSubmit.user = Session.retrieveObject('user').id;
			$.ajax({
				type: "POST",
				url: "publication/insert/publication",
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					alert($filter('translate')('PUBLICATION.ADD.ERROR_ADD') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
					//$state.go('publication');
				},
				complete: function() {
					//$state.go('publication');
				}
			});
			// }
			// }
		};

		$scope.searchAt = function (field) {
			$scope.atEntered = field;
		};

		//search function
		$scope.searchAtFilter = function (Filter) {
			var keyword = new RegExp($scope.atEntered, 'i');
			return !$scope.atEntered || keyword.test(Filter.name_display);
		};

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

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Function to toggle trigger in a list on/off
		$scope.selectTrigger = function (trigger, selectAll) {


			// if(selectAll) {
			// 	var removeAll = false;
			// 	angular.forEach($scope.toSubmit.triggers, function(entry) {
			// 		if(trigger.type === entry.type) {
			// 			if (entry.id === 'ALL')
			// 				removeAll = true;
			// 			delete entry;
			// 		}
			// 	});
			// 	if(!removeAll)
			// 		$scope.toSubmit.triggers.push({id: 'ALL', type: trigger.type});
			// }
			// else {

			selectAll.all = false;
			selectAll.checked = false;
			if (($scope.toSubmit.triggers.findIndex(x => x.id === trigger.id)) != -1)
			{
				$scope.toSubmit.triggers.splice($scope.toSubmit.triggers.findIndex(x => x.id === trigger.id), 1);
			} else {
				$scope.toSubmit.triggers.push({id: trigger.id, type: trigger.type});
			}
			// }


			console.log("selectTrigger");
			// console.log(selectAll);
			// console.log(trigger);

			console.log($scope.toSubmit.triggers);
			// selectAll.all = false;
			// selectAll.checked = false;
			// if (trigger.added)
			// 	trigger.added = 0;
			// else
			// 	trigger.added = 1;


		};







		// Fruits
		$scope.fruits = [
			{ name: 'apple',    selected: true },
			{ name: 'orange',   selected: false },
			{ name: 'pear',     selected: true },
			{ name: 'naartjie', selected: false }
		];

		// Selected fruits
		$scope.selection = [];

		// Helper method to get selected fruits
		$scope.selectedFruits = function selectedFruits() {
			return filterFilter($scope.fruits, { selected: true });
		};

		// Watch fruits for changes
		$scope.$watch('fruits|filter:{selected:true}', function (nv) {
			$scope.selection = nv.map(function (fruit) {
				return fruit.name;
			});
		}, true);








		// Function for selecting all triggers in a trigger list
		$scope.selectAllTriggers = function (triggerList,triggerFilter,selectAll) {

			console.log("selectAllTriggers");
			// console.log(triggerList);
			// console.log(triggerFilter);
			// console.log(selectAll);

			// var filtered = $scope.filter(triggerList,triggerFilter);
			// console.log(filtered);
			//
			// if (filtered.length == triggerList.length) { // search field wasn't used
			// 	if (selectAll.checked) {
			// 		angular.forEach(filtered, function (trigger) {
			// 			trigger.added = 0;
			// 		});
			// 		selectAll.checked = false; // toggle off
			// 		selectAll.all = false;
			// 	}
			// 	else {
			// 		angular.forEach(filtered, function (trigger) {
			// 			trigger.added = 1;
			// 		});
			//
			// 		selectAll.checked = true; // toggle on
			// 		selectAll.all = true;
			// 	}
			// }
			// else {
			// 	if (selectAll.checked) { // was checked
			// 		angular.forEach(filtered, function (trigger) {
			// 			trigger.added = 0;
			// 		});
			// 		selectAll.checked = false; // toggle off
			// 		selectAll.all = false;
			// 	}
			// 	else { // was not checked
			// 		angular.forEach(filtered, function (trigger) {
			// 			trigger.added = 1;
			// 		});
			//
			// 		selectAll.checked = true; // toggle on
			//
			// 	}
			// }

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
			if(selectAll) {
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

		// Function for adding new frequency filter
		$scope.addFrequencyFilter = function () {
			$scope.prepareFrequencyOccurence();
			$scope.showFrequency = true;
			$scope.toSubmit.occurrence.frequency.meta_value = $scope.frequencySelected.meta_value;
			$scope.toSubmit.occurrence.frequency.meta_key = $scope.frequencySelected.meta_key;
			$scope.leftMenu.publishFrequency.display = true;
			$scope.leftMenu.publishFrequency.open = true;
		};

		// Function for removing new frequency filter
		$scope.removeFrequencyFilter = function () {
			$scope.showFrequency = false; // Hide form
			$scope.toSubmit.occurrence.set = 0; // Not set anymore
			$scope.flushAllFrequencyFilters();
			$scope.leftMenu.publishFrequency.display = false;
			$scope.leftMenu.publishFrequency.open = false;
			$scope.deleteFrequencyOccurence();
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


		// Watch to restrict the end calendar to not choose an earlier date than the start date
		$scope.$watch('toSubmit.occurrence.start_date', function(startDate){
			if (startDate !== undefined) {
				$scope.dateOptionsEnd.minDate = startDate;
			}
		});
		// Watch to restrict the start calendar to not choose a start after the end date
		$scope.$watch('toSubmit.occurrence.end_date', function(endDate){
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
			$scope.openAndValidate();
		};
		$scope.openEnd = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();
			$scope.popupStart['opened'] = false;
			$scope.popupEnd['opened'] = true;
			$scope.openAndValidate();
		};

		// default hide end date
		$scope.addEndDate = false;
		$scope.toggleEndDate = function () {
			$scope.addEndDate = !$scope.addEndDate;
			if (!$scope.addEndDate) {
				$scope.toSubmit.occurrence.end_date = null;
			}
			$scope.openAndValidate();
		};

		$scope.openAndValidate = function(){
			try {
				$scope.leftMenu.publishFrequency.open = true;
				$scope.validator.occurrence.completed = $scope.checkFrequencyTrigger();
			} catch(e) {}
		};

		// Initialize list of preset publishing frequencies
		$scope.presetFrequencies = FrequencyFilterService.presetFrequencies;
		$scope.frequencySelected = $scope.presetFrequencies[0]; // Default "Once"

		$scope.selectFrequency = function (frequency) {
			$scope.frequencySelected = frequency;
			$scope.publishFrequencySection.open = true;
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
			$scope.openAndValidate();
		};

		// Initialize object for repeat interval
		$scope.customFrequency = FrequencyFilterService.customFrequency;
		$scope.frequencyUnits = FrequencyFilterService.frequencyUnits;
		$scope.defaultCustomFrequency = $scope.frequencyUnits[0];
		$scope.customFrequency.unit = $scope.defaultCustomFrequency; // Default "1 Day"

		// Custom watch to singularize/pluralize frequency unit names
		$scope.$watch('customFrequency.meta_value', function(newValue, oldValue){
			if ($scope.frequencySelected.id === 'custom') {
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
			}
			else {
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
				if (selectionArray.length === 1) {
					return $filter('translate')('PUBLICATION.ADD.1_DAY_SELECTED');
				}
				return selectionArray.length + $filter('translate')('PUBLICATION.ADD.DAYS_SELECTED');
			}
		};

		$scope.projectText = {
			buttonDefaultText: $filter('translate')('PUBLICATION.ADD.SELECT'),
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
			$scope.openAndValidate();
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
					$scope.selectedDaysInWeekText = $scope.selectedDaysInWeekText.slice(0,-2) + $filter('translate')('PUBLICATION.ADD.AND') +
						($scope.language.toUpperCase() === "FR" ? $scope.selectedDaysInWeek[i].name.toLowerCase() : $scope.selectedDaysInWeek[i].name);
				}
			}
			$scope.openAndValidate();
		};

		$scope.setSelectedWeekNumberInMonthText = function (week) {
			$scope.selectedWeekNumberInMonthText = week.name;
			$scope.openAndValidate();

		};

		$scope.setSelectedSingleDayInWeekText = function (day) {
			$scope.selectedSingleDayInWeekText = ($scope.language.toUpperCase() === "FR" ? day.name.toLowerCase() : day.name);
			$scope.openAndValidate();
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
					$scope.selectedMonthsInYearText = $scope.selectedMonthsInYearText.slice(0,-2) + $filter('translate')('PUBLICATION.ADD.AND')
						+ ($scope.language.toUpperCase() === "FR" ? $scope.selectedMonthsInYear[i].name.toLowerCase() : $scope.selectedMonthsInYear[i].name);
				}
			}
			$scope.openAndValidate();
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
				else if (index < dates.length-1) {
					$scope.selectedDatesInMonthText += dateNumber + ", ";
				}
					// Replace last comma with an "and"
				// Eg. 1st, 2nd and 4th
				else {
					$scope.selectedDatesInMonthText = $scope.selectedDatesInMonthText.slice(0,-2) + $filter('translate')('PUBLICATION.ADD.AND') + dateNumber;
				}
			});
			$scope.openAndValidate();
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
			$scope.openAndValidate();
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
			$scope.openAndValidate();
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
			$scope.openAndValidate();
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
					return $filter('translate')('PUBLICATION.ADD.1_MONTH_SELECTED');
				}
				return selectionArray.length + $filter('translate')('PUBLICATION.ADD.MONTHS_SELECTED');
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
			$scope.openAndValidate();
		};

		// Initialize array holding additional meta data for custom repeats
		$scope.additionalMeta = FrequencyFilterService.additionalMeta;

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



