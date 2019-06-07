angular.module('opalAdmin.controllers.template.question.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns'])

	.controller('template.question.delete', function ($scope, $state, $filter, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deleteQuestion = function () {
			$.ajax({
				type: "POST",
				url: "php/questionnaire/delete.template.question.php",
				data: {"ID": $scope.templateQuestionToDelete.ID, "OAUserId": Session.retrieveObject('user').id},
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.templateQuestionToDelete.name_EN + "/ " + $scope.templateQuestionToDelete.name_FR + "\"!";
					}
					else {
						$scope.setBannerClass('danger');
						var errMsg = "";
						switch(response.message) {
							case 401:
								errMsg = "You are not authenticated!";
								break;
							case 403:
								errMsg = "You do not have the permission to delete this answer type.";
								break;
							case 409:
								errMsg = "The answer type was already modified by someone else. Please verify and try again.";
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