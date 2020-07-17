angular.module('opalAdmin.controllers.diagnosisTranslation', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	 * Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('diagnosisTranslation', function ($scope, $filter, $uibModal, $state, diagnosisCollectionService, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.diagnosis_translation]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.diagnosis_translation]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.diagnosis_translation]) & (1 << 2)) !== 0);

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
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ng-click="grid.appScope.editDiagnosisTranslation(row.entity)"><strong><a href="">{{row.entity.name_'+Session.retrieveObject('user').language.toUpperCase()+'}}</a></strong></div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editDiagnosisTranslation(row.entity)"><i title="' + $filter('translate')('DIAGNOSIS.LIST.EDIT') + '" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editDiagnosisTranslation(row.entity)"><i title="' + $filter('translate')('DIAGNOSIS.LIST.VIEW') + '" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';
		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteDiagnosisTranslation(row.entity)"><i title="' + $filter('translate')('DIAGNOSIS.LIST.DELETE') + '" class="fa fa-trash" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';

		// Diagnosis Translation table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_'+Session.retrieveObject('user').language.toUpperCase()].forEach(function (field) {
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
				{ field: 'name_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('DIAGNOSIS.LIST.NAME'), cellTemplate: cellTemplateName, width: '60%', enableColumnMenu: false },
				{ field: 'count', type: 'number', displayName: $filter('translate')('DIAGNOSIS.LIST.NUMBER'), width: '20%', enableFiltering: false, enableColumnMenu: false },
				{ name: $filter('translate')('DIAGNOSIS.LIST.OPERATIONS'), cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, enableColumnMenu: false }
			],
			//useExternalFiltering: true,
			enableColumnResizing: true,
			enableFiltering: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize variables and get data
		$scope.diagnosisTranslationList = [];
		$scope.diagnosisTranslationToDelete = {};
		getDiagnosisTranslationList();

		// Function for when the diagnosis translation has been clicked for editing
		// We open a modal
		$scope.editDiagnosisTranslation = function (diagnosisTranslation) {

			$scope.currentDiagnosisTranslation = diagnosisTranslation;
			var modalInstance = $uibModal.open({
				templateUrl: ($scope.writeAccess ? 'templates/diagnosis/edit.diagnosis-translation.html' : 'templates/diagnosis/view.diagnosis-translation.html'),
				controller: 'diagnosisTranslation.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the diagnosis translation list
			modalInstance.result.then(function () {
				getDiagnosisTranslationList();
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
				getDiagnosisTranslationList();
			});
		};

		function getDiagnosisTranslationList() {
			diagnosisCollectionService.getDiagnosisTranslations().then(function (response) {
				$scope.diagnosisTranslationList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('DIAGNOSIS.LIST.ERROR_LIST'));
			});
		}
	});

