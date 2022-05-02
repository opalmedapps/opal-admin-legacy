angular.module('opalAdmin.controllers.hospitalMap.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('hospitalMap.edit', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, hospitalMapCollectionService, Session, ErrorHandler) {

	// Default Booleans
	$scope.changesMade = false; // changes have been made?
	$scope.hosMap = {}; // initialize map object

	// Default toolbar for wysiwyg
	$scope.toolbar = [
		['h1', 'h2', 'h3', 'p'],
		['bold', 'italics', 'underline', 'ul', 'ol'],
		['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
		['html', 'insertLink']
	];

	var arrValidationInsert = [
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.NAME_EN'),
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.NAME_FR'),
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.DESCRIPTION_EN'),
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.DESCRIPTION_FR'),
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.URL_EN'),
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.URL_FR'),
		$filter('translate')('HOSPITAL_MAPS.VALIDATION.HOSPITAL_MAP_ID'),
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

	$scope.mapURL_EN = "";
	$scope.mapURL_FR = "";

	// Call our API to get the current map details
	hospitalMapCollectionService.getHospitalMapDetails($scope.currentHosMap.serial).then(function (response) {
		$scope.hosMap = response.data;
		$scope.mapURL_EN = response.data.url_EN;
		$scope.mapURL_FR = response.data.url_FR;

		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('HOSPITAL_MAPS.EDIT.ERROR_DETAILS'));
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
		$uibModalInstance.close();
	});

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		return !!($scope.hosMap.name_EN && $scope.hosMap.name_FR && $scope.hosMap.description_EN
			&& $scope.hosMap.description_FR && $scope.hosMap.url_EN
			&& $scope.hosMap.url_FR && $scope.changesMade);
	};

	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	// Submit changes
	$scope.updateHosMap = function () {
		if ($scope.checkForm()) {

			// For some reason the HTML text fields add a zero-width-space
			// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
			$scope.hosMap.description_EN = $scope.hosMap.description_EN.replace(/\u200B/g,'');
			$scope.hosMap.description_FR = $scope.hosMap.description_FR.replace(/\u200B/g,'');

			// Log who updated hospital map
			var currentUser = Session.retrieveObject('user');
			$scope.hosMap.user = currentUser;
			// Submit form
			$.ajax({
				type: "POST",
				url: "hospital-map/update/hospital-map",
				data: $scope.hosMap,
				success: function () {
					$scope.$parent.bannerMessage = $filter('translate')('HOSPITAL_MAPS.EDIT.SUCCESS');
					$scope.showBanner();
				},
				error: function (err) {
					err.responseText = JSON.parse(err.responseText);
					ErrorHandler.onError(err, $filter('translate')('HOSPITAL_MAPS.EDIT.ERROR_UPDATE'), arrValidationInsert);
				},
				complete: function () {
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
