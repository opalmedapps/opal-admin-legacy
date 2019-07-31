angular.module('opalAdmin.controllers.template.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('template.question.delete', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deleteQuestion = function () {
			$.ajax({
				type: "POST",
				url: "template-question/delete/template-question",
				data: {"ID": $scope.templateQuestionToDelete.ID, "OAUserId": Session.retrieveObject('user').id},
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.DELETED');
					}
					else {
						$scope.setBannerClass('danger');
						var errMsg = "";
						switch(response.message) {
							case 401:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.ERROR_AUTHENTICATED');
								break;
							case 403:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.ERROR_PERMISSION');
								break;
							case 409:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.ERROR_MODIFIED');
								break;
							default:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.TEMPLATE_QUESTION_DELETE.ERROR_UNKNOWN') + "\r\n\r\n" + response.message;
						}
						$scope.$parent.bannerMessage = errMsg;
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