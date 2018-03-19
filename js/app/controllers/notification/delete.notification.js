angular.module('opalAdmin.controllers.notification.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('notification.delete', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, notificationCollectionService, Session) {

		// Submit delete
		$scope.deleteNotification = function () {
			// Log who deleted notification
			var currentUser = Session.retrieveObject('user');
			$scope.notificationToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/notification/delete.notification.php",
				data: $scope.notificationToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.notificationToDelete.name_EN + "/ " + $scope.notificationToDelete.name_FR + "\"!";
					}
					else {
						$scope.setBannerClass('danger');
						$scope.$parent.bannerMessage = response.message;
					}
					$scope.showBanner();
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


	});