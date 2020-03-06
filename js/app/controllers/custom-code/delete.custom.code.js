angular.module('opalAdmin.controllers.customCode.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('customCode.delete', function ($scope, $filter, $uibModal, $uibModalInstance, diagnosisCollectionService, educationalMaterialCollectionService, uiGridConstants, $state, Session) {

	// Submit delete
	$scope.deleteCustomCode = function () {
		$scope.toDelete = {};
		$scope.toDelete.customCodeId = $scope.customCodeToDelete.ID;
		$scope.toDelete.moduleId = $scope.customCodeToDelete.moduleId;
		$scope.toDelete.OAUserId = Session.retrieveObject('user').id;

		$.ajax({
			type: "POST",
			url: "custom-code/delete/custom-code",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('CUSTOM_CODE.DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				alert($filter('translate')('CUSTOM_CODE.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
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