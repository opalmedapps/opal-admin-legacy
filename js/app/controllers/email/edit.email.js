angular.module('opalAdmin.controllers.email.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('email.edit', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, emailCollectionService, Session) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 
		$scope.email = {}; // initialize email object

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

		// Call our API to get the current email details
		emailCollectionService.getEmailDetails($scope.currentEmail.serial).then(function (response) {
			$scope.email = response.data;
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function(response) {
			console.error('Error occurred getting email details:', response.status, response.data);
		});

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.email.subject_EN && $scope.email.subject_FR
				&& $scope.email.body_EN && $scope.email.body_FR
				&& $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		// Submit changes
		$scope.updateEmail = function () {
			if ($scope.checkForm()) {
				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.email.body_EN = $scope.email.body_EN.replace(/\u200B/g,'');
				$scope.email.body_FR = $scope.email.body_FR.replace(/\u200B/g,'');

				// Log who updated email
				var currentUser = Session.retrieveObject('user');
				$scope.email.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/email/update.email.php",
					data: $scope.email,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.email.subject_EN + "/ " + $scope.email.subject_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}
						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			}
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

	});
