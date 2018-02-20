angular.module('opalAdmin.controllers.newLegacyQuestionnaireController', ['ngAnimate', 'ui.bootstrap']).


	/******************************************************************************
	* New Legacy Questionnaire Page controller 
	*******************************************************************************/
	controller('newLegacyQuestionnaireController', function($scope, $filter, $uibModal, legacyQuestionnaireCollectionService, $state, filterCollectionService) {
       
       // Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.legacyQuestionnaireSection = {open:false, show:true};
		$scope.titleSection = {open:false, show:false};
		$scope.demoSection = {open:false, show:false};
		$scope.filterSection = {open:false, show:false};

		// completed steps boolean object; used for progress bar
		var steps = {
			title: { completed: false },
			legacy_questionnaire: { completed: false }
		};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize search field variables
		$scope.appointmentSearchField = null;
		$scope.dxSearchField = null;
		$scope.doctorSearchField = null;
		$scope.resourceSearchField = null;
		$scope.patientSearchField = null;

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 2;

		// Progress for progress bar on default steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Initialize new legacy questionnaire object
		$scope.newLegacyQuestionnaire = {
			name_EN: null,
			name_FR: null,
			legacy_questionnaire: null,
			filters: []
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

		// Initialize lists to hold filters
		$scope.demoFilter = {
			sex: null,
			age: {
				min: 0,
				max: 100
			}
		};
		// Initialize lists to hold filters
		$scope.appointmentList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];

		/* Function for the "Processing..." dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'processingModal.htm',
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

		// Call our API service to get each filter
		filterCollectionService.getFilters().then(function (response) {

			$scope.appointmentList = response.data.appointments; // Assign value
			$scope.dxFilterList = response.data.dx;
			$scope.doctorFilterList = response.data.doctors;
			$scope.resourceFilterList = response.data.resources;
			$scope.patientFilterList = response.data.patients;

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

				$scope.demoSection.show = true;
				$scope.filterSection.show = true;

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

		// Function to toggle necessary changes when updating the sex
		$scope.sexUpdate = function (sex) {

			$scope.demoSection.open = true;

			if (!$scope.demoFilter.sex) {
				$scope.demoFilter.sex = sex.name;
			} else if ($scope.demoFilter.sex == sex.name) {
				$scope.demoFilter.sex = null; // Toggle off
			} else {
				$scope.demoFilter.sex = sex.name;
			}

		};

		// Function to toggle necessary changes when updating the age 
		$scope.ageUpdate = function () {

			$scope.demoSection.open = true;
			
		};

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

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

		// Function to assign legacy questionnaire when textbox is changing 
		$scope.changeLegacyQuestionnaireFilter = function (legacyQuestionnaireFilter) {
			$scope.legacyQuestionnaireFilter = legacyQuestionnaireFilter;
		};

		// Function for searching through the educational material list
		$scope.searchEduMatsFilter = function (legacy_questionnaire) {
			var keyword = new RegExp($scope.legacyQuestionnaireFilter, 'i');
			return !$scope.legacyQuestionnaireFilter || keyword.test(legacy_questionnaire.name);
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

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.newLegacyQuestionnaire.filters.push({ id: Filter.id, type: Filter.type });
			});
		}

		// Function to check if filters are added
		$scope.checkFilters = function (filterList) {
			var filtersAdded = false;
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					filtersAdded = true;
			});
			return filtersAdded;
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {
			if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
				return true;
			else
				return false;
		};

		// Function to submit the new legacy questionnaire
		$scope.submitLegacyQuestionnaire = function () {
			if ($scope.checkForm()) {

				// Add demographic filters, if defined
				if ($scope.demoFilter.sex)
					$scope.newLegacyQuestionnaire.filters.push({ id: $scope.demoFilter.sex, type: 'Sex' });
				if ($scope.demoFilter.age.min >= 0 && $scope.demoFilter.age.max <= 100) { // i.e. not empty
					if ($scope.demoFilter.age.min !== 0 || $scope.demoFilter.age.max != 100) { // Filters were changed
						$scope.newLegacyQuestionnaire.filters.push({
							id: String($scope.demoFilter.age.min).concat(',', String($scope.demoFilter.age.max)),
							type: 'Age'
						});
					}
				}
				// Add filters to new legacy questionnaire object
				addFilters($scope.appointmentList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);
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

