angular.module('opalAdmin.controllers.alert.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('alert.delete', function ($scope, $filter, $uibModalInstance, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteAlert = function () {
		$scope.toDelete = {};
		$scope.toDelete.alertId = $scope.alertToDelete.ID;

		$.ajax({
			type: "POST",
			url: "alert/delete/alert",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('ALERT.DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				ErrorHandler.onError(err, $filter('translate')('ALERT.DELETE.ERROR'));
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