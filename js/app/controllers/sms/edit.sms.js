angular.module('opalAdmin.controllers.sms.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).controller('sms.edit', function ($scope, $filter, $uibModal, $uibModalInstance, smsCollectionService, $state, Session, ErrorHandler) {

	$scope.appointment = JSON.parse(JSON.stringify($scope.currentAppointment));
	if($scope.appointment.type == "-")
		$scope.appointment.type = "UNDEFINED";
	$scope.oldAppointment = JSON.parse(JSON.stringify($scope.appointment));

	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	if ($scope.appointment.type === '-')
		$scope.currentAppointmentType = "UNDEFINED";
	else $scope.currentAppointmentType = $scope.appointment.type;
	$scope.typeSelected = $scope.currentAppointmentType;
	getSmsTypeList();
	$scope.typeSearchField = "";

	//Function to get appointment type list.
	function getSmsTypeList() {
		smsCollectionService.getSmsType().then(function (response) {
			$scope.TypeList = response.data;
			$scope.TypeList.push('UNDEFINED');
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR_DETAILS'));
		});
	}

	//Function for searchbar
	$scope.searchType = function (field) {
		$scope.typeSearchField = field;
	};
	$scope.searchTypeFilter = function (Filter) {
		var keyword = new RegExp($scope.typeSearchField, 'i');
		return !$scope.typeSearchField || keyword.test(Filter);
	};

	var arrValidationInsert = [
		$filter('translate')('SMS.VALIDATION.APPOINTMENT_ID'),
		$filter('translate')('SMS.VALIDATION.ACTIVE'),
		$filter('translate')('SMS.VALIDATION.TYPE'),
		$filter('translate')('SMS.VALIDATION.UNDEFINED_ACTIVE'),
	];

	$scope.$watch('appointment', function() {
		$scope.changesDetected = ($scope.appointment.active !== $scope.oldAppointment.active || $scope.appointment.type !== $scope.oldAppointment.type);
		if($scope.appointment.type == "UNDEFINED") {
			$scope.appointment.active = 0;
		}
	}, true);

	// Submit changes
	$scope.updateAppointment = function () {

		$.ajax({
			type: "POST",
			url: "sms/update/appointment-code",
			data: {
				id: $scope.appointment.id,
				active: $scope.appointment.active,
				type: $scope.appointment.type,
			},
			success: function () {},
			error: function (err) {
				err.responseText = JSON.parse(err.responseText);
				ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR'), arrValidationInsert);
			},
			complete: function () {
				$uibModalInstance.close();
			}
		});
	};
});