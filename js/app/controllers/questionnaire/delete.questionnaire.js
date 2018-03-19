angular.module('opalAdmin.controllers.questionnaire.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaire.delete', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

		// Submit delete
		$scope.deleteQuestionnaire = function () {
			// Log who deleted questionnaire
			var currentUser = Session.retrieveObject('user');
			$scope.questionnaireToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/questionnaire/delete.questionnaire.php",
				data: $scope.questionnaireToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.questionnaireToDelete.name_EN + "/ " + $scope.questionnaireToDelete.name_FR + "\"!";
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