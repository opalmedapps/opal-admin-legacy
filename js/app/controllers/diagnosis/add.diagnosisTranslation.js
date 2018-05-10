angular.module('opalAdmin.controllers.diagnosisTranslation.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	* Add Diagnosis Translation Page controller 
	*******************************************************************************/
	controller('diagnosisTranslation.add', function ($scope, $filter, $uibModal, diagnosisCollectionService, $state, educationalMaterialCollectionService, Session) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default boolean variables
		$scope.showAssigned = false;
		$scope.hideAssigned = false;

		// Default toolbar for wysiwyg
		$scope.toolbar = [ 
			['h1', 'h2', 'h3', 'p'],
      		['bold', 'italics', 'underline', 'ul', 'ol'],
      		['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
      		['html', 'insertLink']
      	];

		$scope.diagnosesSection = {open:false, show: true};
		$scope.titleDescriptionSection = {open:false, show:false};
		$scope.educationalMaterialSection = {open:false, show:false};

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
				templateUrl: 'templates/processingModal.html',
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
			angular.forEach($scope.diagnosisList, function (diagnosis) {
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

					$scope.diagnosesSection.open = false;

					// Toggle boolean
					steps.diagnoses.completed = false;

					// Count the number of completed steps
					$scope.numOfCompletedSteps = stepsCompleted(steps);

					// Change progress bar
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

				}

			}
			else { // Originally not added, add it

				diagnosis.added = 1;

				// Boolean
				steps.diagnoses.completed = true;

				$scope.diagnosesSection.open = true;
				$scope.titleDescriptionSection.show = true;

				// Count the number of steps completed
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}

		};

		// Function to toggle necessary changes when updating title and description
		$scope.titleDescriptionUpdate = function () {

			$scope.titleDescriptionSection.open = true;

			if ($scope.newDiagnosisTranslation.name_EN && $scope.newDiagnosisTranslation.name_FR &&
				$scope.newDiagnosisTranslation.description_EN && $scope.newDiagnosisTranslation.description_FR) {

				// Toggle step completion
				steps.title_description.completed = true;
				$scope.educationalMaterialSection.show = true;

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
		$scope.eduMatUpdate = function (event, eduMat) {

			// Toggle booleans
			$scope.educationalMaterialSection.open = true;

			if ($scope.newDiagnosisTranslation.eduMat) {
				if ($scope.newDiagnosisTranslation.eduMat.serial == event.target.value) {
					$scope.newDiagnosisTranslation.eduMat = null;
					$scope.newDiagnosisTranslation.eduMatSer = null;
					$scope.educationalMaterialSection.open = false;
				}
				else {
					$scope.newDiagnosisTranslation.eduMat = eduMat;
				}
			}
			else {
				$scope.newDiagnosisTranslation.eduMat = eduMat;
			}
		}

		// Function to submit the new diagnosis translation
		$scope.submitDiagnosisTranslation = function () {
			if ($scope.checkForm()) {

				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.newDiagnosisTranslation.description_EN = $scope.newDiagnosisTranslation.description_EN.replace(/\u200B/g,'');
				$scope.newDiagnosisTranslation.description_FR = $scope.newDiagnosisTranslation.description_FR.replace(/\u200B/g,'');

				// Fill in the diagnosis from diagnosisList
				angular.forEach($scope.diagnosisList, function (diagnosis) {
					if (diagnosis.added)
						$scope.newDiagnosisTranslation.diagnoses.push(diagnosis);
				});

				// Log who created diagnosis translation
				var currentUser = Session.retrieveObject('user');
				$scope.newDiagnosisTranslation.user = currentUser;

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
			$scope.selectAll = false; // uncheck select all 
		};

		// Function for search through the diagnoses
		$scope.searchDiagnosesFilter = function (Filter) {
			var keyword = new RegExp($scope.diagnosisFilter, 'i');
			return ((!$scope.diagnosisFilter || keyword.test(Filter.name)) && (($scope.diagnosisCodeFilter == 'all') || ($scope.diagnosisCodeFilter == 'current' && Filter.added)
					|| ($scope.diagnosisCodeFilter == 'other' && Filter.assigned && !Filter.added) || ($scope.diagnosisCodeFilter == 'none' && !Filter.added && !Filter.assigned)));
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

		$scope.diagnosisCodeFilter = 'all';

		$scope.setDiagnosisCodeFilter = function (filter) {
			$scope.diagnosisCodeFilter = filter;
		}

		// Function for selecting all codes in the diagnosis list
		$scope.selectAllFilteredDiagnoses = function () {

			var filtered = $scope.filter($scope.diagnosisList, $scope.searchDiagnosesFilter);

			if ($scope.selectAll) { // was checked
				angular.forEach(filtered, function (diagnosis) {
					diagnosis.added = 0;
				});
				$scope.selectAll = false; // toggle off

				// Check if there are still terms added, if not, flag
				if (!$scope.checkDiagnosesAdded($scope.diagnosisList)) {
					
					// Toggle boolean
					steps.diagnoses.completed = false;
					$scope.diagnosesSection.open = false;

					// Count the number of completed steps
					$scope.numOfCompletedSteps = stepsCompleted(steps);

					// Change progress bar
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

				}

			}
			else { // was not checked
				
				angular.forEach(filtered, function (diagnosis) {
					diagnosis.added = 1;
				});

				$scope.selectAll = true; // toggle on

				// Check if there are still terms added, if not, flag
				if (!$scope.checkDiagnosesAdded($scope.diagnosisList)) {
					
					// Toggle boolean
					steps.diagnoses.completed = false;
					$scope.diagnosesSection.open = false;

				}
				else {
					// Boolean
					steps.diagnoses.completed = true;
					$scope.diagnosesSection.open = true;
					$scope.titleDescriptionSection.show = true;
				}

				// Count the number of steps completed
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
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
