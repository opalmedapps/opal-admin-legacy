angular.module('opalAdmin.controllers.notification.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('notification.delete', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, notificationCollectionService, Session) {

	// Submit delete
	$scope.deleteNotification = function () {
		// Log who deleted notification
		var currentUser = Session.retrieveObject('user');
		$scope.notificationToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "notification/delete/notification",
			data: $scope.notificationToDelete,
			success: function (response) {
				response = JSON.parse(response);
				// Show success or failure depending on response
				if (response.value) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('NOTIFICATIONS.DELETE.SUCCESS');
					$scope.showBanner();
				}
				else {
					alert($filter('translate')('NOTIFICATIONS.DELETE.ERROR') + "\r\n\r\n" + response.message);
				}
				$uibModalInstance.close();

			},
			error: function (err) {
				alert($filter('translate')('NOTIFICATIONS.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});