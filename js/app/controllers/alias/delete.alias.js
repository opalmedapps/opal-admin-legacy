angular.module('opalAdmin.controllers.alias.delete', [])

	.controller('alias.delete', function ($scope, $uibModalInstance, $filter, Session) {
		// Submit delete
		$scope.deleteAlias = function () {

			// Log who updated alias
			var currentUser = Session.retrieveObject('user');
			$scope.aliasToDelete.user = currentUser;

			$.ajax({
				type: "POST",
				url: "alias/delete/alias",
				data: $scope.aliasToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('ALIAS.DELETE.SUCCESS');
						$scope.showBanner();
					}
					else {
						alert($filter('translate')('ALIAS.DELETE.ERROR') + "\r\n\r\n" + response.message);
					}
					$uibModalInstance.close();
				},
				error: function (err) {
					alert($filter('translate')('ALIAS.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});