angular.module('opalAdmin.controllers.newLegacyQuestionnaireController', ['ngAnimate', 'ui.bootstrap']).


	/******************************************************************************
	* New Legacy Questionnaire Page controller 
	*******************************************************************************/
	controller('newLegacyQuestionnaireController', function($scope, $filter, $uibModal, legacyQuestionnaireCollectionService, $state, filterCollectionService) {
       
       // Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default boolean variables
		var selectAll = false; // select All button checked?

		$scope.legacy_questionnaire = {open:false, show:true};
		$scope.title = {open:false, show:false};
		$scope.demo = {open:false, show:false};
		$scope.terms = {open:false, show:false};

		// completed steps boolean object; used for progress bar
		var steps = {
			title: { completed: false },
			legacy_questionnaire: { completed: false }
		};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize search field variables
		$scope.termSearchField = null;
		$scope.dxSearchField = null;
		$scope.doctorSearchField = null;
		$scope.resourceSearchField = null;

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
		$scope.termList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];

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

			$scope.termList = response.data.expressions; // Assign value
			$scope.dxFilterList = response.data.dx;
			$scope.doctorFilterList = response.data.doctors;
			$scope.resourceFilterList = response.data.resources;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

			$scope.formLoaded = true;
			$scope.loadForm();

		}).catch(function(response) {
			console.error('Error occurred getting filter list:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating the legacy questionnaire
		$scope.legacyQuestionnaireUpdate = function () {

			$scope.legacy_questionnaire.open = true;

			$scope.title.show = true;

			// Toggle step completion
			steps.legacy_questionnaire.completed = true;
			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};


		// Function to toggle necessary changes when updating post name
		$scope.titleUpdate = function () {

			$scope.title.open = true;

			if ($scope.newLegacyQuestionnaire.name_EN && $scope.newLegacyQuestionnaire.name_FR) {

				$scope.demo.show = true;
				$scope.terms.show = true;

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
				addFilters($scope.termList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
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

	});

