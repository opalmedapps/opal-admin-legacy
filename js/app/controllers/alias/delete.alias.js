angular.module('opalAdmin.controllers.alias.delete', [])

	.controller('alias.delete', function ($scope, $uibModalInstance, $filter, Session, ErrorHandler) {
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
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('ALIAS.DELETE.SUCCESS');
					$scope.showBanner();
					$uibModalInstance.close();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('ALIAS.DELETE.ERROR'));
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});