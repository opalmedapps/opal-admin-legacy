angular.module('opalAdmin.controllers.educationalMaterial.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).

	controller('educationalMaterial.delete', function ($scope, $filter, $sce, $uibModal, $uibModalInstance, $state, educationalMaterialCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deleteEducationalMaterial = function () {
			// Log who deleted educational material
			var currentUser = Session.retrieveObject('user');
			$scope.eduMatToDelete.user = currentUser;

			$.ajax({
				type: "POST",
				url: "php/educational-material/delete.educational_material.php",
				data: $scope.eduMatToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.eduMatToDelete.name_EN + "/ " + $scope.eduMatToDelete.name_FR + "\"!";
					}
					else {
						$scope.setBannerClass('danger');
						$scope.bannerMessage = response.message;
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