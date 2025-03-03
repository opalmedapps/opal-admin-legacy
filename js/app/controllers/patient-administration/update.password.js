// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.update.password', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.password', function ($scope, $filter, $uibModal, $uibModalInstance, patientAdministrationCollectionService, $state, Session, ErrorHandler) {

	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	//Initialize the params field
	$scope.new_password = {
		firstTime: null,
		secondTime: null,
		errorMessage: null,
	};

	//Function to validate the password given by user
	$scope.validatePassword = function() {
		if($scope.validateInput($scope.new_password.firstTime) && $scope.new_password.firstTime.length < 8) {
			$scope.new_password.errorMessage = $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.PASSWORD_TOO_SHORT');
		}
		else if($scope.validateInput($scope.new_password.firstTime)&& (!$scope.new_password.firstTime.match(/\W|_{1}/) || !$scope.new_password.firstTime.match(/[a-z]/) || !$scope.new_password.firstTime.match(/[A-Z]/) || !$scope.new_password.firstTime.match(/\d/))) {
			$scope.new_password.errorMessage = $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.PASSWORD_MISSING_CHARACTER');
		}
		else if(!$scope.validateInput($scope.new_password.firstTime) || !$scope.validateInput($scope.new_password.secondTime) || $scope.new_password.firstTime !== $scope.new_password.secondTime) {
			$scope.new_password.errorMessage = $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.PASSWORD_NOT_SAME');
		}
		else {
			$scope.new_password.errorMessage = null;
		}
	};

	//Initialize the error messages
	var arrValidationUpdate = [
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.USERID'),
		$filter('translate')('PATIENT_ADMINISTRATION.VALIDATION.PASSWORD'),
	];

	//Function to update the patient password
	$scope.updatePassword = function() {
		if($scope.new_password.firstTime !== null && $scope.new_password.secondTime !== null && $scope.new_password.errorMessage === null){
			//Update patient password in the external database
			$.ajax({
				type: "POST",
				url: "patient-administration/update/external-password",
				data: {
					uid: $scope.puid,
					password: $scope.new_password.firstTime,
				},
				success: function () {
					//If success, update password in internal database
					$scope.updatePasswordInDatabase();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.ERROR'), arrValidationUpdate);
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.ERROR');
				},
				complete: function () {
					$scope.showBanner();
					$uibModalInstance.close();
				}
			});
		}
	};

	//Function to update the password in internal database
	$scope.updatePasswordInDatabase = function() {
		$.ajax({
			type: "POST",
			url: "patient-administration/update/password",
			data: {
				uid: $scope.puid,
				password: CryptoJS.SHA512($scope.new_password.firstTime).toString(),
			},
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.SUCCESS');
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.ERROR'), arrValidationUpdate);
				$scope.setBannerClass('danger');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENT_ADMINISTRATION.PASSWORD.ERROR');
			},
		});
	};

	//function to validate input is not empty
	$scope.validateInput = function(input) {
		return (input !== undefined && input !== null && input !== "");
	};

});