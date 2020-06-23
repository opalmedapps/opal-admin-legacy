angular.module('opalAdmin.controllers.diagnosisTranslation.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('diagnosisTranslation.delete', function ($scope, $filter, $uibModal, $uibModalInstance, $state, uiGridConstants, Session) {

	// Submit delete
	$scope.deleteDiagnosisTranslation = function () {
		// Log who deleted diagnosis translation
		var currentUser = Session.retrieveObject('user');
		$scope.diagnosisTranslationToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "diagnosis-translation/delete/diagnosis-translation",
			data: $scope.diagnosisTranslationToDelete,
			success: function (response) {
				response = JSON.parse(response);
				// Show success or failure depending on response
				if (response.value) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('DIAGNOSIS.DELETE.SUCCESS');
					$scope.showBanner();
				}
				else {
					alert($filter('translate')('DIAGNOSIS.DELETE.ERROR') + "\r\n\r\n" + response.message);
				}
				$uibModalInstance.close();
			},
			error: function (err) {
				alert($filter('translate')('DIAGNOSIS.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};


});