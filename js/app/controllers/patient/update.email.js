angular.module('opalAdmin.controllers.update.email', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.email', function ($scope, $filter, $uibModal, $uibModalInstance, patientCollectionService, $state, Session, ErrorHandler) {

	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	//Initialize the params field
	$scope.new_email = {
		firstTime: null,
		secondTime: null,
		errorMessage: null,
	};

	//Function to validate the email given by user
	$scope.validateEmail = function() {
		if($scope.validateInput($scope.new_email.firstTime) && !$scope.new_email.firstTime.match(/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/)) {
			$scope.new_email.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.EMAIL_NOT_VALID');
		}
		else if($scope.validateInput($scope.new_email.firstTime) && $scope.validateInput($scope.new_email.secondTime) && $scope.new_email.firstTime !== $scope.new_email.secondTime) {
			$scope.new_email.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.EMAIL_NOT_SAME');
		}
		else
        {
			$scope.new_email.errorMessage = null;
		}
	};

	//Initialize the error messages
	var arrValidationUpdateDatabase = [
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.PATIENTSERNUM'),
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.EMAIL'),
	];

	var arrValidationUpdateExternalDatabase = [
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.USERID'),
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.EMAIL'),
	];

	//Function to update the patient email
	$scope.updateEmail = function() {
		if($scope.new_email.firstTime !== null && $scope.new_email.secondTime !== null && $scope.new_email.errorMessage === null){
			//Update patient password in the external database
			$.ajax({
				type: "POST",
				url: "patient/update/external-email",
				data: {
					uid: $scope.puid,
					email: $scope.new_email.firstTime,
				},
				success: function () {
					//If success, update email in internal database
					$scope.updateEmailInDatabase();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR'), arrValidationUpdateExternalDatabase);
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR');
				},
				complete: function () {
					$scope.showBanner();
					$uibModalInstance.close();
				}
			});
		}
	};

	//Function to update email in internal database
	$scope.updateEmailInDatabase = function() {
		$.ajax({
			type: "POST",
			url: "patient/update/email",
			data: {
				email: $scope.new_email.firstTime,
				PatientSerNum: $scope.psnum,
			},
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = "Successfully update patient email！";
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR'), arrValidationUpdateDatabase);
				$scope.setBannerClass('danger');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR');
			},
		});
	};

	//function to validate input is not empty
	$scope.validateInput = function(input) {
		return (input !== undefined && input !== null && input !== "");
	};

});