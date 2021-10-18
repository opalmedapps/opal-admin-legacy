angular.module('opalAdmin.controllers.patientActivity', ['ngAnimate', 'ui.bootstrap']).


controller('patientActivity', function ($scope, $uibModal, $filter, patientCollectionService, Session, ErrorHandler, MODULE) {
	$scope.navMenu = Session.retrieveObject('menu');
	$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.patient];
	angular.forEach($scope.navSubMenu, function(menu) {
		menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
		menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
	});
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 2)) !== 0);

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
			{ field: 'patientid', displayName: $filter('translate')('PATIENTS.ACTIVITY.PATIENTID'), width: '15%', enableColumnMenu: false },
			{ field: 'name', displayName: $filter('translate')('PATIENTS.ACTIVITY.NAME'), width: '15%', enableColumnMenu: false },
			{ field: 'deviceid', displayName: $filter('translate')('PATIENTS.ACTIVITY.DEVICEID'), width: '30%', enableColumnMenu: false },
			{ field: 'login', displayName: $filter('translate')('PATIENTS.ACTIVITY.LOGIN'), width: '20%', enableColumnMenu: false },
			{ field: 'logout', displayName: $filter('translate')('PATIENTS.ACTIVITY.LOGOUT'), width: '20%', enableColumnMenu: false }
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
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('PATIENTS.ACTIVITY.ERROR_ACTIVITIES'));
	}).finally(function () { $scope.loading = false; });
});