angular.module('opalAdmin.controllers.educationalMaterial.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).

controller('educationalMaterial.delete', function ($scope, $filter, $sce, $uibModal, $uibModalInstance, $state, educationalMaterialCollectionService, filterCollectionService, uiGridConstants, Session) {

	// Submit delete
	$scope.deleteEducationalMaterial = function () {
		// Log who deleted educational material
		var currentUser = Session.retrieveObject('user');
		$scope.eduMatToDelete.user = currentUser;

		$.ajax({
			type: "POST",
			url: "educational-material/delete/educational-material",
			data: $scope.eduMatToDelete,
			success: function (response) {
				response = JSON.parse(response);
				// Show success or failure depending on response
				if (response.value) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('EDUCATION.DELETE.SUCCESS');
					$scope.showBanner();
				}
				else {
					alert($filter('translate')('EDUCATION.DELETE.ERROR') + "\r\n\r\n" + response.message);
				}
				$uibModalInstance.close();
			},
			error: function (err) {
				alert($filter('translate')('EDUCATION.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});