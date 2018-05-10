angular.module('opalAdmin.controllers.legacyQuestionnaire.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular', 'multipleDatePicker', 'angularjs-dropdown-multiselect']).

	controller('legacyQuestionnaire.delete', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, legacyQuestionnaireCollectionService, filterCollectionService, uiGridConstants, FrequencyFilterService, Session) {

		// Submit delete
		$scope.deleteLegacyQuestionnaire = function () {
			// Log who deleted legacy questionnaire
			var currentUser = Session.retrieveObject('user');
			$scope.legacyQuestionnaireToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/legacy-questionnaire/delete.legacy_questionnaire.php",
				data: $scope.legacyQuestionnaireToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.legacyQuestionnaireToDelete.name_EN + "/ " + $scope.legacyQuestionnaireToDelete.name_FR + "\"!";
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