angular.module('opalAdmin.controllers.post.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).

	controller('post.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, postCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deletePost = function () {
			// Log who updated post 
			var currentUser = Session.retrieveObject('user');
			$scope.postToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "post/delete/post",
				data: $scope.postToDelete,
				success: function (response) {
					response = JSON.parse(response);
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('POSTS.DELETE.SUCCESS');
						$scope.showBanner();
					}
					else {
						alert($filter('translate')('POSTS.DELETE.ERROR') + "\r\n\r\n" + response.message);
					}
					$uibModalInstance.close();
				},
				error: function (err) {
					alert($filter('translate')('POSTS.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});