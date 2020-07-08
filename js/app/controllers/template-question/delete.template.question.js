angular.module('opalAdmin.controllers.template.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('template.question.delete', function ($scope, $filter, $uibModalInstance, questionnaireCollectionService, Session, ErrorHandler) {

		// Submit delete
		$scope.deleteQuestion = function () {
			$.ajax({
				type: "POST",
				url: "template-question/delete/template-question",
				data: {"ID": $scope.templateQuestionToDelete.ID, "OAUserId": Session.retrieveObject('user').id},
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.DELETED');
					$scope.showBanner();
				},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.ERROR'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});