angular.module('opalAdmin.controllers.alias.delete', [])

	.controller('alias.delete', function ($scope, $uibModalInstance, Session) {
		// Submit delete
		$scope.deleteAlias = function () {

			// Log who updated alias
			var currentUser = Session.retrieveObject('user');
			$scope.aliasToDelete.user = currentUser;

			$.ajax({
				type: "POST",
				url: "php/alias/delete.alias.php",
				data: $scope.aliasToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.aliasToDelete.name_EN + "/ " + $scope.aliasToDelete.name_FR + "\"!";
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