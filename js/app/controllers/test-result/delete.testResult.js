angular.module('opalAdmin.controllers.testResult.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('testResult.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, Session, ErrorHandler) {
	// Submit delete
	$scope.deleteTestResult = function () {
		// Log who deleted test result
		var currentUser = Session.retrieveObject('user');
		$scope.testResultToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "test-result/delete/test-result",
			data: $scope.testResultToDelete,
			success: function () {},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('TEST.DELETE.ERROR'));
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