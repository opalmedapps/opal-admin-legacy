angular.module('opalAdmin.controllers.educationalMaterial.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).

	controller('educationalMaterial.edit', function ($scope, $filter, $sce, $uibModal, $uibModalInstance, $state, educationalMaterialCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		$scope.eduMat = {}; // initialize edumat object

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

		// Initialize to hold demographic triggers
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

		$scope.selectAll = {
			appointment: {all:false, checked:false},
			diagnosis: {all:false, checked:false},
			doctor: {all:false, checked:false},
			machine: {all:false, checked:false},
			patient: {all:false, checked:false}
		}

		$scope.tocsComplete = true;

		// Initialize lists to hold the distinct edu material types
		$scope.EduMatTypes_EN = [];
		$scope.EduMatTypes_FR = [];
		// Call our API to get the list of edu material types
		educationalMaterialCollectionService.getEducationalMaterialTypes().then(function (response) {

			$scope.EduMatTypes_EN = response.data.EN;
			$scope.EduMatTypes_FR = response.data.FR;
		}).catch(function(response) {
			console.error('Error occurred getting educational material types:', response.status, response.data);
		});

		$scope.bannerMessageModal = "";

		// Function to show page banner 
		$scope.showBannerModal = function () {
			$(".bannerMessageModal").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessageModal").slideUp();
				}, 5000);
			});
		};

		// Function to set banner class
		$scope.setBannerModalClass = function (classname) {
			// Remove any classes starting with "alert-" 
			$(".bannerMessageModal").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessageModal").addClass('alert-' + classname);
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

		// Call our API service to get the current educational material details
		educationalMaterialCollectionService.getEducationalMaterialDetails($scope.currentEduMat.serial).then(function (response) {

			// Assign value
			$scope.eduMat = response.data;

			// Assign demographic triggers
			checkDemographicTriggers();

			// Call our API service to get each filter
			filterCollectionService.getFilters().then(function (response) {

				$scope.appointmentTriggerList = checkAdded(response.data.appointments, $scope.selectAll.appointment); // Assign value
				$scope.dxTriggerList = checkAdded(response.data.dx, $scope.selectAll.diagnosis);
				$scope.doctorTriggerList = checkAdded(response.data.doctors, $scope.selectAll.doctor);
				$scope.machineTriggerList = checkAdded(response.data.machines, $scope.selectAll.machine);
				$scope.patientTriggerList = checkAdded(response.data.patients, $scope.selectAll.patient);

				processingModal.close(); // hide modal
				processingModal = null; // remove reference

			}).catch(function(response) {
				console.error('Error occurred getting triggers:', response.status, response.data);
			});
		}).catch(function(response) {
			console.error('Error occurred getting educational material details:', response.status, response.data);
		});

		// Function to toggle trigger in a list on/off
		$scope.selectTrigger = function (trigger, selectAll) {
			$scope.setChangesMade();
			selectAll.all = false;
			selectAll.checked = false;
			$scope.eduMat.triggers_updated = 1;
			if (trigger.added)
				trigger.added = 0;
			else
				trigger.added = 1;

		};

		// Function for selecting all triggers in a trigger list
		$scope.selectAllTriggers = function (triggerList,triggerFilter,selectAll) {

			var filtered = $scope.filter(triggerList,triggerFilter);
			$scope.eduMat.triggers_updated = 1;
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

		$scope.detailsUpdated = function () {
			$scope.eduMat.details_updated = 1;
			$scope.setChangesMade();
		}

		// Function to assign 1 to existing triggers
		function checkAdded(triggerList, selectAll) {
			angular.forEach($scope.eduMat.triggers, function (selectedTrigger) {
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
			var demoTrigger = {
				sex: null,
				age: {
					min: 0,
					max: 100
				}
			};
			angular.forEach($scope.eduMat.triggers, function (selectedTrigger) {
				if (selectedTrigger.type == 'Sex')
					$scope.demoTrigger.sex = selectedTrigger.id;
				if (selectedTrigger.type == 'Age') {
					$scope.demoTrigger.age.min = parseInt(selectedTrigger.id.split(',')[0]);
					$scope.demoTrigger.age.max = parseInt(selectedTrigger.id.split(',')[1]);
				}
			});

			return demoTrigger;
		}

		// Function to toggle necessary changes when updating the sex
		$scope.sexUpdate = function (sex) {

			if (!$scope.demoTrigger.sex) {
				$scope.demoTrigger.sex = sex.name;
			} else if ($scope.demoTrigger.sex == sex.name) {
				$scope.demoTrigger.sex = null; // Toggle off
			} else {
				$scope.demoTrigger.sex = sex.name;
			}

			$scope.setChangesMade();
			$scope.eduMat.triggers_updated = 1;

		};

		// Function to toggle necessary changes when updating the age
		$scope.ageUpdate = function () {

			$scope.setChangesMade();
			$scope.eduMat.triggers_updated = 1;
		}

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.eduMat.name_EN && $scope.eduMat.name_FR && (($scope.eduMat.url_EN && $scope.eduMat.url_FR)
				|| $scope.tocsComplete) && $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		$scope.validateTOCs = function () {

			$scope.setChangesMade();
			$scope.tocsComplete = true;
			$scope.eduMat.tocs_updated = 1;
			if (!$scope.eduMat.tocs.length) {
				$scope.tocsComplete = false;
				$scope.eduMat.tocs_updated = 0;
			}
			else {
				angular.forEach($scope.eduMat.tocs, function (toc) {
					if (!toc.name_EN || !toc.name_FR || !toc.url_EN
						|| !toc.url_FR || !toc.type_EN || !toc.type_FR) {
						$scope.tocsComplete = false;
					$scope.eduMat.tocs_updated = 0;
					}
				});
			}
		}


		// Function to validate english share url
		$scope.validShareURLEN = { status: null, message: null };
		$scope.validateShareURLEN = function (url) {
			if (!url) {
				$scope.validShareURLEN.status = null;
				$scope.setChangesMade();
				return;
			}
			// regex to check pdf extension
			var re = /(?:\.([^.]+))?$/;
			if (re.exec(url)[1] != 'pdf') {
				$scope.validShareURLEN.status = 'invalid';
				$scope.validShareURLEN.message = 'URL must be a pdf';
				$scope.setChangesMade();
				return;
			} else {
				$scope.validShareURLEN.status = 'valid';
				$scope.validShareURLEN.message = null;
				$scope.setChangesMade();
			}
		}

		// Function to validate french share url
		$scope.validShareURLFR = { status: null, message: null };
		$scope.validateShareURLFR = function (url) {
			if (!url) {
				$scope.validShareURLFR.status = null;
				$scope.setChangesMade();
				return;
			}
			// regex to check pdf extension
			var re = /(?:\.([^.]+))?$/;
			if (re.exec(url)[1] != 'pdf') {
				$scope.validShareURLFR.status = 'invalid';
				$scope.validShareURLFR.message = 'URL must be a pdf';
				$scope.setChangesMade();
				return;
			} else {
				$scope.validShareURLFR.status = 'valid';
				$scope.validShareURLFR.message = null;
				$scope.setChangesMade();
			}
		}

		// Submit changes
		$scope.updateEduMat = function () {

			if ($scope.checkForm()) {

				// Initialize trigger
				$scope.eduMat.triggers = [];

				// Add demographic triggers, if defined
				if ($scope.demoTrigger.sex)
					$scope.eduMat.triggers.push({ id: $scope.demoTrigger.sex, type: 'Sex' });
				if ($scope.demoTrigger.age.min >= 0 && $scope.demoTrigger.age.max <= 100) { // i.e. not empty
					if ($scope.demoTrigger.age.min !== 0 || $scope.demoTrigger.age.max != 100) { // Triggers were changed
						$scope.eduMat.triggers.push({
							id: String($scope.demoTrigger.age.min).concat(',', String($scope.demoTrigger.age.max)),
							type: 'Age'
						});
					}
				}

				// Add trigger to edu material
				addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
				addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
				addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
				addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
				addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);

				// Log who updated educational material
				var currentUser = Session.retrieveObject('user');
				$scope.eduMat.user = currentUser;

				// Submit form
				$.ajax({
					type: "POST",
					url: "php/educational-material/update.educational_material.php",
					data: $scope.eduMat,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.eduMat.name_EN + "/ " + $scope.eduMat.name_FR + "\"!";
							$scope.showBanner();
							$uibModalInstance.close();
						}
						else {
							$scope.setBannerModalClass('danger');
							$scope.bannerMessageModal = response.message;
							$scope.$apply();
							$scope.showBannerModal();
						}
						
					}
				});
			}
		};

		// Function to add table of contents to eduMat object
		$scope.addTOC = function () {
			var newOrder = $scope.eduMat.tocs.length + 1;
			$scope.eduMat.tocs.push({
				name_EN: "",
				name_FR: "",
				url_EN: "",
				url_FR: "",
				order: newOrder,
				serial: null
			});
			$scope.validateTOCs();
		};

		// Function to remove table of contents from eduMat object
		$scope.removeTOC = function (order) {
			$scope.eduMat.tocs.splice(order - 1, 1);
			// Decrement orders for content after the one just removed
			for (var index = order - 1; index < $scope.eduMat.tocs.length; index++) {
				$scope.eduMat.tocs[index].order -= 1;
			}
			$scope.validateTOCs();
		};

		// Function to return triggers that have been checked
		function addTriggers(triggerList, selectAll) {
			console.log(selectAll);

			if (selectAll) {
				$scope.eduMat.triggers.push({id: 'ALL', type: triggerList[0].type});
			}
			else {
				angular.forEach(triggerList, function (trigger) {
					if (trigger.added)
						$scope.eduMat.triggers.push({ id: trigger.id, type: trigger.type });

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

		// Function to accept/trust html (styles, classes, etc.)
		$scope.deliberatelyTrustAsHtml = function (htmlSnippet) {
			return $sce.trustAsHtml(htmlSnippet);
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