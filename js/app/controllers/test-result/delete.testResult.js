angular.module('opalAdmin.controllers.testResult.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('testResult.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, testResultCollectionService, educationalMaterialCollectionService, Session) {
	// Submit delete
	$scope.deleteTestResult = function () {
		// Log who deleted test result
		var currentUser = Session.retrieveObject('user');
		$scope.testResultToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "test-result/delete/test-result",
			data: $scope.testResultToDelete,
			success: function (response) {
				response = JSON.parse(response);
				// Show success or failure depending on response
				if (response.value) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('TEST.DELETE.SUCCESS');
					$scope.showBanner();
				}
				else {
					alert($filter('translate')('TEST.DELETE.ERROR') + "\r\n\r\n" + response.message);
				}
				$uibModalInstance.close();
			},
			error: function (err) {
				alert($filter('translate')('TEST.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});