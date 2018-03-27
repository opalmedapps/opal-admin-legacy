angular.module('opalAdmin.controllers.post.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).

	controller('post.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, postCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deletePost = function () {
			// Log who updated post 
			var currentUser = Session.retrieveObject('user');
			$scope.postToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/post/delete.post.php",
				data: $scope.postToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.postToDelete.name_EN + "/ " + $scope.postToDelete.name_FR + "\"!";
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