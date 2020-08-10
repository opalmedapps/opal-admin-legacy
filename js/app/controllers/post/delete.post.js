angular.module('opalAdmin.controllers.post.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).

	controller('post.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, uiGridConstants, Session, ErrorHandler) {
		// Submit delete
		$scope.deletePost = function () {
			// Log who updated post 
			var currentUser = Session.retrieveObject('user');
			$scope.postToDelete.OAUser = currentUser;

			$.ajax({
				type: "POST",
				url: "post/delete/post",
				data: $scope.postToDelete,
				success: function (response) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('POSTS.DELETE.SUCCESS');
						$scope.showBanner();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('POSTS.DELETE.ERROR'));
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