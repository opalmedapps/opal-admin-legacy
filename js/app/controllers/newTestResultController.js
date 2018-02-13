angular.module('opalAdmin.controllers.newTestResultController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	/******************************************************************************
	* Add Test Result Page controller 
	*******************************************************************************/
	controller('newTestResultController', function ($scope, $filter, $sce, $state, $uibModal, testResultCollectionService, educationalMaterialCollectionService) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// default boolean
		$scope.tests = {open: false, show: true};
		$scope.title_description = {open: false, show: false};
		$scope.group = {open: false, show: false};
		$scope.edumat = {open: false, show: false};
		$scope.additional_links = {open: false, show: false};

		// completed steps boolean object; used for progress bar
		var steps = {
			tests: { completed: false },
			title_description: { completed: false },
			group: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 3;

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

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize search field variable
		$scope.testFilter = "";

		// Initialize a list for tests
		$scope.testList = [];

		// Initialize the new test result object
		$scope.newTestResult = {
			name_EN: null,
			name_FR: null,
			description_EN: null,
			description_FR: null,
			group_EN: "",
			group_FR: "",
			eduMat: null,
			tests: [],
			additional_links: []
		};

		// Initialize lists to hold distinct test groups 
		$scope.TestResultGroups_EN = [];
		$scope.TestResultGroups_FR = [];
		// Initialize list that will hold educational materials
		$scope.eduMatList = [];


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

		// Call our API service to get the list of educational material
		educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
			$scope.eduMatList = response.data; // Assign value
		}).catch(function(response) {
			console.error('Error occurred getting educational materials:', response.status, response.data);
		});

		// Call our API to get the list of test groups
		testResultCollectionService.getTestResultGroups().then(function (response) {

			$scope.TestResultGroups_EN = response.data.EN;
			$scope.TestResultGroups_FR = response.data.FR;

		}).catch(function(response) {
			console.error('Error occurred getting test result groups:', response.status, response.data);
		});

		// Call our API to get the list of tests
		testResultCollectionService.getTestNames().then(function (response) {

			$scope.testList = response.data;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

			$scope.formLoaded = true;
			$scope.loadForm();
		}).catch(function(response) {
			console.error('Error occurred getting test names:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating title and description
		$scope.titleDescriptionUpdate = function () {

			$scope.title_description.open = true;

			if ($scope.newTestResult.name_EN && $scope.newTestResult.name_FR &&
				$scope.newTestResult.description_EN && $scope.newTestResult.description_FR) {

				// Toggle step completion
				steps.title_description.completed = true;
				$scope.group.show = true;

				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle step completion
				steps.title_description.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating groups
		$scope.groupUpdate = function () {

			$scope.group.open = true; 

			if ($scope.newTestResult.group_EN && $scope.newTestResult.group_FR) {

				$scope.edumat.show = true;
				$scope.additional_links.show = true;

				// Toggle step completion
				steps.group.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle step completion
				steps.group.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating educational material
		$scope.eduMatUpdate = function () {

			// Toggle booleans
			$scope.edumat.open = true;
		}

		$scope.additionalLinksComplete = false;
		// Function to toggle necessary changes when updating the additional links
		$scope.additionalLinkUpdate = function () {

			$scope.additional_links.open = true;

			$scope.additionalLinksComplete = true;

			if (!$scope.newTestResult.additional_links.length) {
				$scope.additionalLinksComplete = false;

			} else {

				angular.forEach($scope.newTestResult.additional_links, function (link) {
					if (!link.name_EN || !link.name_FR || !link.url_EN
						|| !link.url_FR) {
						$scope.additionalLinksComplete = false;
					}
				});

			}

			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to return boolean for # of added tests
		$scope.checkTestsAdded = function (testList) {

			var addedParam = false;
			angular.forEach(testList, function (test) {
				if (test.added)
					addedParam = true;
			});
			if (addedParam)
				return true;
			else
				return false;
		};

		// Function to add / remove a test
		$scope.toggleTestSelection = function (test) {

			// If originally added, remove it
			if (test.added) {

				test.added = 0; // added parameter

				// Check if there are still tests added, if not, flag
				if (!$scope.checkTestsAdded($scope.testList)) {

					$scope.tests.open = false;

					// Toggle boolean
					steps.tests.completed = false;

					// Count the number of completed steps
					$scope.numOfCompletedSteps = stepsCompleted(steps);

					// Change progress bar
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

				}

			}
			else { // Orignally not added, add it

				test.added = 1;

				// Boolean
				steps.tests.completed = true;

				$scope.tests.open = true;
				$scope.title_description.show = true;

				// Count the number of steps completed
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}

		};

		// Function to add additioanl links to newTestResult object
		$scope.addAdditionalLink = function () {
			$scope.newTestResult.additional_links.push({
				name_EN: "",
				name_FR: "",
				url_EN: "",
				url_FR: ""
			});
			$scope.additionalLinkUpdate();
		};

		// Function to remove additional link from newTestResult object
		$scope.removeAdditionalLink = function (index) {
			$scope.newTestResult.additional_links.splice(index, 1);
			$scope.additionalLinkUpdate();
		};

		// Function to submit the new test result
		$scope.submitTestResult = function () {
			if ($scope.checkForm()) {

				// Fill in the tests from testList
				angular.forEach($scope.testList, function (test) {
					if (test.added)
						$scope.newTestResult.tests.push(test);
				});

				// Submit form
				$.ajax({
					type: 'POST',
					url: 'php/test-result/insert.test_result.php',
					data: $scope.newTestResult,
					success: function () {
						$state.go('test-result');
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

		// Function to assign search field when textbox changes
		$scope.changeTestFilter = function (field) {
			$scope.testFilter = field;
		};

		// Function for search through the test names
		$scope.searchTestsFilter = function (Filter) {
			var keyword = new RegExp($scope.testFilter, 'i');
			return !$scope.testFilter || keyword.test(Filter.name);
		};

		// Function to assign eduMateFilter when textbox is changing 
		$scope.changeEduMatFilter = function (eduMatFilter) {
			$scope.eduMatFilter = eduMatFilter;
		};

		// Function for searching through the educational material list
		$scope.searchEduMatsFilter = function (edumat) {
			var keyword = new RegExp($scope.eduMatFilter, 'i');
			return !$scope.eduMatFilter || keyword.test(edumat.name_EN);
		};


		// Function to return boolean for form completion
		$scope.checkForm = function () {
			if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100) {
				if ($scope.newTestResult.additional_links.length && !$scope.additionalLinksComplete) {
					return false;
				}
				else
					return true;
			}
			else
				return false;
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
