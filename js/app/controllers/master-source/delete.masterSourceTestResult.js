angular.module('opalAdmin.controllers.masterSourceTestResult.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('masterSourceTestResult.delete', function ($scope, $filter, $uibModal, $uibModalInstance, uiGridConstants, $state, Session, ErrorHandler) {
	var arrValidationDelete = [
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_DELETE.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_DELETE.VALIDATION_CODE'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_DELETE.NOT_FOUND'),
	];

	// Submit delete
	$scope.deleteMasterSourceTestResult = function () {
		$scope.toDelete = {};
		$scope.toDelete[0] = {
			"code": $scope.testResultToDelete.code,
			"source": $scope.testResultToDelete.source
		};

		$.ajax({
			type: "POST",
			url: "master-source/delete/test-results",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				err.responseText = JSON.parse(err.responseText)[0];
				ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_DELETE.ERROR'), arrValidationDelete);
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