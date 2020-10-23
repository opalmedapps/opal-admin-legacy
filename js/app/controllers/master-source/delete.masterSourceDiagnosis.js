angular.module('opalAdmin.controllers.masterSourceDiagnosis.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('masterSourceDiagnosis.delete', function ($scope, $filter, $uibModal, $uibModalInstance, uiGridConstants, $state, Session, ErrorHandler) {

	var arrValidationDelete = [
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_DELETE.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_DELETE.VALIDATION_EXTERNAL_ID'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_DELETE.IN_USE'),
	];

	// Submit delete
	$scope.deleteMasterSourceDiagnosis = function () {
		$scope.toDelete = {};
		$scope.toDelete[0] = {
			"externalId": $scope.diagnosisToDelete.externalId,
			"source": $scope.diagnosisToDelete.source
		};

		$.ajax({
			type: "POST",
			url: "master-source/delete/diagnoses",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				err.responseText = JSON.parse(err.responseText)[0];
				ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_DELETE.ERROR'), arrValidationDelete);
			},
			complete: function() {
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});