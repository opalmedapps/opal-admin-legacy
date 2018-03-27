angular.module('opalAdmin.controllers.patientActivity', ['ngAnimate', 'ui.bootstrap']).


	controller('patientActivity', function ($scope, $uibModal, patientCollectionService) {

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

		// Table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['patientid', 'deviceid', 'name'].forEach(function (field) {
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

		// Table options
		$scope.gridOptions = {
			data: 'patientActivityList',
			columnDefs: [
				{ field: 'patientid', displayName: 'Patient Id', width: '15%' },
				{ field: 'name', displayName: 'Name', width: '15%' },
				{ field: 'deviceid', displayName: 'Device ID', width: '30%' },
				{ field: 'login', displayName: 'Login Time', width: '20%' },
				{ field: 'logout', displayName: 'Logout Time', width: '20%' }
			],
			//useExternalFiltering: true,
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list to hold patient activities
		$scope.patientActivityList = [];

		$scope.loading = true;
		// Call our API to get the list of patient activities
		patientCollectionService.getPatientActivities().then(function (response) {
			// Assign value
			$scope.patientActivityList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting patient activities:', response.status, response.data);
		}).finally(function () { $scope.loading = false; });

	});

