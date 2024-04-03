angular.module('opalAdmin.controllers.alias.edit', [])

	.controller('alias.edit', function ($scope, $uibModal, $uibModalInstance, $filter, aliasCollectionService, Session, ErrorHandler) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 
		$scope.emptyTitle = false; // alias title field empty? 
		$scope.emptyDescription = false; // alias description field empty?
		$scope.nameMod = false; // name modified?
		$scope.termsMod = false; // terms modified? 
		$scope.selectAll = false;
		$scope.showAssigned = false;
		$scope.hideAssigned = false;
		$scope.language = Session.retrieveObject('user').language;
		$scope.noteDeactivated = $filter('translate')('ALIAS.EDIT.NOTE_DEACTIVATED');

		// Default toolbar for wysiwyg
		$scope.toolbar = [
			['h1', 'h2', 'h3', 'p'],
			['bold', 'italics', 'underline', 'ul', 'ol'],
			['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
			['html', 'insertLink']
		];

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
			return ((!$scope.termFilter || keyword.test(term.description) || keyword.test(term.externalId) || keyword.test(term.name))
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
			$scope.noteDeactivated = $scope.noteDeactivated.replace("%%SYSTEM_NAME%%", response.data.source_db.name);
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
					angular.forEach($scope.termList, function (term) {
						if (term.masterSourceAliasId === selectedTerm.masterSourceAliasId) { // If term is selected (from current alias)
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

			if (term.added)
				term.added = 0;
			else
				term.added = 1; // added parameter


		};

		// Function that triggers when the title is updated
		$scope.titleUpdate = function () {

			// Toggle booleans
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;

			$scope.emptyTitle = !($scope.alias.name_EN && $scope.alias.name_FR);

		};
		// Function that triggers when the description is updated
		$scope.descriptionUpdate = function () {

			// Toggle booleans
			$scope.changesMade = true;
			$scope.alias.details_updated = 1;

			$scope.emptyDescription = !($scope.alias.description_EN && $scope.alias.description_FR);

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

			// make hospital map required: disallow unselecting a hospital map, only allow the user to change the selection
			$scope.alias.hospitalMap = hospitalMap;

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

		// Submit changes
		$scope.updateAlias = function () {
			if ($scope.checkForm()) {
				let toSubmit = {
					"id": $scope.currentAlias.serial,
					"checkin_details" : $scope.alias.checkin_details,
					"color" : $scope.alias.color,
					"description_EN" : $scope.alias.description_EN.replace(/\u200B/g,''),
					"description_FR" : $scope.alias.description_FR.replace(/\u200B/g,''),
					"eduMat" : (typeof $scope.alias.eduMatSer !== "undefined" ? $scope.alias.eduMatSer: null),
					"hospitalMap" : (typeof $scope.alias.hospitalMapSer !== "undefined" ? $scope.alias.hospitalMapSer: null),
					"name_EN" : $scope.alias.name_EN,
					"name_FR" : $scope.alias.name_FR,
					"source_db" : $scope.alias.source_db.serial,
					"type" : $scope.alias.type,
					"terms" : []
				};

				angular.forEach($scope.termList, function (term) {
					if (term.added)
						toSubmit.terms.push(term.masterSourceAliasId);
				});


				if ($scope.alias.type.name == "Appointment") {
					toSubmit.checkin_details.instruction_EN = $scope.alias.checkin_details.instruction_EN.replace(/\u200B/g,'');
					toSubmit.checkin_details.instruction_FR = $scope.alias.checkin_details.instruction_FR.replace(/\u200B/g,'');
				}

				// Log who created this alias
				const currentUser = Session.retrieveObject('user');
				toSubmit.user = currentUser;
				
				$.ajax({
					type: "POST",
					url: "alias/update/alias",
					data: toSubmit,
					dataType: 'json',
					success: function () {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('ALIAS.EDIT.SUCCESS_EDIT');
						$scope.showBanner();
					},
					error: function (err) {
						if (err.responseText && typeof err.responseText == 'string') {
							err.responseText = JSON.parse(err.responseText);
						}
						ErrorHandler.onError(err, $filter('translate')('ALIAS.EDIT.ERROR_EDIT'), arrValidationInsert);
					},
					complete: function () {
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
			return addedParam;
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
			var total = 0;
			angular.forEach($scope.termList, function (term) {
				if (term.added) total++;
			});

			return !!(($scope.alias.name_EN && $scope.alias.name_FR && $scope.alias.description_EN
				&& $scope.alias.description_FR && $scope.alias.type && (total + $scope.alias.deleted.length + $scope.alias.published.length > 0)
				&& $scope.changesMade) && ($scope.alias.type != 'Appointment' || ($scope.alias.type == 'Appointment' &&
				$scope.alias.checkin_details.instruction_EN && $scope.alias.checkin_details.instruction_FR && $scope.alias.hospitalMap)));
		};
	});