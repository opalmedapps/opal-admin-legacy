angular.module('opalAdmin.controllers.educationalMaterial.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).

controller('educationalMaterial.edit', function ($scope, $filter, $sce, $uibModal, $uibModalInstance, $state, educationalMaterialCollectionService, uiGridConstants, Session, ErrorHandler) {

	// Default Booleans
	$scope.changesMade = false; // changes have been made?

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');

	$scope.eduMat = {}; // initialize edumat object

	$scope.language = Session.retrieveObject('user').language;
	$scope.tocsComplete = true;

	// Initialize lists to hold the distinct edu material types
	$scope.EduMatTypes = [];

	// Call our API to get the list of edu material types
	// Call our API to get the list of edu material types
	educationalMaterialCollectionService.getEducationalMaterialTypes().then(function (response) {
		$scope.EduMatTypes = response.data;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('EDUCATION.ADD.ERROR_TYPES'));
	});

	$scope.bannerMessageModal = "";

	// Function to show page banner
	$scope.showBannerModal = function () {
		$(".bannerMessageModal").slideDown(function () {
			setTimeout(function () {
				$(".bannerMessageModal").slideUp();
			}, 5000);
		});
	};

	// Function to set banner class
	$scope.setBannerModalClass = function (classname) {
		// Remove any classes starting with "alert-"
		$(".bannerMessageModal").removeClass(function (index, css) {
			return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
		});
		// Add class
		$(".bannerMessageModal").addClass('alert-' + classname);
	};

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};
	// Show processing dialog
	// $scope.showProcessingModal();

	// Call our API service to get the current educational material details
	educationalMaterialCollectionService.getEducationalMaterialDetails($scope.currentEduMat.serial).then(function (response) {
		$scope.eduMat = response.data;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('EDUCATION.EDIT.ERROR_DETAILS'));
		$uibModalInstance.close();
	});

	$scope.detailsUpdated = function () {
		$scope.eduMat.details_updated = 1;
		$scope.setChangesMade();
	};

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		if ($scope.eduMat.name_EN && $scope.eduMat.name_FR && (($scope.eduMat.url_EN && $scope.eduMat.url_FR)
			|| $scope.tocsComplete) && $scope.changesMade) {
			return true;
		}
		else
			return false;
	};

	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	$scope.typeTocUpdate = function(toc, language) {
		if (language === 'EN') {
			for (let i=0; i < $scope.EduMatTypes.length; i++) {
				if (toc.type_EN.toLowerCase() === $scope.EduMatTypes[i].EN.toLowerCase()) {
					toc.type_FR = $scope.EduMatTypes[i].FR;
					break;
				}
			}
		}
		else if (language === 'FR') {
			for (let i=0; i < $scope.EduMatTypes.length; i++) {
				if (toc.type_FR.toLowerCase() === $scope.EduMatTypes[i].FR.toLowerCase()) {
					toc.type_EN = $scope.EduMatTypes[i].EN;
					break;
				}
			}
		}

		$scope.validateTOCs();
	};

	$scope.validateTOCs = function () {

		$scope.setChangesMade();
		$scope.tocsComplete = true;
		$scope.eduMat.tocs_updated = 1;
		if (!$scope.eduMat.tocs.length) {
			$scope.tocsComplete = false;
			$scope.eduMat.tocs_updated = 0;
		}
		else {
			angular.forEach($scope.eduMat.tocs, function (toc) {
				if (!toc.name_EN || !toc.name_FR || !toc.url_EN
					|| !toc.url_FR || !toc.type_EN || !toc.type_FR) {
					$scope.tocsComplete = false;
					$scope.eduMat.tocs_updated = 0;
				}
			});
		}
	};

	// Function to validate english share url
	$scope.validShareURLEN = { status: null, message: null };
	$scope.validateShareURLEN = function (url) {
		if (!url) {
			$scope.validShareURLEN.status = null;
			$scope.setChangesMade();
			return;
		}
		// regex to check pdf extension
		var re = /(?:\.([^.]+))?$/;
		if (re.exec(url)[1] != 'pdf') {
			$scope.validShareURLEN.status = 'invalid';
			$scope.validShareURLEN.message = 'URL must be a pdf';
			$scope.setChangesMade();
			return;
		} else {
			$scope.validShareURLEN.status = 'valid';
			$scope.validShareURLEN.message = null;
			$scope.setChangesMade();
		}
	}

	// Function to validate french share url
	$scope.validShareURLFR = { status: null, message: null };
	$scope.validateShareURLFR = function (url) {
		if (!url) {
			$scope.validShareURLFR.status = null;
			$scope.setChangesMade();
			return;
		}
		// regex to check pdf extension
		var re = /(?:\.([^.]+))?$/;
		if (re.exec(url)[1] != 'pdf') {
			$scope.validShareURLFR.status = 'invalid';
			$scope.validShareURLFR.message = 'URL must be a pdf';
			$scope.setChangesMade();
			return;
		} else {
			$scope.validShareURLFR.status = 'valid';
			$scope.validShareURLFR.message = null;
			$scope.setChangesMade();
		}
	};

	// Submit changes
	$scope.updateEduMat = function () {

		if ($scope.checkForm()) {
			$scope.eduMat.user = Session.retrieveObject('user');

			// Submit form
			$.ajax({
				type: "POST",
				url: "educational-material/update/educational-material",
				dataType: "json",
				data: $scope.eduMat,
				success: function (response) {
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('EDUCATION.EDIT.SUCCESS_EDIT');
						$scope.showBanner();
					}
					else {
						ErrorHandler.onError(response, $filter('translate')('EDUCATION.DELETE.ERROR'));
					}
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('EDUCATION.EDIT.ERROR_EDIT'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
	};

	// Function to add table of contents to eduMat object
	$scope.addTOC = function () {
		var newOrder = $scope.eduMat.tocs.length + 1;
		$scope.eduMat.tocs.push({
			name_EN: "",
			name_FR: "",
			url_EN: "",
			url_FR: "",
			order: newOrder,
			serial: null
		});
		$scope.validateTOCs();
	};

	// Function to remove table of contents from eduMat object
	$scope.removeTOC = function (order) {
		$scope.eduMat.tocs.splice(order - 1, 1);
		// Decrement orders for content after the one just removed
		for (var index = order - 1; index < $scope.eduMat.tocs.length; index++) {
			$scope.eduMat.tocs[index].order -= 1;
		}
		$scope.validateTOCs();
	};

	// Function to accept/trust html (styles, classes, etc.)
	$scope.deliberatelyTrustAsHtml = function (htmlSnippet) {
		return $sce.trustAsHtml(htmlSnippet);
	};

	$scope.showWeeks = true; // show weeks sidebar
	$scope.toggleWeeks = function () {
		$scope.showWeeks = !$scope.showWeeks;
	};

	// set minimum date (today's date)
	$scope.toggleMin = function () {
		$scope.minDate = ($scope.minDate) ? null : new Date();
	};
	$scope.toggleMin();

	$scope.popup = {
		opened: false
	};

	// Open popup calendar
	$scope.open = function () {
		$scope.popup.opened = true;
	};

	$scope.dateOptions = {
		'year-format': "'yy'",
		'starting-day': 1
	};

	// Date format
	$scope.format = 'yyyy-MM-dd';

	// object for cron repeat units
	$scope.repeatUnits = [
		'Minutes',
		'Hours'
	];

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});