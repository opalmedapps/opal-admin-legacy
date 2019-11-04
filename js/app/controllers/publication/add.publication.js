angular.module('opalAdmin.controllers.publication.add', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).

/******************************************************************************
 * Add Publication Page controller
 *******************************************************************************/
controller('publication.add', function ($scope, $filter, $uibModal, publicationCollectionService, $state, educationalMaterialCollectionService, Session, hospitalMapCollectionService) {

	// Function to go to previous page
	$scope.goBack = function () {
		window.history.back();
	};

	// Default boolean variables
	$scope.selectAll = false; // select All button checked?

	$scope.sourceSection = {open:false, show:true};
	$scope.titleDescriptionSection = {open:false, show:false};
	$scope.educationalMaterialSection = {open:false, show:false};
	$scope.typeSection = {open:false, show:false};
	$scope.colorSection = {open:false, show:false};
	$scope.clinicalCodeSection = {open:false, show:false};
	$scope.hospitalMapSection = {open:false, show:false};
	$scope.checkinSection = {open: false, show:false};
	$scope.language = Session.retrieveObject('user').language;

	$scope.showAssigned = false;
	$scope.hideAssigned = false;

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
		publication: { completed: false },
		description: { completed: false },
	};

	$scope.filter = $filter('filter');

	// Default count of completed steps
	$scope.numOfCompletedSteps = 0;

	// Default total number of steps
	$scope.stepTotal = 3;

	// Progress bar based on default completed steps and total
	$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	// Initialize the new publication object
	$scope.newPublication = {
		name_EN: null,
		name_FR: null,
		moduleId: null,
		publicationId: null
	};

	$scope.demoSection = {open:false, show:false};
	$scope.publishFrequencySection = {open: false, show:false};
	$scope.triggerSection = {
		show:false,
		patient: {open:false},
		appointment: {open:false},
		appointmentStatus: {open:false},
		doctor: {open:false},
		machine: {open:false},
		diagnosis: {open:false}
	};

	$scope.publicationList = [];

	// Initialize list that will hold unassigned terms
	$scope.termList = [];
	// Initialize list that will hold educational materials
	$scope.eduMatList = [];
	// Initialize list that will hold hospital maps
	$scope.hospitalMapList = [];

	// Initialize list that will hold source databases
	$scope.moduleList = [];

	// Initialize list that will hold existing color tags
	$scope.existingColorTags = [];

	// Initialize the termFilter from NULL to single quotes
	$scope.termFilter = '';
	$scope.eduMatFilter = null;

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};

	// Call our API service to get the list of educational material
	educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
		response.data.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name_EN;
		});
		$scope.eduMatList = response.data; // Assign value
	}).catch(function(response) {
		alert($filter('translate')('PUBLICATION.ADD.ERROR_EDUCATION') + "\r\n\r\n" + response.status + " - " + response.data);
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
	}).catch(function(response) {
		alert($filter('translate')('PUBLICATION.ADD.ERROR_DATABASE') + "\r\n\r\n" + response.status + " - " + response.data);
	});

	// Call our API to get the list of existing hospital maps
	hospitalMapCollectionService.getHospitalMaps().then(function (response) {
		response.data.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name_EN;
		});
		$scope.hospitalMapList = response.data;
	}).catch(function(response) {
		alert($filter('translate')('PUBLICATION.ADD.ERROR_HOSPITAL') + "\r\n\r\n" + response.status + " - " + response.data);
	});

	// Function to toggle necessary changes when updating the source database buttons
	$scope.moduleUpdate = function (moduleSelected) {
		if(moduleSelected !== $scope.newPublication.module) {
			$scope.showProcessingModal();
			$scope.newPublication.module = moduleSelected;
			$scope.sourceSection.open = true;
			$scope.typeSection.show = true;

			steps.type.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			publicationCollectionService.getPublicationsPerModule(Session.retrieveObject('user').id, moduleSelected).then(function (response) {

				response.data.forEach(function(entry) {
					if($scope.language.toUpperCase() === "FR")
						entry.name_display = entry.name_FR;
					else
						entry.name_display = entry.name_EN;
				});

				console.log(response.data);
				$scope.publicationList = response.data; // Assign value
				processingModal.close(); // hide modal
				processingModal = null; // remove reference

			}).catch(function(response) {
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
				alert($filter('translate')('PUBLICATION.ADD.ERROR_MODULE') + "\r\n\r\n" + response.status + " - " + response.statusText + " - " + response.data.message);
				$state.go('publication');
			});

		}
	};

	$scope.updateAt = function (selectedAt) {
		$scope.answerTypeSection.open = true;
		if ($scope.newQuestion.typeId) {
			$scope.questionLibrarySection.show = true;
			$scope.selectedAt = selectedAt;
			steps.answerType.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			if (selectedAt.typeId === "2") {
				var increment = parseFloat($scope.selectedAt.increment);
				var minValue = parseFloat($scope.selectedAt.minValue);
				if (minValue === 0.0) minValue = increment;
				var maxValue = parseFloat($scope.selectedAt.maxValue);

				$scope.radiostep = new Array();
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
				for (var i = minValue; i <= maxValue; i += increment) {
					$scope.radiostep.push({"name": i});
				}
				$scope.radiostep[0]["name"] +=  " " + $scope.selectedAt.minCaption_EN + " / " + $scope.selectedAt.minCaption_FR;
				$scope.radiostep[$scope.radiostep.length - 1]["name"] += " " + $scope.selectedAt.maxCaption_EN + " / " + $scope.selectedAt.maxCaption_FR;
			}
		}
		else {

			steps.answerType.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		}
	};

	// Function to toggle necessary changes when updating publication title & description
	$scope.titleDescriptionUpdate = function () {

		$scope.titleDescriptionSection.open = true;

		if (!$scope.newPublication.name_EN && !$scope.newPublication.name_FR &&
			!$scope.newPublication.description_EN && !$scope.newPublication.description_FR) {
			$scope.titleDescriptionSection.open = false;
		}

		if ($scope.newPublication.name_EN && $scope.newPublication.name_FR &&
			$scope.newPublication.description_EN && $scope.newPublication.description_FR) { // if textboxes are not empty

			if ($scope.newPublication.type.name == 'Appointment') {
				$scope.checkinSection.show = true;
				$scope.hospitalMapSection.show = true;
			}
			else {
				// Toggle boolean
				$scope.educationalMaterialSection.show = true;
				$scope.colorSection.show = true;
			}

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
	$scope.eduMatUpdate = function (event, eduMat) {

		// Toggle booleans
		$scope.educationalMaterialSection.open = true;

		if ($scope.newPublication.eduMat) {
			if ($scope.newPublication.eduMat.serial == event.target.value) {
				$scope.newPublication.eduMat = null;
				$scope.newPublication.eduMatSer = null;
				$scope.educationalMaterialSection.open = false;
			}
			else {
				$scope.newPublication.eduMat = eduMat;
			}
		}
		else {
			$scope.newPublication.eduMat = eduMat;
		}
	}

	// Function to toggle necessary changes when updating hospital map
	$scope.hospitalMapUpdate = function (event, hospitalMap) {

		// Toggle booleans
		$scope.hospitalMapSection.open = true;

		if ($scope.newPublication.hospitalMap) {
			if ($scope.newPublication.hospitalMap.serial == event.target.value) {
				$scope.newPublication.hospitalMap = null;
				$scope.newPublication.hospitalMapSer = null;
				$scope.hospitalMapSection.open = false;
			}
			else {
				$scope.newPublication.hospitalMap = hospitalMap;
			}
		}
		else {
			$scope.newPublication.hospitalMap = hospitalMap;
		}
	}

	// Function to toggle necessary changes when updating publication type
	$scope.typeUpdate = function (type) {

		if (!$scope.newPublication.source_db)
			return;

		$scope.typeSection.open = true;

		// Set the name
		$scope.newPublication.type = type;

		// Toggle boolean
		steps.type.completed = true;

		$scope.clinicalCodeSection.show = true;
		// If terms were assigned previously, we reset that step.
		if ($scope.termList) {
			// Set false for each term in termList
			angular.forEach($scope.termList, function (term) {
				term.added = 0;
			});

			// Toggle boolean
			steps.terms.completed = false;
		}

		// Proceed with getting a list of publication expressions if a source database has been defined
		if ($scope.newPublication.source_db) {

			$scope.showProcessingModal();

			// Call our API service to get the list of publication expressions
			publicationCollectionService.getExpressions($scope.newPublication.source_db.serial, $scope.newPublication.type.name).then(function (response) {

				$scope.termList = response.data; // Assign value

				processingModal.close(); // hide modal
				processingModal = null; // remove reference

			}).catch(function(response) {
				alert($filter('translate')('PUBLICATION.ADD.ERROR_ALIAS') + "\r\n\r\n" + response.status + " - " + response.data);
			});
		}

		if (type.name != "Appointment") {
			steps.checkin.completed = true;
		}
		else {
			if ($scope.newPublication.name_EN && $scope.newPublication.name_FR &&
				$scope.newPublication.description_EN && $scope.newPublication.description_FR) { // if textboxes are not empty

				$scope.checkinSection.show = true;
				$scope.hospitalMapSection.show = true;
			}
			if (!$scope.newPublication.checkin_details.instruction_EN || !$scope.newPublication.checkin_details.instruction_FR || $scope.newPublication.checkin_details.checkin_possible == null) {
				steps.checkin.completed = false;

			}
		}

		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);

		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// Function to toggle necessary changes when updating color
	$scope.colorUpdate = function () {

		// Toggle booleans
		$scope.colorSection.open = true;

		steps.color.completed = true;

		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);

		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	}

	// Function to toggle necessary changes when checkin details
	$scope.checkinDetailsUpdate = function () {

		// Toggle booleans
		$scope.checkinSection.open = true;

		if (!$scope.newPublication.checkin_details.instruction_EN && !$scope.newPublication.checkin_details.instruction_FR
			&& $scope.newPublication.checkin_details.checkin_possible == null) {
			$scope.checkinSection.open = false;
		}

		if ($scope.newPublication.checkin_details.instruction_EN && $scope.newPublication.checkin_details.instruction_FR
			&& $scope.newPublication.checkin_details.checkin_possible != null) {

			// Toggle boolean
			$scope.educationalMaterialSection.show = true;
			$scope.colorSection.show = true;

			steps.checkin.completed = true;

		}

		else {
			steps.checkin.completed = false;
		}

		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);

		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	}

	// Function to add / remove a term to publication
	$scope.toggleTermSelection = function (term) {

		$scope.clinicalCodeSection.open = true;

		// If originally added, remove it
		if (term.added) {

			term.added = 0; // added parameter

			// Check if there are still terms added, if not, flag
			if (!$scope.checkTermsAdded($scope.termList)) {

				// Toggle boolean
				steps.terms.completed = false;

				$scope.clinicalCodeSection.open = false;

				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}

		}
		else { // Originally not added, add it

			term.added = 1;

			$scope.titleDescriptionSection.show = true;

			// Boolean
			steps.terms.completed = true;

			// Count the number of steps completed
			$scope.numOfCompletedSteps = stepsCompleted(steps);

			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		}

	};

	// Submit new publication
	$scope.submitPublication = function () {

		if ($scope.checkForm()) {

			// For some reason the HTML text fields add a zero-width-space
			// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
			$scope.newPublication.description_EN = $scope.newPublication.description_EN.replace(/\u200B/g,'');
			$scope.newPublication.description_FR = $scope.newPublication.description_FR.replace(/\u200B/g,'');

			// Fill it with the added terms from termList
			angular.forEach($scope.termList, function (term) {
				if (term.added)
					$scope.newPublication.terms.push(term);
			});

			if ($scope.newPublication.type == "Appointment") {
				$scope.newPublication.checkin_details.instruction_EN = $scope.newPublication.checkin_details.instruction_EN.replace(/\u200B/g,'');
				$scope.newPublication.checkin_details.instruction_FR = $scope.newPublication.checkin_details.instruction_FR.replace(/\u200B/g,'');
			}

			// Log who created this publication
			var currentUser = Session.retrieveObject('user');
			$scope.newPublication.user = currentUser;

			// Submit form
			$.ajax({
				type: "POST",
				url: "publication/insert/publication",
				data: $scope.newPublication,
				success: function () {
					$state.go('publication');
				},
				error: function (err) {
					alert($filter('translate')('PUBLICATION.ADD.ERROR_ADD') + "\r\n\r\n" + err.status + " - " + err.statusText);
					$state.go('publication');
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
		return ((!$scope.termFilter || keyword.test(term.name))
			&& (($scope.clinicalCodeFilter == 'all') || ($scope.clinicalCodeFilter == 'current' && term.added)
				|| ($scope.clinicalCodeFilter == 'other' && term.assigned && !term.added) || ($scope.clinicalCodeFilter == 'none' && !term.added && !term.assigned)));
	};

	// Function to assign eduMateFilter when textbox is changing
	$scope.changeEduMatFilter = function (eduMatFilter) {
		$scope.eduMatFilter = eduMatFilter;
	};

	// Function for searching through the educational material list
	$scope.searchEduMatsFilter = function (edumat) {
		var keyword = new RegExp($scope.eduMatFilter, 'i');
		return !$scope.eduMatFilter || keyword.test($scope.language.toUpperCase() === "FR"?edumat.name_FR:edumat.name_EN);
	};

	// Function to assign hospitalMapFilter when textbox is changing
	$scope.changeHospitalMapFilter = function (hospitalMapFilter) {
		$scope.hospitalMapFilter = hospitalMapFilter;
	};

	// Function for searching through the hospital map list
	$scope.searchHospitalMapsFilter = function (hospitalMap) {
		var keyword = new RegExp($scope.hospitalMapFilter, 'i');
		return !$scope.hospitalMapFilter || keyword.test($scope.language.toUpperCase() === "FR"?hospitalMap.name_FR:hospitalMap.name_EN);
	};

	$scope.clinicalCodeFilter = 'all';

	$scope.setClinicalCodeFilter = function (filter) {
		$scope.clinicalCodeFilter = filter;
	}

	// Function for selecting all terms in the expression list
	$scope.selectAllFilteredTerms = function () {

		var filtered = $scope.filter($scope.termList, $scope.searchTermsFilter);

		if ($scope.selectAll) { // was checked
			angular.forEach(filtered, function (term) {
				term.added = 0;
			});
			$scope.selectAll = false; // toggle off

			// Check if there are still terms added, if not, flag
			if (!$scope.checkTermsAdded($scope.termList)) {

				// Toggle boolean
				steps.terms.completed = false;
				$scope.clinicalCodeSection.open = false;

				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);

				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}

		}
		else { // was not checked

			angular.forEach(filtered, function (term) {
				term.added = 1;
			});

			$scope.selectAll = true; // toggle on

			// Check if there are still terms added, if not, flag
			if (!$scope.checkTermsAdded($scope.termList)) {

				// Toggle boolean
				steps.terms.completed = false;
				$scope.clinicalCodeSection.open = false;

			}
			else {
				// Boolean
				steps.terms.completed = true;
				$scope.clinicalCodeSection.open = true;
				$scope.titleDescriptionSection.show = true;
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
			if (term.added)
				addedParam = true;
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



