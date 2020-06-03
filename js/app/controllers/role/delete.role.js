angular.module('opalAdmin.controllers.role.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('role.delete', function ($scope, $filter, $uibModal, $uibModalInstance, Session) {

	// Submit delete
	$scope.deleteRole = function () {
		$scope.toDelete = {};
		$scope.toDelete.roleId = $scope.roleToDelete.ID;
		$scope.toDelete.OAUserId = Session.retrieveObject('user').id;

		$.ajax({
			type: "POST",
			url: "role/delete/role",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('ROLE.DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				alert($filter('translate')('ROLE.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
			},
			complete: function() {
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});