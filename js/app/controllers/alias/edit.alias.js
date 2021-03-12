angular.module('opalAdmin.controllers.alias.edit', [])

	.controller('alias.edit', function ($scope, $uibModal, $uibModalInstance, $filter, aliasCollectionService, Session, ErrorHandler) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 
		$scope.emptyTitle = false; // alias title field empty? 
		$scope.emptyDescription = false; // alias description field empty? 
		$scope.emptyTerms = false; // alias terms field empty? 
		$scope.nameMod = false; // name modified?
		$scope.termsMod = false; // terms modified? 
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

		$scope.alias = {}; // initialize alias object
		$scope.aliasModal = {}; // for deep copy
		$scope.termList = []; // initialize list for unassigned expressions in our DB
		$scope.eduMatList = [];
		$scope.hospitalMapList = [];

		$scope.termFilter = null;
		$scope.eduMatFilter = null;

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		$scope.alertMessage = "";
		$scope.hiddenAlert = true;
		// Function to show alert 
		$scope.showAlert = function (message) {
			$scope.hiddenAlert = false;
			$scope.alertMessage = message;
			$(".alertMessage").slideDown();
		};

		$scope.hideAlert = function () {
			$scope.hiddenAlert = true;
		};

		// Function to check if triggers are added
		$scope.checkTriggers = function (triggerList) {
			var triggersAdded = false;
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					triggersAdded = true;
			});
			return triggersAdded;
		};

		// Call our API service to get the list of educational material
		aliasCollectionService.getEducationalMaterials().then(function (response) {
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
			ErrorHandler.onError(err, $filter('translate')('ALIAS.EDIT.ERROR_EDUCATION'));
			$scope.cancel();
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
			ErrorHandler.onError(err, $filter('translate')('ALIAS.EDIT.ERROR_HOSPITAL'));
			$scope.cancel();
		});

		// Function to assign termFilter when textbox is changing 
		$scope.changeTermFilter = function (termFilter) {
			$scope.termFilter = termFilter;
			$scope.selectAll = false;
		};

		// Function for searching through expression names
		$scope.searchTermsFilter = function (term) {
			var keyword = new RegExp($scope.termFilter, 'i');
			return ((!$scope.termFilter || keyword.test(term.name))
				&& (($scope.clinicalCodeFilter == 'all') || ($scope.clinicalCodeFilter == 'current' && term.added)
					|| ($scope.clinicalCodeFilter == 'other' && term.assigned && !term.added) || ($scope.clinicalCodeFilter == 'none' && !term.added && !term.assigned)));
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

		// Call our API service to get the current alias details
		aliasCollectionService.getAliasDetails($scope.currentAlias.serial).then(function (response) {
			if($scope.language.toUpperCase() === "FR") {
				response.data.eduMat.name_display = response.data.eduMat.name_FR;
				response.data.eduMat.url_display = response.data.eduMat.url_FR;
				response.data.hospitalMap.name_display = response.data.hospitalMap.name_FR;
			}
			else {
				response.data.eduMat.name_display = response.data.eduMat.name_EN;
				response.data.eduMat.url_display = response.data.eduMat.url_EN;
				response.data.hospitalMap.name_display = response.data.hospitalMap.name_EN;
			}
			if(typeof response.data.eduMat.tocs  !== 'undefined') {
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

			switch (response.data.type) {
			case "Appointment":
				response.data.type_display = $filter('translate')('ALIAS.EDIT.APPOINTMENT');
				break;
			case "Task":
				response.data.type_display = $filter('translate')('ALIAS.EDIT.TASK');
				break;
			case "Document":
				response.data.type_display = $filter('translate')('ALIAS.EDIT.DOCUMENT');
				break;
			default:
				response.data.type_display = $filter('translate')('ALIAS.EDIT.NOT_TRANSLATED');
			}

			// Assign value
			$scope.alias = response.data;
			$scope.aliasModal = jQuery.extend(true, {}, $scope.alias); // deep copy

			// Call our API service to get the list of alias expressions
			aliasCollectionService.getExpressions($scope.alias.source_db.serial, $scope.alias.type).then(function (response) {
				$scope.termList = response.data; // Assign value

				// Loop within current alias' expressions (terms) 
				angular.forEach($scope.alias.terms, function (selectedTerm) {

					var selectedTermName = selectedTerm.id;
					var selectedTermDesc = selectedTerm.description;
					// Loop within each of the existing terms
					angular.forEach($scope.termList, function (term) {
						var termName = term.id; // get the id name
						var termDesc = term.description;
						if (selectedTermName == termName && selectedTermDesc == termDesc) { // If term is selected (from current alias)
							term.added = 1; // term added?
							term.assigned = null; // remove self assigned alias
						}
					});

				});
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('ALIAS.EDIT.ERROR_ALIAS'));
				$scope.cancel();
			}).finally(function() {
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('ALIAS.EDIT.ERROR_DETAILS'));
			$scope.cancel;
		});

		// Function to add / remove a term to alias
		$scope.toggleTermSelection = function (term) {

			// Toggle booleans
			$scope.changesMade = true;
			$scope.termsMod = true;
			$scope.alias.expressions_updated = 1;

			// Toggle boolean 
			$scope.emptyTerms = false;

			// If originally added, remove it
			if (term.added) {

				term.added = 0;

				// Check if there are still terms added, if not, flag
				if (!$scope.checkTermsAdded($scope.termList)) {
					$scope.emptyTerms = true;
				}

			} else { // Originally not added, add it

				term.added = 1; // added parameter

				// Just in case it was originally true
				// For sure we have a term
				$scope.emptyTerms = false;

			}

		};

		// Function that triggers when the title is updated
		$scope.titleUpdate = function () {

			// Toggle booleans
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;

			if ($scope.alias.name_EN && $scope.alias.name_FR) { // if textbox field is not empty

				// Toggle boolean
				$scope.emptyTitle = false;
			}
			else { // textbox is empty

				// Toggle boolean
				$scope.emptyTitle = true;
			}

		};
		// Function that triggers when the description is updated
		$scope.descriptionUpdate = function () {

			// Toggle booleans
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;

			if ($scope.alias.description_EN && $scope.alias.description_FR) { // if textbox field is not empty

				// Toggle boolean
				$scope.emptyDescription = false;
			}
			else { // textbox is empty

				// Toggle boolean
				$scope.emptyDescription = true;
			}

		};

		// Function that triggers when the checkin instructions are updated
		$scope.checkinInstructionsUpdate = function () {

			$scope.changesMade = true;
			$scope.alias.checkin_details_updated = 1;
		};

		// Function that triggers when the checkin possible option is updated
		$scope.checkinPossibleUpdate = function (flag) {

			$scope.changesMade = true;
			$scope.alias.checkin_details_updated = 1;

			$scope.alias.checkin_details.checkin_possible = flag;
		};

		// Function to show/hide educational material table of contents when link is clicked
		$scope.showTOCs = false;
		$scope.toggleTOCDisplay = function () {
			$scope.showTOCs = !$scope.showTOCs;
		};

		$scope.eduMatUpdate = function (event, eduMat) {

			if ($scope.alias.eduMat) {
				if ($scope.alias.eduMat.serial == event.target.value) {
					$scope.alias.eduMat = null;
					$scope.alias.eduMatSer = null;
				}
				else {
					$scope.alias.eduMat = eduMat;
				}
			}
			else {
				$scope.alias.eduMat = eduMat;
			}

			// Toggle boolean
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;
		};

		$scope.hospitalMapUpdate = function (event, hospitalMap) {

			if ($scope.alias.hospitalMap) {
				if ($scope.alias.hospitalMap.serial == event.target.value) {
					$scope.alias.hospitalMap = null;
					$scope.alias.hospitalMapSer = null;
				}
				else {
					$scope.alias.hospitalMap = hospitalMap;
				}
			}
			else {
				$scope.alias.hospitalMap = hospitalMap;
			}

			// Toggle boolean
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;
		};

		$scope.colorUpdate = function (color) {

			// Toggle boolean
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;

			if (color)
				$scope.alias.color = color;
		};


		$scope.toggleAlertText = function () {
			if ($scope.emptyTitle || $scope.emptyDescription || $scope.emptyTerms) {
				return true; // boolean
			}
			else {
				return false;
			}
		};

		// Submit changes
		$scope.updateAlias = function () {

			if ($scope.checkForm()) {

				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.alias.description_EN = $scope.alias.description_EN.replace(/\u200B/g,'');
				$scope.alias.description_FR = $scope.alias.description_FR.replace(/\u200B/g,'');

				if ($scope.alias.checkin_details_updated) {
					$scope.alias.checkin_details.instruction_EN = $scope.alias.checkin_details.instruction_EN.replace(/\u200B/g,'');
					$scope.alias.checkin_details.instruction_FR = $scope.alias.checkin_details.instruction_FR.replace(/\u200B/g,'');
				}

				// Empty alias terms list
				$scope.alias.terms = [];

				// Fill it with the added terms from termList
				angular.forEach($scope.termList, function (term) {
					if (term.added)
						$scope.alias.terms.push(term);
				});

				// Log who updated alias
				$scope.alias.user = Session.retrieveObject('user');

				$.ajax({
					type: "POST",
					url: "alias/update/alias",
					data: $scope.alias,
					success: function (response) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('ALIAS.EDIT.SUCCESS_EDIT');
						$scope.showBanner();
						$uibModalInstance.close();
					},
					error: function(err) {
						ErrorHandler.onError(err, $filter('translate')('ALIAS.EDIT.ERROR_EDIT'));
						$uibModalInstance.close();
					}
				});
			}
		};

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

		// Function for selecting all terms in the term list
		$scope.selectAllFilteredTerms = function () {

			var filtered = $scope.filter($scope.termList, $scope.searchTermsFilter);

			if ($scope.selectAll) { // was checked
				angular.forEach(filtered, function (term) {
					term.added = 0;
				});
				$scope.selectAll = false; // toggle off

			}
			else { // was not checked

				angular.forEach(filtered, function (term) {
					term.added = 1;
				});

				$scope.selectAll = true; // toggle on

			}
			$scope.changesMade = true;
			$scope.alias.expressions_updated = 1;

		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {

			if (($scope.alias.name_EN && $scope.alias.name_FR && $scope.alias.description_EN
				&& $scope.alias.description_FR && $scope.alias.type && $scope.checkTermsAdded($scope.termList)
				&& $scope.changesMade) && ($scope.alias.type != 'Appointment' || ($scope.alias.type == 'Appointment' &&
				$scope.alias.checkin_details.instruction_EN && $scope.alias.checkin_details.instruction_FR ))) {
				return true;
			}
			else
				return false;
		};
	});