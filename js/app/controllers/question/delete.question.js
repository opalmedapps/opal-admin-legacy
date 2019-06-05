angular.module('opalAdmin.controllers.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('question.delete', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deleteQuestion = function () {
			$.ajax({
				type: "POST",
				url: "php/questionnaire/delete.question.php",
				data: {"ID": $scope.questionToDelete.serNum, "OAUserId": Session.retrieveObject('user').id},
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.questionToDelete.text_EN + "/ " + $scope.questionToDelete.text_FR + "\"!";
					}
					else {
						$scope.setBannerClass('danger');
						var errMsg = "";
						switch(response.message) {
							case 401:
								errMsg = "You are not authenticated!";
								break;
							case 403:
								errMsg = "You do not have the permission to delete this question.";
								break;
							case 409:
								errMsg = "The question was already modified by someone else. Please verify and try again.";
								break;
							case 423:
								errMsg = "The question was already sent and is now locked.";
								break;
							default:
								errMsg = response.message;
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