angular.module('opalAdmin.controllers.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.delete', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, uiGridConstants, Session, ErrorHandler) {

		// Submit delete
		$scope.deleteQuestion = function () {
			$.ajax({
				type: "POST",
				url: "question/delete/question",
				data: {"ID": $scope.questionToDelete.serNum, "OAUserId": Session.retrieveObject('user').id},
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.DELETED');
					}
					$scope.showBanner();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.ERROR'));
				},
				complete: function (err) {
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});