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

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editPatient(row.entity)">Edit</a></strong>' +
			'- <strong><a href="" ng-click="grid.appScope.toggleBlock(row.entity)"><span ng-if="row.entity.disabled">Unblock</span>' + 
			'<span ng-if="!row.entity.disabled">Block</span></a></strong></div> '; 

		// patient table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name', 'patientid'].forEach(function (field) {
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
				{ field: 'patientid', displayName: 'Patient ID', width: '25%' },
				{ field: 'name', displayName: 'Name', width: '25%' },
				{ field: 'transfer', displayName: 'Publish Flag', width: '10%', cellTemplate: checkboxCellTemplate, enableFiltering: false },
				{ field: 'lasttransferred', displayName: 'Last Publish', width:'20%' },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '20%'}

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

		// Call our API to get the list of existing patients
		patientCollectionService.getPatients().then(function (response) {
			// Assign value
			$scope.patientList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting patient list:', response.status, response.data);
		});

		// When this function is called, we set the "publish" field to checked 
		// or unchecked based on value in the argument
		$scope.updateTransferFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
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
					url: "php/patient/update.patient_publish_flags.php",
					data: $scope.patientTransfers,
					success: function () {
						// Call our API to get the list of existing patients
						patientCollectionService.getPatients().then(function (response) {
							// Assign value
							$scope.patientList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting patient list:', response.status, response.data);
						});
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
				// Call our API to get the list of existing patients
				patientCollectionService.getPatients().then(function (response) {
					// Assign value
					$scope.patientList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting patient list:', response.status, response.data);
				});
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
				// Call our API to get the list of existing patients
				patientCollectionService.getPatients().then(function (response) {
					// Assign value
					$scope.patientList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting patient list:', response.status, response.data);
				});
			});
		};

	});


