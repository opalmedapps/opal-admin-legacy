angular.module('opalAdmin.controllers.hospitalMap', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Hospital Map Page controller 
	*******************************************************************************/
	controller('hospitalMap', function ($scope, $filter, $sce, $state, $uibModal, hospitalMapCollectionService, Session) {

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
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
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
				{ field: 'qrid', displayName: 'QR Identifier', width: '10%' },
				{ field: 'url_EN', displayName: 'Map URL EN', width: '15%' },
				{ field: 'url_FR', displayName: 'Map URL FR', width: '15%' },
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
				templateUrl: 'templates/hospital-map/edit.hospital-map.html',
				controller: 'hospitalMap.edit',
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

		// Function for when the map has been clicked for deletion
		// Open a modal
		$scope.deleteHosMap = function (currentHosMap) {

			// Assign selected map as the item to delete
			$scope.hosMapToDelete = currentHosMap;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/hospital-map/delete.hospital-map.html',
				controller: 'hospitalMap.delete',
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

	});
