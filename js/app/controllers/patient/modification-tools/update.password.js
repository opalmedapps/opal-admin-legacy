angular.module('opalAdmin.controllers.update.password', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.password', function ($scope, $filter, $uibModal, $uibModalInstance, patientCollectionService, $state, Session, ErrorHandler) {

	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};

	$scope.new_password = {
		firstTime: null,
		secondTime: null,
		errorMessage: null,
	};

	$scope.validatePassword = function(){
		if($scope.validateInput($scope.new_password.firstTime) && $scope.new_password.firstTime.length < 8)
        {
			$scope.new_password.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.PASSWORD_TOO_SHORT');
		}
		else if($scope.validateInput($scope.new_password.firstTime)&& (!$scope.new_password.firstTime.match(/\W|_{1}/) || !$scope.new_password.firstTime.match(/[A-Z]/) || !$scope.new_password.firstTime.match(/\d/)))
        {
			$scope.new_password.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.PASSWORD_MISSING_CHARACTER');
		}
		else if($scope.validateInput($scope.new_password.firstTime) && $scope.validateInput($scope.new_password.secondTime) && $scope.new_password.firstTime !== $scope.new_password.secondTime)
        {
			$scope.new_password.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.PASSWORD_NOT_SAME');
		}
		else
        {
			$scope.new_password.errorMessage = null;
		}
	};

	var arrValidationUpdate = [
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.USERID'),
		$filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.PASSWORD'),
	];

	$scope.updatePassword = function(){
		if($scope.new_password.firstTime !== null && $scope.new_password.secondTime !== null && $scope.new_password.errorMessage === null){
			$.ajax({
				type: "POST",
				url: "patient/update/external-password",
				data: {
					uid: $scope.puid,  //for test: "b0tEHXqDqwN9s7qKQdX1SqdTIQm1",
					password: $scope.new_password.firstTime,
				},
				success: function () {
					$scope.updatePasswordInDatabase();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.ERROR'), arrValidationUpdate);
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.ERROR');
				},
				complete: function () {
					$scope.showBanner();
					$uibModalInstance.close();
				}
			});
		}
	};

	$scope.updatePasswordInDatabase = function(){
		$.ajax({
			type: "POST",
			url: "patient/update/password",
			data: {
				uid: $scope.puid,
				password: CryptoJS.SHA512($scope.new_password.firstTime).toString(),
			},
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = "Successfully update patient password！";
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.ERROR'), arrValidationUpdate);
				$scope.setBannerClass('danger');
				$scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.PASSWORD.ERROR');
			},
		});
	};

	$scope.validateInput = function(input){
		return (input !== undefined && input !== null && input !== "");
	};

});