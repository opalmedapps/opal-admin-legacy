angular.module('opalAdmin.controllers.hospitalMap', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	 * Hospital Map Page controller
	 *******************************************************************************/
	controller('hospitalMap', function ($scope, $filter, $sce, $state, $uibModal, hospitalMapCollectionService, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.hospital_map]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.hospital_map]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.hospital_map]) & (1 << 2)) !== 0);

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
			'<strong><a href="">{{row.entity.name_'+Session.retrieveObject('user').language.toUpperCase()+'}}</a></strong></div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editHosMap(row.entity)"><i title="'+$filter('translate')('HOSPITAL_MAPS.LIST.EDIT')+'" class="fa fa-pencil"></i></a></strong> ';
		else
			cellTemplateOperations += '<div style="text-align:center; padding-top: 5px;">' + '<strong><a href="" ng-click="grid.appScope.editHosMap(row.entity)"><i title="'+$filter('translate')('HOSPITAL_MAPS.LIST.VIEW')+'" class="fa fa-eye"></i></a></strong> ';

		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteHosMap(row.entity)"><i title="'+$filter('translate')('HOSPITAL_MAPS.LIST.DELETE')+'" class="fa fa-trash"></i></a></strong>';

		cellTemplateOperations += '</div>';

		var cellTemplateURL = '<div class="ui-grid-cell-contents"><a target="_blank" href="{{row.entity.url_'+Session.retrieveObject('user').language.toUpperCase()+'}}">{{row.entity.url_'+Session.retrieveObject('user').language.toUpperCase()+'}}</a></div>';

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
				{ field: 'name_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('HOSPITAL_MAPS.LIST.NAME'), cellTemplate: cellTemplateName, width: '40%', enableColumnMenu: false },
				// { field: 'qrid', displayName: $filter('translate')('HOSPITAL_MAPS.LIST.QR_IDENTIFIER'), width: '20%', enableColumnMenu: false },
				{ field: 'url_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('HOSPITAL_MAPS.LIST.MAP_URL'), cellTemplate: cellTemplateURL, width: '50%', enableColumnMenu: false },
				{ name: $filter('translate')('HOSPITAL_MAPS.LIST.OPERATIONS'), cellTemplate: cellTemplateOperations, sortable: false, width: '10%', enableColumnMenu: false }
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
		getHospitalMapsList();

		function getHospitalMapsList() {
			hospitalMapCollectionService.getHospitalMaps().then(function (response) {
				$scope.hosMapList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('HOSPITAL_MAPS.LIST.ERROR_LIST'));
			});
		}

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
				templateUrl: ($scope.writeAccess ? 'templates/hospital-map/edit.hospital-map.html' : 'templates/hospital-map/view.hospital-map.html'),
				controller: 'hospitalMap.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the hospital map list
			modalInstance.result.then(function () {
				getHospitalMapsList();
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
				getHospitalMapsList();
			});
		};

	});
