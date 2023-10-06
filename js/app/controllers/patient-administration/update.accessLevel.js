angular.module('opalAdmin.controllers.update.accessLevel', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.accessLevel', function ($scope, $filter, $uibModal, $uibModalInstance, patientAdministrationCollectionService, $state, Session, ErrorHandler) {

	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	//Initialize the params field
	getAllAccessLevel();
	$scope.accessLevel = {
		value : null,
		valid : false,
	};

	//Function to validate the access level chosen by user
	$scope.validateAccessLevel = function () {
		if($scope.accessLevel.value !== null && $scope.accessLevel.value !== undefined && $scope.accessLevel.value !== "") {
			$scope.accessLevel.valid = true;
		}
		else {
			$scope.accessLevel.valid = false;
		}
	};

	//Initialize the error messages
	var arrValidationUpdate = [
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.ACCESS_LEVEL'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.PATIENTSERNUM'),
    ];

	//Function to update the patient access level
	$scope.updateAccessLevel = function () {
		if ($scope.accessLevel.valid === true) {
			$.ajax({
				type: "POST",
				url: "patient-administration/update/access-level",
				data: {
					accessLevel: $scope.accessLevel.value,
					PatientSerNum: $scope.psnum,
					language: $scope.plang,
				},
				success: function (response) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.ACCESS_LEVEL.SUCCESS');
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.ACCESS_LEVEL.ERROR'), arrValidationUpdate);
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.ACCESS_LEVEL.ERROR');
				},
				complete: function () {
					$scope.showBanner();
					$uibModalInstance.close();
				}
			});
		}
	};

	//Function to get the possible access level list in database
	function getAllAccessLevel () {
		patientAdministrationCollectionService.getAllAccessLevel().then(function (response) {
			$scope.levelList = []
			response.data.forEach(function (row) {
				var level = {
					levelId: row.Id,
					levelText: (Session.retrieveObject('user').language === "FR" ? row.AccessLevelName_FR : row.AccessLevelName_EN),
				}
				$scope.levelList.push(level);
			});
		});
	}

});