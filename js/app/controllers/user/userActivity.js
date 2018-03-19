angular.module('opalAdmin.controllers.userActivity', ['ngAnimate', 'ui.bootstrap']).


	controller('userActivity', function ($scope, $uibModal, userCollectionService) {

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
				['username'].forEach(function (field) {
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

		$scope.filterUser = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options
		$scope.gridOptions = {
			data: 'userActivityList',
			columnDefs: [
				{ field: 'username', displayName: 'User', width: '45%' },
				{ field: 'login', displayName: 'Login Time', width: '20%' },
				{ field: 'logout', displayName: 'Logout Time', width: '20%' },
				{ field: 'session_duration', displayName: 'Session Duration', width: '15%'}
			],
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list to hold user activities
		$scope.userActivityList = [];

		$scope.loading = true;
		// Call our API to get the list of user activities
		userCollectionService.getUserActivities().then(function (response) {
			// Assign value
			$scope.userActivityList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting user activities:', response.status, response.data);
		}).finally(function () { $scope.loading = false; });


	});