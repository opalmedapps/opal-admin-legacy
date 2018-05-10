angular.module('opalAdmin.controllers.user.delete', ['ui.bootstrap', 'ui.grid']).

	controller('user.delete', function ($scope, $uibModal, $uibModalInstance,  $filter, $sce, $state, userCollectionService, Encrypt) {

		// Submit delete
		$scope.deleteUser = function () {
			$.ajax({
				type: "POST",
				url: "php/user/delete.user.php",
				data: $scope.userToDelete,
				success: function (response) {
					response = JSON.parse(response);
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully delete \"" + $scope.userToDelete.username + "\"";
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