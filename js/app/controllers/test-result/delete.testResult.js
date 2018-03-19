angular.module('opalAdmin.controllers.testResult.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('testResult.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, testResultCollectionService, educationalMaterialCollectionService, Session) {

		
		// Submit delete
		$scope.deleteTestResult = function () {
			// Log who deleted test result 
			var currentUser = Session.retrieveObject('user');
			$scope.testResultToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/test-result/delete.test_result.php",
				data: $scope.testResultToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.testResultToDelete.name_EN + "/ " + $scope.testResultToDelete.name_FR + "\"!";
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