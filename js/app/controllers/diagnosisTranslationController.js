angular.module('opalAdmin.controllers.diagnosisTranslationController', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Diagnosis Translation Page controller 
	*******************************************************************************/
	controller('diagnosisTranslationController', function ($scope, $filter, $uibModal, diagnosisCollectionService, educationalMaterialCollectionService, uiGridConstants, $state, Session) {

		// Function to go to add diagnosis page
		$scope.goToAddDiagnosisTranslation = function () {
			$state.go('diagnosis-translation-add');
		};

		$scope.bannerMessage = "";
		// Function to show page banner 
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};
		// Function to set banner class
		$scope.setBannerClass = function (className) {
			// Remove any classes starting with "alert-" 
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + className);
		};

		// Initialize a scope variable for a selected diagnosis translation
		$scope.currentDiagnosisTranslation = {};

		$scope.changesMade = false;

		// Templates for alias table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editDiagnosisTranslation(row.entity)">' +
			'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editDiagnosisTranslation(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteDiagnosisTranslation(row.entity)">Delete</a></strong></div>';
	
		// Diagnosis Translation table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN'].forEach(function (field) {
					if (row.entity[field].match(matcher)) {
						match = true;
					}
				});
				if (!match) {
					row.visible = false;
				}
			});

			return renderableRows;
		};

		$scope.filterDiagnosisTranslation = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options for diagnosis translations
		$scope.gridOptions = {
			data: 'diagnosisTranslationList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Diagnosis Translation (EN / FR)', cellTemplate: cellTemplateName, width: '50%' },
				{ field: 'count', type: 'number', displayName: '# of codes', width: '15%', enableFiltering: false },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '35%' }
			],
			//useExternalFiltering: true,
			enableColumnResizing: true,
			enableFiltering: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},

		};

		// Initialize list of existing diagnosis translations
		$scope.diagnosisTranslationList = [];

		// Initialize an object for deleting a diagnosis translation
		$scope.diagnosisTranslationToDelete = {};

		// Call our API to get the list of existing diagnosis translations
		diagnosisCollectionService.getDiagnosisTranslations().then(function (response) {
			// Assign value
			$scope.diagnosisTranslationList = response.data; 

		}).catch(function(response) {
			console.error('Error occurred getting diagnosis translation list:', response.status, response.data);
		});

		// Function for when the diagnosis translation has been clicked for editing
		// We open a modal
		$scope.editDiagnosisTranslation = function (diagnosisTranslation) {

			$scope.currentDiagnosisTranslation = diagnosisTranslation;
			var modalInstance = $uibModal.open({
				templateUrl: 'editDiagnosisTranslationModalContent.htm',
				controller: EditDiagnosisTranslationModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the diagnosis translation list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing diagnosis translations
				diagnosisCollectionService.getDiagnosisTranslations().then(function (response) {

					// Assign the retrieved response
					$scope.diagnosisTranslationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting diagnosis translation list:', response.status, response.data);
				});
			});

		};

		// Controller for the edit alias modal
		var EditDiagnosisTranslationModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Default booleans
			$scope.changesMade = false;
			$scope.selectAll = false; 
			$scope.showAssigned = false;
			$scope.hideAssigned = false;

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
			educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
				$scope.eduMatList = response.data; // Assign value
			}).catch(function(response) {
				console.error('Error occurred getting educational material list:', response.status, response.data);
			});

			// Function to assign search field when textbox changes
			$scope.changeDiagnosisFilter = function (field) {
				$scope.diagnosisFilter = field;
				$scope.selectAll = false; // uncheck select all
			};

			// Function for search through the diagnoses
			$scope.searchDiagnosesFilter = function (Filter) {
				var keyword = new RegExp($scope.diagnosisFilter, 'i');
				return (!$scope.diagnosisFilter || keyword.test(Filter.name)) && (!$scope.showAssigned || Filter.assigned) && (!$scope.hideAssigned || !Filter.assigned);
			};

			// Function to enable "Show all" in diagnoses accordion
			$scope.changeShowAssigned = function () {
				$scope.showAssigned = true;
				$scope.hideAssigned = false;
			};

			// Function to enable "Show only assigned" tab in diagnoses accordion
			$scope.changeShowUnassigned = function () {
				$scope.hideAssigned = true;
				$scope.showAssigned = false;
			};

			// Function to enable "Show only unassigned" tab in diagnoses accordion
			$scope.changeShowAll = function () {
				$scope.showAssigned = false;
				$scope.hideAssigned = false;
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

			/* Function for the "Processing" dialog */
			var processingModal;
			$scope.showProcessingModal = function () {

				processingModal = $uibModal.open({
					templateUrl: 'processingModal.htm',
					backdrop: 'static',
					keyboard: false,
				});
			};
			// Show processing dialog
			$scope.showProcessingModal();

			// Call our API service to get the current diagnosis translation details
			diagnosisCollectionService.getDiagnosisTranslationDetails($scope.currentDiagnosisTranslation.serial).then(function (response) {

				$scope.diagnosisTranslation = response.data;

				// Call our API service to get the list of diagnosis codes
				diagnosisCollectionService.getDiagnoses().then(function (response) {

					$scope.diagnosisList = checkAdded(response.data);

					processingModal.close(); // hide modal
					processingModal = null; // remove reference

				}).catch(function(response) {
					console.error('Error occurred getting diagnoses:', response.status, response.data);
				});

			}).catch(function(response) {
				console.error('Error occurred getting diagnosis translation details:', response.status, response.data);
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
					var selectedDiagnosisSourceUID = selectedDiagnosis.sourceuid;
					angular.forEach(diagnosisList, function (diagnosis) {
						var sourceuid = diagnosis.sourceuid;
						if (sourceuid == selectedDiagnosisSourceUID) {
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

			$scope.eduMatUpdate = function (eduMat) {

				$scope.diagnosisTranslation.eduMat = eduMat;

				// Toggle boolean
				$scope.changesMade = true;
			};

			// Function to add / remove a diagnosis
			$scope.toggleDiagnosisSelection = function (diagnosis) {

				$scope.changesMade = true;

				// If originally added, remove it
				if (diagnosis.added) {

					diagnosis.added = 0; // added parameter

				}
				else { // Originally not added, add it

					diagnosis.added = 1;


				}
			};

			// Function to return boolean for # of added diagnoses
			$scope.checkDiagnosesAdded = function (diagnosisList) {

				var addedParam = false;
				angular.forEach(diagnosisList, function (diagnosis) {
					// ignore already assigned diagnoses
					if (!diagnosis.assigned) {
						if (diagnosis.added)
							addedParam = true;
					}
				});
				if (addedParam)
					return true;
				else
					return false;
			};

			$scope.setChangesMade = function () {
				$scope.changesMade = true;
			};

			// Function for selecting all codes in the diagnosis list
			$scope.selectAllFilteredDiagnoses = function () {

				var filtered = $scope.filter($scope.diagnosisList, $scope.searchDiagnosesFilter);
				
				if ($scope.selectAll) { // was checked
					angular.forEach(filtered, function (diagnosis) {
						// ignore assigned diagnoses
						if (!diagnosis.assigned)
							diagnosis.added = 0;
					});
					$scope.selectAll = false; // toggle off

				}
				else { // was not checked
					
					angular.forEach(filtered, function (diagnosis) {
						// ignore already assigned diagnoses
						if (!diagnosis.assigned)
							diagnosis.added = 1;
					});

					$scope.selectAll = true; // toggle on

				}
				$scope.setChangesMade();
			};

			// Submit changes
			$scope.updateDiagnosisTranslation = function() {

				if ($scope.checkForm()) {

					$scope.diagnosisTranslation.diagnoses = [];
					// Fill in the diagnoses from diagnosisList
					angular.forEach($scope.diagnosisList, function (diagnosis) {
						// ignore already assigned diagnoses
						if (!diagnosis.assigned) {
							if(diagnosis.added) {
								$scope.diagnosisTranslation.diagnoses.push(diagnosis);
							}
						}
					});
					// Log who updated diagnosis translation
					var currentUser = Session.retrieveObject('user');
					$scope.diagnosisTranslation.user = currentUser;
					// Submit form
					$.ajax({
						type: "POST",
						url: "php/diagnosis-translation/update.diagnosis_translation.php",
						data: $scope.diagnosisTranslation,
						success: function (response) {
							response = JSON.parse(response);
							// Show success or failure depending on response
							if (response.value) {
								$scope.setBannerClass('success');
								$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.diagnosisTranslation.name_EN + "/ " + $scope.diagnosisTranslation.name_FR + "\"!";
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

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};

		};

		// Function for when the diagnosis translation has been clicked for deletion
		// Open a modal
		$scope.deleteDiagnosisTranslation = function (currentDiagnosisTranslation) {

			// Assign selected diagnosis translation as the item to delete 
			$scope.diagnosisTranslationToDelete = currentDiagnosisTranslation;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteDiagnosisTranslationModalContent.htm',
				controller: DeleteDiagnosisTranslationModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});
			// After delete, refresh the diagnosis translation list
			modalInstance.result.then(function () {
				$scope.diagnosisTranslationList = [];
				// Call our API to get the list of existing diagnosis translations
				diagnosisCollectionService.getDiagnosisTranslations().then(function (response) {
					$scope.diagnosisTranslationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred diagnosis translations:', response.status, response.data);
				});

			});
		};

		// Controller for the delete diagnosis translation modal
		var DeleteDiagnosisTranslationModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Submit delete
			$scope.deleteDiagnosisTranslation = function () {
				// Log who deleted diagnosis translation
				var currentUser = Session.retrieveObject('user');
				$scope.diagnosisTranslationToDelete.user = currentUser;
				$.ajax({
					type: "POST",
					url: "php/diagnosis-translation/delete.diagnosis_translation.php",
					data: $scope.diagnosisTranslationToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.diagnosisTranslationToDelete.name_EN + "/ " + $scope.diagnosisTranslationToDelete.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}
						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			};

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};

		};

	});

