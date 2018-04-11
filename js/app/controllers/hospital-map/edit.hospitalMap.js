angular.module('opalAdmin.controllers.hospitalMap.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('hospitalMap.edit', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, hospitalMapCollectionService, Session) {
	
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
			$scope.$parent.oldqrid = response.data.qrid;
			$scope.mapURL_EN = response.data.url_EN;
			$scope.mapURL_FR = response.data.url_FR;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function(response) {
			console.error('Error occurred getting hospital map details:', response.status, response.data);
		});

		// Function to call api to generate qr code
		$scope.generateQRCode = function (qrid) {

			if (qrid && $scope.changesMade) {
				hospitalMapCollectionService.generateQRCode(qrid, $scope.$parent.oldqrid).then(function (response) {
					$scope.hosMap.qrcode = response.data.qrcode;
					$scope.hosMap.qrpath = response.data.qrpath;

					$scope.$parent.oldqrid = qrid;

				}).catch(function(response) {
					console.error('Error occurred generating QR code:', response.status, response.data);
				});
			}
			else if (!qrid) {
				$scope.hosMap.qrcode = "";
				$scope.hosMap.qrpath = "";
			}

		};
		// Function to show map
		$scope.showMap = function (url, language) {
			if (language == 'EN')
				$scope.mapURL_EN = url;
			else if (language == 'FR')
				$scope.mapURL_FR = url;
		};


		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.hosMap.name_EN && $scope.hosMap.name_FR && $scope.hosMap.description_EN
				&& $scope.hosMap.description_FR && $scope.hosMap.qrid && $scope.hosMap.qrcode && $scope.hosMap.url_EN
				&& $scope.hosMap.url_FR && $scope.changesMade) {
				return true;
			}
			else
				return false;
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
					url: "php/hospital-map/update.hospital_map.php",
					data: $scope.hosMap,
					success: function () {
						$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.hosMap.name_EN + "/ " + $scope.hosMap.name_FR + "\"!";
						$scope.showBanner();
						$scope.$parent.updatedHosMap = true;
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
