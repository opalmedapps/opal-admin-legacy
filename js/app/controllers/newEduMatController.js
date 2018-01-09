angular.module('opalAdmin.controllers.newEduMatController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* New Educational Material Page controller 
	*******************************************************************************/
	controller('newEduMatController', function ($scope, $filter, $state, $sce, $uibModal, educationalMaterialCollectionService, filterCollectionService) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};
		
		// Default boolean variables
		$scope.title = {open:false, show:true};
		$scope.type = {open:false, show:false};
		$scope.phase = {open:false, show:false};
		$scope.url = {open:false, show:false};
		$scope.tocs = {open:false, show:false};
		$scope.share_url = {open:false, show:false};
		$scope.demo = {open:false, show:false};
		$scope.terms = {open:false, show:false};

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

		// Initialize search field variables
		$scope.termSearchField = "";
		$scope.dxSearchField = "";
		$scope.doctorSearchField = "";
		$scope.resourceSearchField = "";
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
			filters: []
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

		// Initialize lists to hold filters
		$scope.demoFilter = {
			sex: null,
			age: {
				min: 0,
				max: 100
			}
		};
		$scope.termList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];

		// Initialize lists to hold the distinct edu material types
		$scope.EduMatTypes_EN = [];
		$scope.EduMatTypes_FR = [];


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

		// Call our API service to get each filter
		filterCollectionService.getFilters().then(function (response) {

			$scope.termList = response.data.expressions; // Assign value
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

		// Call our API to get the list of edu material types
		educationalMaterialCollectionService.getEducationalMaterialTypes().then(function (response) {

			$scope.EduMatTypes_EN = response.data.EN;
			$scope.EduMatTypes_FR = response.data.FR;
		}).catch(function(response) {
			console.error('Error occurred getting educational material types:', response.status, response.data);
		});

		// Call our API to get the list of phase-in-treatments
		educationalMaterialCollectionService.getPhasesInTreatment().then(function (response) {
			$scope.phaseInTxs = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting phases in treatment:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating titles
		$scope.titleUpdate = function () {

			$scope.title.open = true;

			if ($scope.newEduMat.name_EN && $scope.newEduMat.name_FR) {

				$scope.type.show = true;

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

			$scope.url.open = true;

			if ($scope.newEduMat.url_EN || $scope.newEduMat.url_FR) {
				steps.tocs.completed = true; // Since it will be hidden
				$scope.tocs.show = false;
			}

			else {
				$scope.tocs.show = true;
			}

			if ($scope.newEduMat.url_EN && $scope.newEduMat.url_FR) {

				// Toggle booleans
				$scope.share_url.show = true;
				$scope.demo.show = true;
				$scope.terms.show = true;

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
		$scope.typeUpdate = function () {

			$scope.type.open = true;

			if ($scope.newEduMat.type_EN && $scope.newEduMat.type_FR) {

				$scope.phase.show = true;

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

			$scope.phase.open = true;

			$scope.url.show = true;
			$scope.tocs.show = true;

			// Toggle boolean 
			steps.phase.completed = true;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle necessary changes when updating the share URL 
		$scope.shareURLUpdate = function () {

			$scope.share_url.open = true;

		};

		// Function to toggle necessary changes when updating the sex
		$scope.sexUpdate = function (sex) {

			$scope.demo.open = true;

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

			$scope.demo.open = true;
			
		};

		$scope.tocsComplete = false;
		// Function to toggle necessary changes when updating the table of contents
		$scope.tocUpdate = function () {

			$scope.tocs.open = true;

			steps.tocs.completed = true;
			$scope.tocsComplete = true;

			if (!$scope.newEduMat.tocs.length) {
				steps.url.completed = false; // Since it will be hidden
				$scope.tocsComplete = false;
				steps.tocs.completed = false;

				$scope.url.show = true;

			} else {

				steps.url.completed = true; // Since it will be hidden
				$scope.url.show = false;

				angular.forEach($scope.newEduMat.tocs, function (toc) {
					if (!toc.name_EN || !toc.name_FR || !toc.url_EN
						|| !toc.url_FR || !toc.type_EN || !toc.type_FR) {
						$scope.tocsComplete = false;
						steps.tocs.completed = false;
						steps.url.completed = false;
					}
				});

				if ($scope.tocsComplete) {
					$scope.share_url.show = true;
					$scope.demo.show = true;
					$scope.terms.show = true;
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

				// Add demographic filters, if defined
				if ($scope.demoFilter.sex)
					$scope.newEduMat.filters.push({ id: $scope.demoFilter.sex, type: 'Sex' });
				if ($scope.demoFilter.age.min >= 0 && $scope.demoFilter.age.max <= 100) { // i.e. not empty
					if ($scope.demoFilter.age.min !== 0 || $scope.demoFilter.age.max != 100) { // Filters were changed
						$scope.newEduMat.filters.push({
							id: String($scope.demoFilter.age.min).concat(',', String($scope.demoFilter.age.max)),
							type: 'Age'
						});
					}
				}
				// Add other filters to new edu material object
				addFilters($scope.termList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);

				// Submit
				$.ajax({
					type: "POST",
					url: "php/educational-material/insert.educational_material.php",
					data: $scope.newEduMat,
					success: function () {
						$state.go('educational-material');
					}
				});
			}
		};

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		// Function for selecting all terms in the expression list
		var selectAllTerms = false;
		$scope.selectAllTerms = function () {
			var filtered = $scope.filter($scope.termList, $scope.termSearchField);

			if (selectAllTerms) {
				angular.forEach(filtered, function (term) {
					term.added = 0;
				});
				selectAllTerms = !selectAllTerms;
			} else {
				angular.forEach(filtered, function (term) {
					term.added = 1;
				});
				selectAllTerms = !selectAllTerms;
			}
		};

		// Function to assign search fields when textbox changes
		$scope.searchTerm = function (field) {
			$scope.termSearchField = field;
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
		$scope.searchTermsFilter = function (Filter) {
			var keyword = new RegExp($scope.termSearchField, 'i');
			return !$scope.termSearchField || keyword.test(Filter.name);
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

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.newEduMat.filters.push({ id: Filter.id, type: Filter.type });
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


	});
