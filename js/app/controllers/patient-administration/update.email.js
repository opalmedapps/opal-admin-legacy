angular.module('opalAdmin.controllers.update.email', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.email', function ($scope, $filter, $uibModal, $uibModalInstance, patientAdministrationCollectionService, $state, Session, ErrorHandler) {

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
			$scope.new_email.errorMessage = $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.EMAIL_NOT_VALID');
		}
		else if($scope.validateInput($scope.new_email.firstTime) && $scope.validateInput($scope.new_email.secondTime) && $scope.new_email.firstTime !== $scope.new_email.secondTime) {
			$scope.new_email.errorMessage = $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.EMAIL_NOT_SAME');
		}
		else
        {
			$scope.new_email.errorMessage = null;
		}
	};

	//Initialize the error messages
	var arrValidationUpdateDatabase = [
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.PATIENTSERNUM'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.EMAIL'),
	];

	var arrValidationUpdateExternalDatabase = [
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.USERID'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.EMAIL'),
	];

	//Function to update the patient email
	$scope.updateEmail = function() {
		if($scope.new_email.firstTime !== null && $scope.new_email.secondTime !== null && $scope.new_email.errorMessage === null){
			//Update patient password in the external database
			$.ajax({
				type: "POST",
				url: "patient-administration/update/external-email",
				data: {
					puid: $scope.puid,
					lan: $scope.plang,
					email: $scope.new_email.firstTime,
				},
				success: function () {
					//If success, update email in internal database
					$scope.updateEmailInDatabase();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.ERROR'), arrValidationUpdateExternalDatabase);
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.ERROR');
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
			url: "patient-administration/update/email",
			data: {
				email: $scope.new_email.firstTime,
				PatientSerNum: $scope.psnum,
			},
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.SUCCESS');
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.ERROR'), arrValidationUpdateDatabase);
				$scope.setBannerClass('danger');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.EMAIL.ERROR');
			},
		});
	};

	//function to validate input is not empty
	$scope.validateInput = function(input) {
		return (input !== undefined && input !== null && input !== "");
	};

});