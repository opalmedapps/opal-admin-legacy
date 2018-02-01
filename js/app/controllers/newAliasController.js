angular.module('opalAdmin.controllers.newAliasController', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).

	/******************************************************************************
	* Add Alias Page controller 
	*******************************************************************************/
	controller('newAliasController', function ($scope, $filter, $uibModal, aliasCollectionService, $state, educationalMaterialCollectionService) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default boolean variables
		$scope.selectAll = false; // select All button checked?

		$scope.source = {open:false, show:true};
		$scope.title_description = {open:false, show:false};
		$scope.edumat = {open:false, show:false};
		$scope.type = {open:false, show:false};
		$scope.color = {open:false, show:false};
		$scope.terms = {open:false, show:false};

		$scope.showAssigned = false;
		$scope.hideAssigned = false;

		// completed steps in object notation
		var steps = {
			source: { completed: false },
			title_description: { completed: false },
			type: { completed: false },
			color: { completed: false },
			terms: { completed: false }
		};

		$scope.filter = $filter('filter');

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps
		$scope.stepTotal = 5;

		// Progress bar based on default completed steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Initialize the list of alias types
		$scope.aliasTypes = [
			{
				name: 'Task',
				icon: 'th-list'
			}, {
				name: 'Appointment',
				icon: 'calendar'
			}, {
				name: 'Document',
				icon: 'folder-open'
			}
		];

		// Initialize the new alias object
		$scope.newAlias = {
			name_EN: null,
			name_FR: null,
			description_EN: null,
			description_FR: null,
			type: null,
			eduMat: null,
			source_db: null,
			color: '',
			terms: []
		};

		// Initialize list that will hold unassigned terms
		$scope.termList = [];
		// Initialize list that will hold educational materials
		$scope.eduMatList = [];

		// Initialize list that will hold source databases
		$scope.sourceDBList = [];

		// Initialize list that will hold existing color tags
		$scope.existingColorTags = [];

		// Initialize the termFilter from NULL to single quotes 
		$scope.termFilter = '';
		$scope.eduMatFilter = null;

		/* Function for the "Processing" dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'processingModal.htm',
				backdrop: 'static',
				keyboard: false,
			});
		};

		// Call our API service to get the list of educational material
		educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
			$scope.eduMatList = response.data; // Assign value
		}).catch(function(response) {
			console.error('Error occurred getting educational materials:', response.status, response.data);
		});

		// Call our API service to get the list of source databases
		aliasCollectionService.getSourceDatabases().then(function (response) {
			$scope.sourceDBList = response.data; // Assign value
		}).catch(function(response) {
			console.error('Error occurred getting source database list:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating the source database buttons
		$scope.sourceDBUpdate = function (sourceDB) {

			// Assign value
			$scope.newAlias.source_db = sourceDB;

			// Toggle boolean
			$scope.source.open = true;
			$scope.type.show = true;

			steps.source.completed = true;

			// If terms were assigned previously, we reset that step.
			if ($scope.termList) {
				// Set false for each term in termList
				angular.forEach($scope.termList, function (term) {
					// ignore already assigned terms
					if (!term.assigned)
						term.added = 0;
				});

				// Toggle boolean
				steps.terms.completed = false;
			}

			// Proceed with getting a list of alias expressions if a typee has been defined
			if ($scope.newAlias.type) {

				$scope.showProcessingModal();

				// Call our API service to get the list of alias expressions
				aliasCollectionService.getExpressions($scope.newAlias.source_db.serial, $scope.newAlias.type.name).then(function (response) {

					$scope.termList = response.data; // Assign value

					processingModal.close(); // hide modal
					processingModal = null; // remove reference

				}).catch(function(response) {
					console.error('Error occurred getting alias expressions:', response.status, response.data);
				});
			}

			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);

			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};

		// Function to toggle necessary changes when updating alias title & description
		$scope.titleDescriptionUpdate = function () {

			$scope.title_description.open = true;

			if (!$scope.newAlias.name_EN && !$scope.newAlias.name_FR &&
			!$scope.newAlias.description_EN && !$scope.newAlias.description_FR) {
				$scope.title_description.open = false;
			}

			if ($scope.newAlias.name_EN && $scope.newAlias.name_FR &&
			$scope.newAlias.description_EN && $scope.newAlias.description_FR) { // if textboxes are not empty

				// Toggle boolean
				$scope.edumat.show = true;
				$scope.color.show = true;

				steps.title_description.completed = true;

				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
			else { // at least one textbox is empty

				// Toggle boolean
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

		// Function to toggle necessary changes when updating alias type
		$scope.typeUpdate = function (type) {

			if (!$scope.newAlias.source_db)
				return;

			$scope.type.open = true;
			$scope.terms.show = true;

			// Set the name
			$scope.newAlias.type = type;

			// Toggle boolean
			steps.type.completed = true;

			// If terms were assigned previously, we reset that step.
			if ($scope.termList) {
				// Set false for each term in termList
				angular.forEach($scope.termList, function (term) {
					// ignore already assigned terms
					if (!term.assigned)
						term.added = 0;
				});

				// Toggle boolean
				steps.terms.completed = false;
			}

			// Proceed with getting a list of alias expressions if a source database has been defined
			if ($scope.newAlias.source_db) {

				$scope.showProcessingModal();

				// Call our API service to get the list of alias expressions
				aliasCollectionService.getExpressions($scope.newAlias.source_db.serial, $scope.newAlias.type.name).then(function (response) {

					$scope.termList = response.data; // Assign value

					processingModal.close(); // hide modal
					processingModal = null; // remove reference

				}).catch(function(response) {
					console.error('Error occurred getting alias expressions:', response.status, response.data);
				});
			}

			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);

			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle necessary changes when updating color
		$scope.colorUpdate = function () {

			// Toggle booleans
			$scope.color.open = true;

			steps.color.completed = true;

			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);

			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		}

		// Function to add / remove a term to alias
		$scope.toggleTermSelection = function (term) {

			$scope.terms.open = true;

			// If originally added, remove it
			if (term.added) {

				term.added = 0; // added parameter

				// Check if there are still terms added, if not, flag
				if (!$scope.checkTermsAdded($scope.termList)) {

					// Toggle boolean
					steps.terms.completed = false;

					$scope.terms.open = false;

					// Count the number of completed steps
					$scope.numOfCompletedSteps = stepsCompleted(steps);

					// Change progress bar
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

				}

			}
			else { // Originally not added, add it

				term.added = 1;

				$scope.title_description.show = true;

				// Boolean
				steps.terms.completed = true;

				// Count the number of steps completed
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}

		};



		// Submit new alias
		$scope.submitAlias = function () {

			if ($scope.checkForm()) {

				// Fill it with the added terms from termList
				angular.forEach($scope.termList, function (term) {
					// ignore already assigned terms
					if (!term.assigned) {
						if (term.added)
							$scope.newAlias.terms.push(term.id);
					}
				});

				// Submit form
				$.ajax({
					type: "POST",
					url: "php/alias/insert.alias.php",
					data: $scope.newAlias,
					success: function () {
						$state.go('alias');
					}
				});
			}
		};

		// Function to assign termFilter when textbox is changing 
		$scope.changeTermFilter = function (termFilter) {
			$scope.termFilter = termFilter;
			$scope.selectAll = false;
		};

		// Function for searching through the expression list
		$scope.searchTermsFilter = function (term) {
			var keyword = new RegExp($scope.termFilter, 'i');
			return (!$scope.termFilter || keyword.test(term.name)) && (!$scope.showAssigned || term.assigned) && (!$scope.hideAssigned || !term.assigned);
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

		// Function to enable "Show all" 
		$scope.changeShowAssigned = function () {
			$scope.showAssigned = true;
			$scope.hideAssigned = false;
		}

		// Function to enable "Show only assigned" tab 
		$scope.changeShowUnassigned = function () {
			$scope.hideAssigned = true;
			$scope.showAssigned = false;
		}

		// Function to enable "Show only unassigned" tab 
		$scope.changeShowAll = function () {
			$scope.showAssigned = false;
			$scope.hideAssigned = false;
		}

		// Function for selecting all terms in the expression list
		$scope.selectAllFilteredTerms = function () {

			var filtered = $scope.filter($scope.termList, $scope.searchTermsFilter);
			
			if ($scope.selectAll) { // was checked
				angular.forEach(filtered, function (term) {
					// ignore already assigned terms
					if (!term.assigned)
						term.added = 0;
				});
				$scope.selectAll = false; // toggle off

				// Check if there are still terms added, if not, flag
				if (!$scope.checkTermsAdded($scope.termList)) {
					
					// Toggle boolean
					steps.terms.completed = false;
					$scope.terms.open = false;

					// Count the number of completed steps
					$scope.numOfCompletedSteps = stepsCompleted(steps);

					// Change progress bar
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

				}

			}
			else { // was not checked
				
				angular.forEach(filtered, function (term) {
					// ignore already assigned terms
					if (!term.assigned)
						term.added = 1;
				});

				$scope.selectAll = true; // toggle on

				// Check if there are still terms added, if not, flag
				if (!$scope.checkTermsAdded($scope.termList)) {
					
					// Toggle boolean
					steps.terms.completed = false;
					$scope.terms.open = false;

				}
				else {
					// Boolean
					steps.terms.completed = true;
					$scope.terms.open = true;
					$scope.title_description.show = true;
				}

				// Count the number of steps completed
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
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

		// Function to return boolean for # of added terms
		$scope.checkTermsAdded = function (termList) {
			var addedParam = false;
			angular.forEach(termList, function (term) {
				// ignore already assigned terms
				if (!term.assigned) {
					if (term.added)
						addedParam = true;
				}
			});
			if (addedParam)
				return true;
			else
				return false;
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {

			if ($scope.stepProgress == 100) {
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

	});



