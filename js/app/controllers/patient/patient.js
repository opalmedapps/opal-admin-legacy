angular.module('opalAdmin.controllers.patient', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


/******************************************************************************
 * Patient Page controller
 *******************************************************************************/
controller('patient', function ($scope, $filter, $sce, $state, $uibModal, patientCollectionService) {

	// Function to go to register new patient
	$scope.goToAddPatient = function () {
		$state.go('patients-register');
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
	$scope.setBannerClass = function (classname) {
		// Remove any classes starting with "alert-"
		$(".bannerMessage").removeClass(function (index, css) {
			return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
		});
		// Add class
		$(".bannerMessage").addClass('alert-' + classname);
	};

	$scope.changesMade = false;

	// Templates for the patient table
	var checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
		'ng-click="grid.appScope.checkTransferFlag(row.entity)" ' +
		'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
		'ng-checked="grid.appScope.updateTransferFlag(row.entity.transfer)" ng-model="row.entity.transfer"></div>';

	// var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;" ng-if="isAuthorized([userRoles.admin])" >' +
	// 	'<strong><a href="" ng-click="grid.appScope.editPatient(row.entity)">Edit</a></strong>' +
	// 	'- <strong><a href="" ng-click="grid.appScope.toggleBlock(row.entity)"><span ng-if="row.entity.disabled">Unblock</span>' +
	// 	'<span ng-if="!row.entity.disabled">Block</span></a></strong></div> ';

	// patient table search textbox param
	$scope.filterOptions = function (renderableRows) {
		var matcher = new RegExp($scope.filterValue, 'i');
		renderableRows.forEach(function (row) {
			var match = false;
			['name', 'patientid', 'email'].forEach(function (field) {
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


	$scope.filterPatient = function (filterValue) {
		$scope.filterValue = filterValue;
		$scope.gridApi.grid.refresh();

	};

	// Table options for patient
	$scope.gridOptions = {
		data: 'patientList',
		columnDefs: [
			{ field: 'patientid', displayName: $filter('translate')('PATIENTS.LIST.PATIENTID'), width: '15%', enableColumnMenu: false },
			{ field: 'name', displayName: $filter('translate')('PATIENTS.LIST.NAME'), width: '30%', enableColumnMenu: false },
			{ field: 'email', displayName: $filter('translate')('PATIENTS.LIST.EMAIL'), width: '30%', enableColumnMenu: false },
			{ field: 'transfer', displayName: $filter('translate')('PATIENTS.LIST.PUBLISH_FLAG'), width: '10%', cellTemplate: checkboxCellTemplate, enableFiltering: false, enableColumnMenu: false },
			{ field: 'lasttransferred', displayName: $filter('translate')('PATIENTS.LIST.LAST'), width:'15%', enableColumnMenu: false },

		],
		enableFiltering: true,
		//useExternalFiltering: true,
		enableColumnResizing: true,
		onRegisterApi: function (gridApi) {
			$scope.gridApi = gridApi;
			$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
		},

	};

	// Initialize list of existing patients
	$scope.patientList = [];
	$scope.patientTransfers = {
		transferList: []
	};

	getPatientsList();

	// When this function is called, we set the "publish" field to checked
	// or unchecked based on value in the argument
	$scope.updateTransferFlag = function (value) {
		return (parseInt(value) === 1);
	};


	// Function for when the patient checkbox has been modified
	$scope.checkTransferFlag = function (patient) {

		$scope.changesMade = true;
		patient.transfer = parseInt(patient.transfer);
		// If the "transfer" column has been checked
		if (patient.transfer) {
			patient.transfer = 0; // set transfer to "false"
		}

		// Else the "Transfer" column was unchecked
		else {
			patient.transfer = 1; // set transfer to "true"
		}
	};

	// Function to submit changes when transfer flags have been modified
	$scope.submitTransferFlags = function () {
		if ($scope.changesMade) {
			angular.forEach($scope.patientList, function (patient) {
				$scope.patientTransfers.transferList.push({
					serial: patient.serial,
					transfer: patient.transfer
				});
			});
			// Submit form
			$.ajax({
				type: "POST",
				url: "patient/update/patient-publish-flags",
				data: $scope.patientTransfers,
				success: function () {
					getPatientsList();
					$scope.bannerMessage = "Transfer Flags Saved!";
					$scope.showBanner();
					$scope.changesMade = false;
				}
			});
		}
	};

	// Function for when a user has been clicked for (un)blocking
	// Open a modal
	$scope.patientToToggleBlock = null;
	$scope.toggleBlock = function (currentPatient) {

		$scope.patientToToggleBlock = currentPatient;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/patient/block.patient.html',
			windowClass: 'customModal',
			controller: 'patient.block',
			scope: $scope,
			backdrop: 'static'
		});

		// After toggle, refresh the patient list
		modalInstance.result.then(function () {
			getPatientsList();
		});
	};

	// Function for when the patient has been clicked for editing
	// Open a modal
	$scope.currentPatient = null;
	$scope.editPatient = function (patient) {

		$scope.currentPatient = patient;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/patient/edit.patient.html',
			controller: 'patient.edit',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
			keyboard: false,
		});

		// After update, refresh the patient list
		modalInstance.result.then(function () {
			getPatientsList();
		});
	};

	function getPatientsList() {
		patientCollectionService.getPatients().then(function (response) {
			console.log(response.data);
			$scope.patientList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting patient list:', response.status, response.data);
		});
	}
});
