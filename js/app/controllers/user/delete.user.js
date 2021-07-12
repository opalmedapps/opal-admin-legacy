angular.module('opalAdmin.controllers.user.delete', ['ui.bootstrap', 'ui.grid']).

controller('user.delete', function ($scope, $uibModal, $uibModalInstance,  $filter, $sce, $state, userCollectionService, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteUser = function () {
		if($scope.userToDelete.serial !== Session.retrieveObject('user').id) {
			$.ajax({
				type: "POST",
				url: "user/delete/user",
				data: {"ID": $scope.userToDelete.serial, "OAUserId": Session.retrieveObject('user').id},
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('USERS.DELETE.SUCCESS');
					$scope.showBanner();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('USERS.DELETE.ERROR'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
		else {
			alert($filter('translate')('USERS.DELETE.ERROR_SAME_USER'));
			$uibModalInstance.close();
		}
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});