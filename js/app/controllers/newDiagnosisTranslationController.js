angular.module('opalAdmin.controllers.newDiagnosisTranslationController', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	* Add Diagnosis Translation Page controller 
	*******************************************************************************/
	controller('newDiagnosisTranslationController', function ($scope, $filter, $uibModal, diagnosisCollectionService, $state, educationalMaterialCollectionService) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default boolean variables
		var selectAll = false; // select All button checked?

		$scope.diagnoses = {open:false, show: true};
		$scope.title_description = {open:false, show:false};
		$scope.edumat = {open:false, show:false};

		// completed steps booleans - used for progress bar
		var steps = {
			diagnoses: { completed: false },
			title_description: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 2;

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
		$scope.diagnosisFilter = "";

		// Initialize a list for diagnoses
		$scope.diagnosisList = [];

		// Initialize the new diagnosis translation object
		$scope.newDiagnosisTranslation = {
			name_EN: null,
			name_FR: null,
			description_EN: null,
			description_FR: null,
			eduMat: null,
			diagnoses: []
		};

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

		// Call our API to ge the list of diagnoses
		diagnosisCollectionService.getDiagnoses().then(function (response) {
			$scope.diagnosisList = response.data;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

			$scope.formLoaded = true;
			$scope.loadForm();
		}).catch(function(response) {
			console.error('Error occurred getting diagnoses:', response.status, response.data);
		});

		// Function to return boolean for # of added diagnoses
		$scope.checkDiagnosesAdded = function (diagnosisList) {

			var addedParam = false;
			angular.forEach(diagnosisList, function (diagnosis) {
				if (diagnosis.added)
					addedParam = true;
			});
			if (addedParam)
				return true;
			else
				return false;
		};

		// Function to add / remove a diagnosis
		$scope.toggleDiagnosisSelection = function 	(diagnosis) {

			// If originally added, remove it
			if 	(diagnosis.added) {

				diagnosis.added = 0; // added parameter

				// Check if there are still diagnoses added, if not, flag
				if (!$scope.checkDiagnosesAdded($scope.diagnosisList)) {

					$scope.diagnoses.open = false;

					// Toggle boolean
					steps.diagnoses.completed = false;

					// Count the number of completed steps
					$scope.numOfCompletedSteps = stepsCompleted(steps);

					// Change progress bar
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

				}

			}
			else { // Orignally not added, add it

				diagnosis.added = 1;

				// Boolean
				steps.diagnoses.completed = true;

				$scope.diagnoses.open = true;
				$scope.title_description.show = true;

				// Count the number of steps completed
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}

		};

		// Function to toggle necessary changes when updating title and description
		$scope.titleDescriptionUpdate = function () {

			$scope.title_description.open = true;

			if ($scope.newDiagnosisTranslation.name_EN && $scope.newDiagnosisTranslation.name_FR &&
				$scope.newDiagnosisTranslation.description_EN && $scope.newDiagnosisTranslation.description_FR) {

				// Toggle step completion
				steps.title_description.completed = true;
				$scope.edumat.show = true;

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

		// Function to toggle necessary changes when updating educational material
		$scope.eduMatUpdate = function () {

			// Toggle booleans
			$scope.edumat.open = true;
		}

		// Function to submit the new diagnosis translation
		$scope.submitDiagnosisTranslation = function () {
			if ($scope.checkForm()) {

				// Fill in the diagnosis from diagnosisList
				angular.forEach($scope.diagnosisList, function (diagnosis) {
					if (diagnosis.added)
						$scope.newDiagnosisTranslation.diagnoses.push(diagnosis);
				});

				// Submit form
				$.ajax({
					type: 'POST',
					url: 'php/diagnosis-translation/insert.diagnosis_translation.php',
					data: $scope.newDiagnosisTranslation,
					success: function () {
						$state.go('diagnosis-translation');
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
		$scope.changeDiagnosisFilter = function (field) {
			$scope.diagnosisFilter = field;
		};

		// Function for search through the diagnoses
		$scope.searchDiagnosesFilter = function (Filter) {
			var keyword = new RegExp($scope.diagnosisFilter, 'i');
			return !$scope.diagnosisFilter || keyword.test(Filter.name);
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
			if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
				return true;
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

	});
