angular.module('opalAdmin.controllers.update.accessLevel', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.accessLevel', function ($scope, $filter, $uibModal, $uibModalInstance, patientCollectionService, $state, Session, ErrorHandler) {

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
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.ACCESS_LEVEL'),
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.PATIENTSERNUM'),
    ];

	//Function to update the patient access level
	$scope.updateAccessLevel = function () {
		if ($scope.accessLevel.valid === true) {
			$.ajax({
				type: "POST",
				url: "patient/update/access-level",
				data: {
					accessLevel: $scope.accessLevel.value,
					PatientSerNum: $scope.psnum,
				},
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = "Successfully update patient access level!";
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.ACCESS_LEVEL.ERROR'), arrValidationUpdate);
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.ACCESS_LEVEL.ERROR');
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
		patientCollectionService.getAllAccessLevel().then(function (response) {
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