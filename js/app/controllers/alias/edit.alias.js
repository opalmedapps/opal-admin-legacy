angular.module('opalAdmin.controllers.alias.edit', [])

	.controller('alias.edit', function ($scope, $uibModal, $uibModalInstance, $filter, aliasCollectionService, educationalMaterialCollectionService, Session) {

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
		$scope.existingColorTags = [];

		$scope.termFilter = null;
		$scope.eduMatFilter = null;

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Call our API service to get the list of educational material
		educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
			$scope.eduMatList = response.data; // Assign value
		}).catch(function(response) {
			console.error('Error occurred getting educational material list:', response.status, response.data);
		});

		// Function to assign termFilter when textbox is changing 
		$scope.changeTermFilter = function (termFilter) {
			$scope.termFilter = termFilter;
			$scope.selectAll = false;
		};

		// Function for searching through expression names
		$scope.searchTermsFilter = function (term) {
			var keyword = new RegExp($scope.termFilter, 'i');
			return (!$scope.termFilter || keyword.test(term.name)) && (!$scope.showAssigned || term.assigned) && (!$scope.hideAssigned || !term.assigned);
		};

		// Function to assign eduMatFilter when textbox is changing 
		$scope.changeEduMatFilter = function (eduMatFilter) {
			$scope.eduMatFilter = eduMatFilter;
		};

		// Function for searching through expression names
		$scope.searchEduMatsFilter = function (edumat) {
			var keyword = new RegExp($scope.eduMatFilter, 'i');
			return !$scope.eduMatFilter || keyword.test(edumat.name_EN);
		};

		// Function to enable "Show all" tab in term accordion
		$scope.changeShowAssigned = function () {
			$scope.showAssigned = true;
			$scope.hideAssigned = false;
		}

		// Function to enable "Show only assigned" tab in term accordion
		$scope.changeShowUnassigned = function () {
			$scope.hideAssigned = true;
			$scope.showAssigned = false;
		}

		// Function to enable "Show unassigned" tab in term accordion
		$scope.changeShowAll = function () {
			$scope.showAssigned = false;
			$scope.hideAssigned = false;
		}

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

			// Assign value
			$scope.alias = response.data;
			$scope.aliasModal = jQuery.extend(true, {}, $scope.alias); // deep copy


			// Call our API service to get the list of alias expressions
			aliasCollectionService.getExpressions($scope.alias.source_db.serial, $scope.alias.type).then(function (response) {
				$scope.termList = response.data; // Assign value

				// Loop within current alias' expressions (terms) 
				angular.forEach($scope.alias.terms, function (selectedTerm) {

					// Loop within each of the existing terms
					angular.forEach($scope.termList, function (term) {
						var termId = term.id; // get the id name
						var selectedTermName = selectedTerm.name;

						if (selectedTermName == termId) { // If term is selected (from current alias)
							term.added = 1; // term added?
							term.assigned = null; // remove self assigned alias
						}
					});

				});

				processingModal.close(); // hide modal
				processingModal = null; // remove reference


			}).catch(function(response) {
				console.error('Error occurred getting expression list:', response.status, response.data);
			});

			// Call our API service to get the list of existing color tags
			aliasCollectionService.getExistingColorTags($scope.alias.type).then(function (response) {
				$scope.existingColorTags = response.data; // Assign response

			}).catch(function(response) {
				console.error('Error occurred getting color tags:', response.status, response.data);
			});

		}).catch(function(response) {
			console.error('Error occurred getting alias details:', response.status, response.data);
		});

		// Function to add / remove a term to alias
		$scope.toggleTermSelection = function (term) {

			// Toggle booleans
			$scope.changesMade = true;
			$scope.termsMod = true;

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

			if ($scope.alias.description_EN && $scope.alias.description_FR) { // if textbox field is not empty

				// Toggle boolean
				$scope.emptyDescription = false;
			}
			else { // textbox is empty

				// Toggle boolean
				$scope.emptyDescription = true;
			}

		};

		$scope.eduMatUpdate = function () {

			// Toggle boolean
			$scope.changesMade = true;
		};

		$scope.colorUpdate = function (color) {

			// Toggle boolean
			$scope.changesMade = true;

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

				// Empty alias terms list
				$scope.alias.terms = [];

				// Fill it with the added terms from termList
				angular.forEach($scope.termList, function (term) {
					// ignore already assigned expressions
					if (!term.assigned) {
						if (term.added)
							$scope.alias.terms.push(term.id);
					}
				});

				// Log who updated alias
				var currentUser = Session.retrieveObject('user');
				$scope.alias.user = currentUser;

				// Submit form
				$.ajax({
					type: "POST",
					url: "php/alias/update.alias.php",
					data: $scope.alias,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.alias.name_EN + "/ " + $scope.alias.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}

						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			}
		};

		// Function to return boolean for # of added terms
		$scope.checkTermsAdded = function (termList) {

			var addedParam = false;
			angular.forEach(termList, function (term) {
				// ignore already assigned expressions
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

		// Function for selecting all terms in the term list
		$scope.selectAllFilteredTerms = function () {

			var filtered = $scope.filter($scope.termList, $scope.searchTermsFilter);
			
			if ($scope.selectAll) { // was checked
				angular.forEach(filtered, function (term) {
					// ignore assigned diagnoses
					if (!term.assigned)
						term.added = 0;
				});
				$scope.selectAll = false; // toggle off

			}
			else { // was not checked
				
				angular.forEach(filtered, function (term) {
					// ignore already assigned diagnoses
					if (!term.assigned)
						term.added = 1;
				});

				$scope.selectAll = true; // toggle on

			}
			$scope.changesMade = true;
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {

			if ($scope.alias.name_EN && $scope.alias.name_FR && $scope.alias.description_EN
				&& $scope.alias.description_FR && $scope.alias.type && $scope.checkTermsAdded($scope.termList)
				&& $scope.changesMade) {
				return true;
			}
			else
				return false;
		};


});