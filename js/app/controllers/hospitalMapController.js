angular.module('opalAdmin.controllers.hospitalMapController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Hospital Map Page controller 
	*******************************************************************************/
	controller('hospitalMapController', function ($scope, $filter, $sce, $state, $uibModal, hospitalMapCollectionService, Session) {

		// Function to go to add hospital map page
		$scope.goToAddHospitalMap = function () {
			$state.go('hospital-map-add');
		};
		// Function to control search engine model
		$scope.filterHosMap = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editHosMap(row.entity)">' +
			'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editHosMap(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteHosMap(row.entity)">Delete</a></strong></div>';

		// Search engine for table
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

		// Table options for hospital maps
		$scope.gridOptions = {
			data: 'hosMapList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Name (EN / FR)', cellTemplate: cellTemplateName, width: '35%' },
				{ field: 'qrid', displayName: 'QR Identifier', width: '15%' },
				{ field: 'url', displayName: 'Map URL', width: '25%' },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, width: '25%' }
			],
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing maps
		$scope.hosMapList = [];

		// Initialize an object for deleting map
		$scope.hosMapToDelete = {};

		$scope.oldqrid = "";
		$scope.updatedHosMap = false;

		// Call our API to get the list of existing maps
		hospitalMapCollectionService.getHospitalMaps().then(function (response) {
			$scope.hosMapList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting hospital map list:', response.status, response.data);
		});

		$scope.bannerMessage = "";
		// Function to show page banner 
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};

		// Initialize a scope variable for a selected map
		$scope.currentHosMap = {};

		// Function for when the map has been clicked for editing
		$scope.editHosMap = function (hosmap) {

			$scope.currentHosMap = hosmap;
			var modalInstance = $uibModal.open({
				templateUrl: 'editHosMapModalContent.htm',
				controller: EditHosMapModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the hospital map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing maps
				hospitalMapCollectionService.getHospitalMaps().then(function (response) {

					// Assign the retrieved response
					$scope.hosMapList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting hospital map list:', response.status, response.data);
				});
			});

			modalInstance.closed.then(function () {

				if (!$scope.updatedHosMap) {
					hospitalMapCollectionService.generateQRCode($scope.currentHosMap.qrid, $scope.oldqrid).then(function () {
						$scope.updatedHosMap = false;
					}).catch(function(response) {
						console.error('Error occurred generating QR code:', response.status, response.data);
					});
				}

			});

		};

		// Controller for the edit Map modal
		var EditHosMapModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Default Booleans
			$scope.changesMade = false; // changes have been made? 
			$scope.hosMap = {}; // initialize map object

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

			$scope.mapURL = "";

			// Call our API to get the current map details
			hospitalMapCollectionService.getHospitalMapDetails($scope.currentHosMap.serial).then(function (response) {
				$scope.hosMap = response.data;
				$scope.$parent.oldqrid = response.data.qrid;
				$scope.mapURL = response.data.url;

				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			}).catch(function(response) {
				console.error('Error occurred getting hospital map details:', response.status, response.data);
			});

			// Function to call api to generate qr code
			$scope.generateQRCode = function (qrid) {

				if (qrid && $scope.changesMade) {
					hospitalMapCollectionService.generateQRCode(qrid, $scope.$parent.oldqrid).then(function (response) {
						$scope.hosMap.qrcode = response.data.qrcode;
						$scope.hosMap.qrpath = response.data.qrpath;

						$scope.$parent.oldqrid = qrid;

					}).catch(function(response) {
						console.error('Error occurred generating QR code:', response.status, response.data);
					});
				}
				else if (!qrid) {
					$scope.hosMap.qrcode = "";
					$scope.hosMap.qrpath = "";
				}

			};
			// Function to show map
			$scope.showMap = function (url) {
				$scope.mapURL = url;
			};


			// Function to check necessary form fields are complete
			$scope.checkForm = function () {
				if ($scope.hosMap.name_EN && $scope.hosMap.name_FR && $scope.hosMap.description_EN
					&& $scope.hosMap.description_FR && $scope.hosMap.qrid && $scope.hosMap.qrcode && $scope.hosMap.url
					&& $scope.changesMade) {
					return true;
				}
				else
					return false;
			};

			$scope.setChangesMade = function () {
				$scope.changesMade = true;
			};

			// Submit changes
			$scope.updateHosMap = function () {
				if ($scope.checkForm()) {
					// Log who updated hospital map
					var currentUser = Session.retrieveObject('user');
					$scope.hosMap.user = currentUser;
					// Submit form
					$.ajax({
						type: "POST",
						url: "php/hospital-map/update.hospital_map.php",
						data: $scope.hosMap,
						success: function () {
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.hosMap.name_EN + "/ " + $scope.hosMap.name_FR + "\"!";
							$scope.showBanner();
							$scope.$parent.updatedHosMap = true;
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

		// Function for when the map has been clicked for deletion
		// Open a modal
		$scope.deleteHosMap = function (currentHosMap) {

			// Assign selected map as the item to delete
			$scope.hosMapToDelete = currentHosMap;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteHosMapModalContent.htm',
				controller: DeleteHosMapModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing hospital maps
				hospitalMapCollectionService.getHospitalMaps().then(function (response) {
					// Assign the retrieved response
					$scope.hosMapList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting hospital map list:', response.status, response.data);
				});
			});
		};

		// Controller for the delete hospital map modal
		var DeleteHosMapModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Submit delete
			$scope.deleteHospitalMap = function () {
				// Log who deleted hospital map
				var currentUser = Session.retrieveObject('user');
				$scope.hosMapToDelete.user = currentUser;
				$.ajax({
					type: "POST",
					url: "php/hospital-map/delete.hospital_map.php",
					data: $scope.hosMapToDelete,
					success: function () {
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.hosMapToDelete.name_EN + "/ " + $scope.hosMapToDelete.name_FR + "\"!";
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
