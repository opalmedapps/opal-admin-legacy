angular.module('opalAdmin.controllers.diagnosisTranslation.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('diagnosisTranslation.delete', function ($scope, $filter, $uibModalInstance, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteDiagnosisTranslation = function () {
		// Log who deleted diagnosis translation
		var currentUser = Session.retrieveObject('user');
		$scope.diagnosisTranslationToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "diagnosis-translation/delete/diagnosis-translation",
			data: $scope.diagnosisTranslationToDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('DIAGNOSIS.DELETE.SUCCESS');
				$scope.showBanner();
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('DIAGNOSIS.DELETE.ERROR'));
			},
			complete: function () {
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};


});