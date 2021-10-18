angular.module('opalAdmin.controllers.patient', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	 * Patient Page controller
	 *******************************************************************************/
	controller('patient', function ($rootScope, $scope, $filter, $sce, $state, $uibModal, patientCollectionService, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.patient];
		angular.forEach($scope.navSubMenu, function(menu) {
			menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
			menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
		});

		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 2)) !== 0);

		var arrValidationInsert = [
			$filter('translate')('PATIENTS.LIST.VALIDATION_FLAGS'),
		];

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
		var checkboxCellTemplate;

		if($scope.writeAccess)
			checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
				'ng-click="grid.appScope.checkTransferFlag(row.entity)" ' +
				'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
				'ng-checked="grid.appScope.updateTransferFlag(row.entity.transfer)" ng-model="row.entity.transfer"></div>';
		else
			checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents"><i ng-class="row.entity.transfer == 1 ? \'fa-check text-success\' : \'fa-times text-danger\'" class="fa"></i></div>';

		// patient table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name', 'RAMQ', 'email', 'MRN'].forEach(function (field) {
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
				{ field: 'RAMQ', displayName: $filter('translate')('PATIENTS.LIST.RAMQ'), width: '15%', enableColumnMenu: false },
				{ field: 'name', displayName: $filter('translate')('PATIENTS.LIST.NAME'), width: '20%', enableColumnMenu: false },
				{ field: 'email', displayName: $filter('translate')('PATIENTS.LIST.EMAIL'), width: '20%', enableColumnMenu: false },
				{ field: 'MRN', displayName: $filter('translate')('PATIENTS.LIST.MRN'), width: '20%', enableColumnMenu: false },
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
		$scope.patientTransfers = [];

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
					$scope.patientTransfers.push({
						serial: patient.serial,
						transfer: patient.transfer
					});
				});

				// Submit form
				$.ajax({
					type: "POST",
					url: "patient/update/publish-flags",
					data: {data: $scope.patientTransfers},
					success: function () {
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('PATIENTS.LIST.SUCCESS_FLAGS');
						$scope.showBanner();
						$scope.changesMade = false;
						getPatientsList();
					},
					error: function(err) {
						err.responseText = JSON.parse(err.responseText);
						ErrorHandler.onError(err, $filter('translate')('PATIENTS.LIST.ERROR_FLAGS'), arrValidationInsert);
					}
				});
			}
		};

		function getPatientsList() {
			patientCollectionService.getPatients().then(function (response) {
				$scope.patientList = response.data;
				var temp;

				for (var i = 0; i < $scope.patientList.length; i++) {
					temp = "";
					if($scope.patientList[i].MRN.length > 0)
						for (var j = 0; j < $scope.patientList[i].MRN.length; j++)
							temp += $scope.patientList[i].MRN[j].MRN + " (" + $scope.patientList[i].MRN[j].hospital + "), ";

					$scope.patientList[i].MRN = temp.slice(0, -2);
				}

			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENTS.LIST.ERROR_PATIENTS'));
			});
		}
	});
