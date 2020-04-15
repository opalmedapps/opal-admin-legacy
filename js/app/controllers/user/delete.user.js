angular.module('opalAdmin.controllers.user.delete', ['ui.bootstrap', 'ui.grid']).

controller('user.delete', function ($scope, $uibModal, $uibModalInstance,  $filter, $sce, $state, userCollectionService, Encrypt, Session) {

	// Submit delete
	$scope.deleteUser = function () {
		if($scope.userToDelete.serial !== Session.retrieveObject('user').id) {
			$.ajax({
				type: "POST",
				url: "user/delete/user",
				data: {"ID": $scope.userToDelete.serial, "OAUserId": Session.retrieveObject('user').id},
				success: function (response) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('USERS.DELETE.SUCCESS');
				},
				error: function (err) {
					$scope.setBannerClass('danger');
					$scope.$parent.bannerMessage = $filter('translate')('USERS.DELETE.ERROR') + err.status + " - " + err.responseText;
				},
				complete: function () {
					$scope.showBanner();
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