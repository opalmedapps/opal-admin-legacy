angular.module('opalAdmin.controllers.diagnosisTranslation.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('diagnosisTranslation.delete', function ($scope, $filter, $uibModal, $uibModalInstance, diagnosisCollectionService, educationalMaterialCollectionService, uiGridConstants, $state, Session) {

		// Submit delete
		$scope.deleteDiagnosisTranslation = function () {
			// Log who deleted diagnosis translation
			var currentUser = Session.retrieveObject('user');
			$scope.diagnosisTranslationToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/diagnosis-translation/delete.diagnosis_translation.php",
				data: $scope.diagnosisTranslationToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.diagnosisTranslationToDelete.name_EN + "/ " + $scope.diagnosisTranslationToDelete.name_FR + "\"!";
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