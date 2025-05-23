// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.alias.add', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.bootstrap.materialPicker']).

/******************************************************************************
 * Add Alias Page controller
 *******************************************************************************/
controller('alias.add', function ($scope, $filter, $uibModal, $state, Session, aliasCollectionService, ErrorHandler) {

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
		source: { completed: false },
		title_description: { completed: false },
		type: { completed: false },
		color: { completed: false },
		terms: { completed: false },
		checkin: { completed: false },
		hospital_map: { completed: false }
	};

	$scope.filter = $filter('filter');

	// Default count of completed steps
	$scope.numOfCompletedSteps = 0;

	// Default total number of steps
	$scope.stepTotal = 7;

	// Progress bar based on default completed steps and total
	$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	// Initialize the list of alias types
	$scope.aliasTypes = [
		{
			name: 'Appointment',
			name_display: $filter('translate')('ALIAS.ADD.APPOINTMENT'),
			icon: 'calendar'
		}, {
			name: 'Document',
			name_display: $filter('translate')('ALIAS.ADD.DOCUMENT'),
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
		hospitalMap: null,
		source_db: null,
		color: '',
		terms: [],
		checkin_details: {
			checkin_possible: 0,
			instruction_EN: '',
			instruction_FR: ''
		}
	};

	var arrValidationInsert = [
		$filter('translate')('ALIAS.VALIDATION.TYPE'),
		$filter('translate')('ALIAS.VALIDATION.CHECKIN'),
		$filter('translate')('ALIAS.VALIDATION.HOSPITAL'),
		$filter('translate')('ALIAS.VALIDATION.COLOR'),
		$filter('translate')('ALIAS.VALIDATION.DESCRPIPTION_EN'),
		$filter('translate')('ALIAS.VALIDATION.DESCRPIPTION_FR'),
		$filter('translate')('ALIAS.VALIDATION.EDU_MAT'),
		$filter('translate')('ALIAS.VALIDATION.NAME_EN'),
		$filter('translate')('ALIAS.VALIDATION.NAME_FR'),
		$filter('translate')('ALIAS.VALIDATION.SOURCE_DB'),
		$filter('translate')('ALIAS.VALIDATION.ALIAS_EXP'),
		$filter('translate')('ALIAS.VALIDATION.ID'),
	];

	// Initialize list that will hold unassigned terms
	$scope.termList = [];
	// Initialize list that will hold educational materials
	$scope.eduMatList = [];
	// Initialize list that will hold hospital maps
	$scope.hospitalMapList = [];

	// Initialize list that will hold source databases
	$scope.sourceDBList = [];

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
	aliasCollectionService.getEducationalMaterials().then(function (response) {
		response.data.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name_EN;
		});
		$scope.eduMatList = response.data; // Assign value
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('ALIAS.ADD.ERROR_EDUCATION'));
		$state.go('alias');
	});

	// Call our API service to get the list of source databases
	aliasCollectionService.getSourceDatabases().then(function (response) {
		$scope.sourceDBList = response.data; // Assign value
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('ALIAS.ADD.ERROR_DATABASE'));
		$state.go('alias');
	});

	// Call our API to get the list of existing hospital maps
	aliasCollectionService.getHospitalMaps().then(function (response) {
		response.data.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR")
				entry.name_display = entry.name_FR;
			else
				entry.name_display = entry.name_EN;
		});
		$scope.hospitalMapList = response.data;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('ALIAS.ADD.ERROR_HOSPITAL'));
		$state.go('alias');
	});

	// Function to toggle necessary changes when updating the source database buttons
	$scope.sourceDBUpdate = function (sourceDB) {

		// Assign value
		$scope.newAlias.source_db = sourceDB;

		// Toggle boolean
		$scope.sourceSection.open = true;
		$scope.typeSection.show = true;

		steps.source.completed = true;

		// If terms were assigned previously, we reset that step.
		if ($scope.termList) {
			// Set false for each term in termList
			angular.forEach($scope.termList, function (term) {
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


			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('ALIAS.ADD.ERROR_ALIAS'));
				$state.go('alias');
			}).finally(function() {
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			});
		}

		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);

		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	};

	// Function to toggle necessary changes when updating alias title & description
	$scope.titleDescriptionUpdate = function () {

		$scope.titleDescriptionSection.open = true;

		if (!$scope.newAlias.name_EN && !$scope.newAlias.name_FR &&
			!$scope.newAlias.description_EN && !$scope.newAlias.description_FR) {
			$scope.titleDescriptionSection.open = false;
		}

		if ($scope.newAlias.name_EN && $scope.newAlias.name_FR &&
			$scope.newAlias.description_EN && $scope.newAlias.description_FR) { // if textboxes are not empty

			if ($scope.newAlias.type.name == 'Appointment') {
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

		if ($scope.newAlias.eduMat) {
			if ($scope.newAlias.eduMat.serial == event.target.value) {
				$scope.newAlias.eduMat = null;
				$scope.newAlias.eduMatSer = null;
				$scope.educationalMaterialSection.open = false;
			}
			else {
				$scope.newAlias.eduMat = eduMat;
			}
		}
		else {
			$scope.newAlias.eduMat = eduMat;
		}
	}

	// Function to toggle necessary changes when updating hospital map
	$scope.hospitalMapUpdate = function (event, hospitalMap) {

		// Toggle booleans
		$scope.hospitalMapSection.open = true;

		// unselect clicked hospital map if it is already selected
		if ($scope.newAlias.hospitalMap && $scope.newAlias.hospitalMap.serial == event.target.value) {
			$scope.newAlias.hospitalMap = null;
			$scope.newAlias.hospitalMapSer = null;
			$scope.hospitalMapSection.open = false;

			steps.hospital_map.completed = false;
		}

		if ($scope.newAlias.hospitalMap || $scope.hospitalMapSection.open) {
			$scope.newAlias.hospitalMap = hospitalMap;

			steps.hospital_map.completed = true;
		}


		// Count the number of completed steps
		$scope.numOfCompletedSteps = stepsCompleted(steps);

		// Change progress bar
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	}

	// Function to toggle necessary changes when updating alias type
	$scope.typeUpdate = function (type) {

		if (!$scope.newAlias.source_db)
			return;

		$scope.typeSection.open = true;

		// Set the name
		$scope.newAlias.type = type;

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

		// Proceed with getting a list of alias expressions if a source database has been defined
		if ($scope.newAlias.source_db) {

			$scope.showProcessingModal();

			// Call our API service to get the list of alias expressions
			aliasCollectionService.getExpressions($scope.newAlias.source_db.serial, $scope.newAlias.type.name).then(function (response) {
				$scope.termList = response.data; // Assign value

			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('ALIAS.ADD.ERROR_ALIAS'));
				$state.go('alias');
			}).finally(function() {
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			});
		}

		if (type.name != "Appointment") {
			steps.checkin.completed = true;
			steps.hospital_map.completed = true;
		}
		else {
			if ($scope.newAlias.name_EN && $scope.newAlias.name_FR &&
				$scope.newAlias.description_EN && $scope.newAlias.description_FR) { // if textboxes are not empty

				$scope.checkinSection.show = true;
				$scope.hospitalMapSection.show = true;
			}
			if (!$scope.newAlias.checkin_details.instruction_EN || !$scope.newAlias.checkin_details.instruction_FR || $scope.newAlias.checkin_details.checkin_possible == null) {
				steps.checkin.completed = false;

			}
			if (!$scope.newAlias.hospitalMap) {
				steps.hospital_map.completed = false;
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

		if (!$scope.newAlias.checkin_details.instruction_EN && !$scope.newAlias.checkin_details.instruction_FR
			&& $scope.newAlias.checkin_details.checkin_possible == null) {
			$scope.checkinSection.open = false;
		}

		if ($scope.newAlias.checkin_details.instruction_EN && $scope.newAlias.checkin_details.instruction_FR
			&& $scope.newAlias.checkin_details.checkin_possible != null) {

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

	// Function to add / remove a term to alias
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

	// Submit new alias
	$scope.submitAlias = function () {

		if ($scope.checkForm()) {
			let toSubmit = {
				"checkin_details" : $scope.newAlias.checkin_details,
				"color" : $scope.newAlias.color,
				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				"description_EN" : $scope.newAlias.description_EN.replace(/\u200B/g,''),
				"description_FR" : $scope.newAlias.description_FR.replace(/\u200B/g,''),
				"eduMat" : (typeof $scope.newAlias.eduMatSer !== "undefined" ? $scope.newAlias.eduMatSer: null),
				"hospitalMap" : (typeof $scope.newAlias.hospitalMapSer !== "undefined" ? $scope.newAlias.hospitalMapSer: null),
				"name_EN" : $scope.newAlias.name_EN,
				"name_FR" : $scope.newAlias.name_FR,
				"source_db" : $scope.newAlias.source_db.serial,
				"type" : $scope.newAlias.type.name,
				"terms" : []
			};

			angular.forEach($scope.termList, function (term) {
				if (term.added)
					toSubmit.terms.push(term.masterSourceAliasId);
			});


			if ($scope.newAlias.type.name == "Appointment") {
				toSubmit.checkin_details.instruction_EN = $scope.newAlias.checkin_details.instruction_EN.replace(/\u200B/g,'');
				toSubmit.checkin_details.instruction_FR = $scope.newAlias.checkin_details.instruction_FR.replace(/\u200B/g,'');
			}

			// Log who created this alias
			const currentUser = Session.retrieveObject('user');
			toSubmit.user = currentUser;

			// Submit form
			$.ajax({
				type: "POST",
				url: "alias/insert/alias",
				data: toSubmit,
				dataType: 'json',
				success: function () {},
				error: function (err) {
					if (err.responseText && typeof err.responseText == 'string') {
						err.responseText = JSON.parse(err.responseText);
					}
					ErrorHandler.onError(err, $filter('translate')('ALIAS.ADD.ERROR_ADD'), arrValidationInsert);
				},
				complete: function() {
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
		return ((!$scope.termFilter || keyword.test(term.description) || keyword.test(term.externalId) || keyword.test(term.name))
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
