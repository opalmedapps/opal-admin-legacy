angular.module('opalAdmin.controllers.study.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('study.delete', function ($scope, $filter, $uibModal, $uibModalInstance, Session) {

	// Submit delete
	$scope.deleteStudy = function () {
		$scope.toDelete = {};
		$scope.toDelete.studyId = $scope.studyToDelete.ID;
		$scope.toDelete.OAUserId = Session.retrieveObject('user').id;

		$.ajax({
			type: "POST",
			url: "study/delete/study",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('STUDY.DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				alert($filter('translate')('STUDY.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
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