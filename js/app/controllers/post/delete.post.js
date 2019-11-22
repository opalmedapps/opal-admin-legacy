angular.module('opalAdmin.controllers.post.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).

	controller('post.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, postCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Submit delete
		$scope.deletePost = function () {
			// Log who updated post 
			var currentUser = Session.retrieveObject('user');
			$scope.postToDelete.OAUser = currentUser;

			console.log($scope.postToDelete);
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
					alert($filter('translate')('POSTS.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
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