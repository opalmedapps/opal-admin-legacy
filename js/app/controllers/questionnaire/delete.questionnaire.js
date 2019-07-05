angular.module('opalAdmin.controllers.questionnaire.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaire.delete', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, Session) {

		// Submit delete
		$scope.deleteQuestionnaire = function () {
			// Log who deleted questionnaire
			var currentUser = Session.retrieveObject('user');
			$scope.questionnaireToDelete.OAUserId = currentUser.id;
			$.ajax({
				type: "POST",
				url: "questionnaire/delete/questionnaire",
				data: $scope.questionnaireToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.message === 200) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.questionnaireToDelete.name_EN + "/ " + $scope.questionnaireToDelete.name_FR + "\"!";
					}
					else {
						$scope.setBannerClass('danger');
						$scope.$parent.bannerMessage = "Code " + response.message + ". " + response.details;
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