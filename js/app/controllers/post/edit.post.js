angular.module('opalAdmin.controllers.post.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).


// Function to accept/trust html (styles, classes, etc.)
filter('deliberatelyTrustAsHtml', function ($sce) {
	return function (text) {
		return $sce.trustAsHtml(text);
	};
}).
controller('post.edit', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, $locale, postCollectionService, uiGridConstants, Session, ErrorHandler) {

	// Default Booleans
	$scope.changesMade = false; // changes have been made?
	$scope.language = Session.retrieveObject('user').language;
	$scope.post = {}; // initialize post object
	$scope.postModal = {}; // for deep copy
	$scope.name_display = null;

	$scope.postTypes = [
		{
			name: 'Announcement',
			name_display: $filter('translate')('POSTS.ADD.ANNOUNCEMENT'),
			icon: 'bullhorn'
		},
		{
			name: 'Treatment Team Message',
			name_display: $filter('translate')('POSTS.ADD.TREATMENT_TEAM_MESSAGE'),
			icon: 'user-md'
		}
	];

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};
	// Show processing dialog
	$scope.showProcessingModal();

	// Call our API service to get the current post details
	postCollectionService.getPostDetails($scope.currentPost.serial, Session.retrieveObject('user').id).then(function (response) {
		$scope.postTypes.forEach(function(entry) {
			if(entry.name === response.data.type) {
				$scope.name_display = entry.name_display;
			}
		});
		$scope.post = response.data;
		$scope.postModal = jQuery.extend(true, {}, $scope.post); // deep copy
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('POSTS.EDIT.ERROR_DETAILS'));
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		return ($scope.post.name_EN && $scope.post.name_FR && $scope.post.body_EN && $scope.post.body_FR
			&& $scope.changesMade);
	};

	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	$scope.detailsUpdated = function () {
		$scope.post.details_updated = 1;
		$scope.setChangesMade();
	}

	// Submit changes
	$scope.updatePost = function () {

		if ($scope.checkForm()) {
			// For some reason the HTML text fields add a zero-width-space
			// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
			$scope.post.body_EN = $scope.post.body_EN.replace(/\u200B/g,'');
			$scope.post.body_FR = $scope.post.body_FR.replace(/\u200B/g,'');

			// Log who updated post
			var currentUser = Session.retrieveObject('user');
			$scope.post.OAUser = currentUser;
			// Submit form
			$.ajax({
				type: "POST",
				url: "post/update/post",
				data: $scope.post,
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('POSTS.EDIT.SUCCESS_EDIT') ;
					$scope.showBanner();
				},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('POSTS.EDIT.ERROR_EDIT'));
				},
				complete: function() {
					$uibModalInstance.close();
				}
			});
		}
	};

	$scope.popup = {
		opened: false
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});