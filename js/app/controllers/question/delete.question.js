angular.module('opalAdmin.controllers.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.delete', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

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
					else {
						$scope.setBannerClass('danger');
						var errMsg = "";
						switch(response.message) {
							case 401:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.AUTHENTICATED');
								break;
							case 403:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.DELETED');
								break;
							case 409:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.MODIFIED');
								break;
							case 423:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.LOCKED');
								break;
							default:
								errMsg = $filter('translate')('QUESTIONNAIRE_MODULE.QUESTION_DELETE.UNKNOWN') + " " + response.message;
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