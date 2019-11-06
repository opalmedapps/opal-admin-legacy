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

	$scope.moduleSection = {open:false, show:true};
	$scope.materialSection = {open:false, show:false};
	$scope.publicationNameSections = {open:false, show:false};

	$scope.language = Session.retrieveObject('user').language;

	$scope.showAssigned = false;
	$scope.hideAssigned = false;

	// Initialize search field variables
	$scope.atEntered = '';

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
		material: { completed: false },
		publicationName: { completed: false },
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
		ID: null,
		name_EN: null,
		name_FR: null,
		moduleId: null,
		moduleName: null,
		materialName: null,
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

	// Initialize list that will hold source databases
	$scope.moduleList = [];

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};

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

	// Function to toggle necessary changes when click on module buttons
	$scope.moduleUpdate = function (moduleSelected, moduleName) {
		if(moduleSelected !== $scope.newPublication.moduleId) {
			$scope.showProcessingModal();
			$scope.newPublication.moduleId = moduleSelected;
			$scope.newPublication.moduleName = moduleName;
			$scope.newPublication.ID = null;
			$scope.moduleSection.open = true;
			$scope.materialSection.show = true;
			$scope.newPublication.materialName = null;
			steps.type.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			publicationCollectionService.getPublicationsPerModule(Session.retrieveObject('user').id, moduleSelected).then(function (response) {

				response.data["publications"].forEach(function(entry) {
					if($scope.language.toUpperCase() === "FR")
						entry.name_display = entry.name_FR;
					else
						entry.name_display = entry.name_EN;
				});

				$scope.publicationList = response.data["publications"]; // Assign value
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

	$scope.updateMaterial = function (selectedAt) {
		$scope.newPublication.ID = selectedAt.ID;
		$scope.materialSection.open = true;
		 $scope.publicationNameSections.show = true;
		 $scope.newPublication.materialName = selectedAt.name_display;
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

	$scope.searchAt = function (field) {
		$scope.atEntered = field;
	};

	//search function
	$scope.searchAtFilter = function (Filter) {
		var keyword = new RegExp($scope.atEntered, 'i');
		return !$scope.atEntered || keyword.test(Filter.name_display);
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



