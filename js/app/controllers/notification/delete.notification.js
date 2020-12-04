angular.module('opalAdmin.controllers.notification.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('notification.delete', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, notificationCollectionService, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteNotification = function () {
		// Log who deleted notification
		var currentUser = Session.retrieveObject('user');
		$scope.notificationToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "notification/delete/notification",
			data: $scope.notificationToDelete,
			success: function () {},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.DELETE.ERROR'));
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