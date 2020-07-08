angular.module('opalAdmin.controllers.questionnaire.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaire.delete', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, questionnaireCollectionService, Session, ErrorHandler) {

		// Submit delete
		$scope.deleteQuestionnaire = function () {
			// Log who deleted questionnaire
			var currentUser = Session.retrieveObject('user');
			$scope.questionnaireToDelete.OAUserId = currentUser.id;
			$.ajax({
				type: "POST",
				url: "questionnaire/delete/questionnaire",
				data: $scope.questionnaireToDelete,
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_DELETE.DELETED');
					$scope.showBanner();
				},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTIONNAIRE_DELETE.ERROR'));
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