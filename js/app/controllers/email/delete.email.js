angular.module('opalAdmin.controllers.email.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('email.delete', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, emailCollectionService, Session) {

		// Submit delete
		$scope.deleteEmail = function () {
			// Log who deleted email
			var currentUser = Session.retrieveObject('user');
			$scope.emailToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/email/delete.email.php",
				data: $scope.emailToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.emailToDelete.subject_EN + "/ " + $scope.emailToDelete.subject_FR + "\"!";
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
