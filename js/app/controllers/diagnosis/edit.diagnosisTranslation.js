// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.diagnosisTranslation.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('diagnosisTranslation.edit', function ($scope, $filter, $uibModal, $uibModalInstance, diagnosisCollectionService, Session, ErrorHandler) {


	// Default booleans
	$scope.changesMade = false;
	$scope.selectAll = false;
	$scope.showAssigned = false;
	$scope.hideAssigned = false;
	$scope.language = Session.retrieveObject('user').language;

	// Default toolbar for wysiwyg
	$scope.toolbar = [
		['h1', 'h2', 'h3', 'p'],
		['bold', 'italics', 'underline', 'ul', 'ol'],
		['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
		['html', 'insertLink']
	];

	// Responsible for "searching" in search bars
	$scope.filter = $filter('filter');

	$scope.diagnosisTranslation = {}; // Initialize diagnosis translation object

	// Initialize lists
	$scope.diagnosisList = [];
	$scope.eduMatList = [];

	// Initialize search fields
	$scope.diagnosisFilter = "";
	$scope.eduMatFilter = null;

	// Call our API service to get the list of educational material
	diagnosisCollectionService.getEducationalMaterials().then(function (response) {
		response.data.forEach(function(entry) {
			if($scope.language.toUpperCase() === "FR") {
				entry.name_display = entry.name_FR;
				entry.url_display = entry.url_FR;
			}
			else {
				entry.name_display = entry.name_EN;
				entry.url_display = entry.url_EN;
			}
			entry.tocs.forEach(function (sub) {
				if($scope.language.toUpperCase() === "FR") {
					sub.name_display = sub.name_FR;
					sub.url_display = sub.url_FR;
				}
				else {
					entry.name_display = sub.name_EN;
					sub.url_display = sub.url_EN;
				}
			});
		});
		$scope.eduMatList = response.data; // Assign value
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('DIAGNOSIS.EDIT.ERROR_EDUCATION'));
	});

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

	$scope.diagnosisCodeFilter = 'all';

	$scope.setDiagnosisCodeFilter = function (filter) {
		$scope.diagnosisCodeFilter = filter;
	};

	// Function to assign eduMatFilter when textbox is changing
	$scope.changeEduMatFilter = function (eduMatFilter) {
		$scope.eduMatFilter = eduMatFilter;
	};

	// Function for searching through expression names
	$scope.searchEduMatsFilter = function (edumat) {
		var keyword = new RegExp($scope.eduMatFilter, 'i');
		return !$scope.eduMatFilter || keyword.test($scope.language.toUpperCase() === "FR"?edumat.name_FR:edumat.name_EN);
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
	$scope.showProcessingModal();

	// Call our API service to get the current diagnosis translation details
	diagnosisCollectionService.getDiagnosisTranslationDetails($scope.currentDiagnosisTranslation.serial).then(function (response) {
		if(response.data.eduMat !== null) {
			if($scope.language.toUpperCase() === "FR") {
				response.data.eduMat.name_display = response.data.eduMat.name_FR;
				response.data.eduMat.url_display = response.data.eduMat.url_FR;
			}
			else {
				response.data.eduMat.name_display = response.data.eduMat.name_EN;
				response.data.eduMat.url_display = response.data.eduMat.url_EN;
			}
			if(response.data.eduMat.tocs  !== null)
			{
				response.data.eduMat.tocs.forEach(function (sub) {
					if ($scope.language.toUpperCase() === "FR") {
						sub.name_display = sub.name_FR;
						sub.url_display = sub.url_FR;
					} else {
						sub.name_display = sub.name_EN;
						sub.url_display = sub.url_EN;
					}
				});
			}
		}
		$scope.diagnosisTranslation = response.data;

		// Call our API service to get the list of diagnosis codes
		diagnosisCollectionService.getDiagnoses().then(function (response) {
			if(response.data.length <= 0) {
				alert($filter('translate')('DIAGNOSIS.ADD.ERROR_NO_DIAGNOSIS_FOUND'));
				$uibModalInstance.close();
			}
			response.data.forEach(function(entry) {
				if (typeof entry.assigned !== 'undefined') {
					if ($scope.language.toUpperCase() === "FR")
						entry.assigned.name_display = entry.assigned.name_FR;
					else
						entry.assigned.name_display = entry.assigned.name_EN;
				}
			});
			$scope.diagnosisList = checkAdded(response.data);

		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('DIAGNOSIS.EDIT.ERROR_DIAGNOSIS'));
			$uibModalInstance.close();
		}).finally(function() {
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('DIAGNOSIS.EDIT.ERROR_DETAILS'));
	});

	// Function to toggle Item in a list on/off
	$scope.selectItem = function (item) {
		$scope.changesMade = true;
		if (item.added)
			item.added = 0;
		else
			item.added = 1;
	};

	// Function to assign '1' to existing diagnosis
	function checkAdded(diagnosisList) {
		angular.forEach($scope.diagnosisTranslation.diagnoses, function (selectedDiagnosis) {
			angular.forEach(diagnosisList, function (diagnosis) {
				if (diagnosis.code === selectedDiagnosis.code && diagnosis.description === selectedDiagnosis.description) {
					diagnosis.added = 1;
					diagnosis.assigned = null; // remove self assigned diagnoses
				}

			});
		});

		return diagnosisList;
	}

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		if ($scope.diagnosisTranslation.name_EN && $scope.diagnosisTranslation.name_FR && $scope.diagnosisTranslation.description_EN
			&& $scope.diagnosisTranslation.description_FR && $scope.checkDiagnosesAdded($scope.diagnosisList) && $scope.changesMade) {
			return true;
		}
		else return false;
	};

	$scope.eduMatUpdate = function (event, eduMat) {

		if ($scope.diagnosisTranslation.eduMat) {
			if ($scope.diagnosisTranslation.eduMat.serial == event.target.value) {
				$scope.diagnosisTranslation.eduMat = null;
				$scope.diagnosisTranslation.eduMatSer = null;
			}
			else {
				$scope.diagnosisTranslation.eduMat = eduMat;
			}
		}
		else {
			$scope.diagnosisTranslation.eduMat = eduMat;
		}

		// Toggle boolean
		$scope.changesMade = true;
		$scope.diagnosisTranslation.details_updated = 1;
	};

	$scope.showTOCs = false;
	$scope.toggleTOCDisplay = function () {
		$scope.showTOCs = !$scope.showTOCs;
	}


	// Function to add / remove a diagnosis
	$scope.toggleDiagnosisSelection = function (diagnosis) {
		$scope.changesMade = true;
		$scope.diagnosisTranslation.codes_updated = 1;
		if (diagnosis.added)
			diagnosis.added = 0;
		else
			diagnosis.added = 1;
	};

	// Function to return boolean for # of added diagnoses
	$scope.checkDiagnosesAdded = function (diagnosisList) {

		var addedParam = false;
		angular.forEach(diagnosisList, function (diagnosis) {
			if (diagnosis.added)
				addedParam = true;
		});
		return addedParam;
	};

	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	$scope.detailsUpdated = function () {
		$scope.diagnosisTranslation.details_updated = 1;
		$scope.setChangesMade();
	}

	// Function for selecting all codes in the diagnosis list
	$scope.selectAllFilteredDiagnoses = function () {

		var filtered = $scope.filter($scope.diagnosisList, $scope.searchDiagnosesFilter);

		if ($scope.selectAll) { // was checked
			angular.forEach(filtered, function (diagnosis) {
				diagnosis.added = 0;
			});
			$scope.selectAll = false; // toggle off

		}
		else { // was not checked

			angular.forEach(filtered, function (diagnosis) {
				diagnosis.added = 1;
			});

			$scope.selectAll = true; // toggle on

		}
		$scope.setChangesMade();
		$scope.diagnosisTranslation.codes_updated = 1;
	};

	// Submit changes
	$scope.updateDiagnosisTranslation = function() {

		if ($scope.checkForm()) {

			// For some reason the HTML text fields add a zero-width-space
			// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string

			var toSubmit = {
				serial: $scope.currentDiagnosisTranslation.serial,
				description_EN: $scope.diagnosisTranslation.description_EN.replace(/\u200B/g,''),
				description_FR: $scope.diagnosisTranslation.description_FR.replace(/\u200B/g,''),
				name_EN: $scope.diagnosisTranslation.name_EN,
				name_FR: $scope.diagnosisTranslation.name_FR,
				eduMat: ($scope.diagnosisTranslation.eduMat != null ? $scope.diagnosisTranslation.eduMat.serial : null),
				diagnoses: []
			};

			angular.forEach($scope.diagnosisList, function (diagnosis) {
				if (diagnosis.added)
					toSubmit.diagnoses.push(diagnosis.ID);
			});

			// Submit form
			$.ajax({
				type: "POST",
				url: "diagnosis-translation/update/diagnosis-translation",
				data: toSubmit,
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('DIAGNOSIS.EDIT.SUCCESS_UPDATE');
					$scope.showBanner();
				},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('DIAGNOSIS.EDIT.ERROR_UPDATE'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});