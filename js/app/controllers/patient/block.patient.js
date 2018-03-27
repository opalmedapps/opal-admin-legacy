angular.module('opalAdmin.controllers.patient.block', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('patient.block', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, patientCollectionService) {

		$scope.currentPatient = jQuery.extend(true, {}, $scope.patientToToggleBlock);

		// toggle block immediately
		if ($scope.currentPatient.disabled == 0)
			$scope.currentPatient.disabled = 1;
		else
			$scope.currentPatient.disabled = 0;

		// Submit (un)block
		$scope.submitToggle = function () {

			if ($scope.currentPatient.reason) {

				// Database (un)block
				$.ajax({
					type: "POST",
					url: "php/patient/toggle_block.php",
					data: $scope.currentPatient,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							var toggleText = "blocked";
							if (!$scope.currentPatient.disabled)
								toggleText = "unblocked";
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully " + toggleText + " \"" + $scope.currentPatient.name + "\"";
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
