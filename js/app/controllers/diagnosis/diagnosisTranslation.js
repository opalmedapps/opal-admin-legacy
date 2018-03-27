angular.module('opalAdmin.controllers.diagnosisTranslation', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Diagnosis Translation Page controller 
	*******************************************************************************/
	controller('diagnosisTranslation', function ($scope, $filter, $uibModal, diagnosisCollectionService, educationalMaterialCollectionService, uiGridConstants, $state, Session) {

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
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';

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
				templateUrl: 'templates/diagnosis/edit.diagnosis-translation.html',
				controller: 'diagnosisTranslation.edit',
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

		// Function for when the diagnosis translation has been clicked for deletion
		// Open a modal
		$scope.deleteDiagnosisTranslation = function (currentDiagnosisTranslation) {

			// Assign selected diagnosis translation as the item to delete 
			$scope.diagnosisTranslationToDelete = currentDiagnosisTranslation;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/diagnosis/delete.diagnosis-translation.html',
				controller: 'diagnosisTranslation.delete',
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

	});

