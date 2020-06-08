angular.module('opalAdmin.controllers.user.edit.ad', ['ui.bootstrap', 'ui.grid']).

controller('user.edit.ad', function ($scope, $uibModal, $uibModalInstance, $filter, $sce, $state, userCollectionService, Encrypt, Session) {
	var OAUserId = Session.retrieveObject('user').id;
	$scope.roleDisabled = false;

	// Default booleans
	$scope.changesMade = false;
	$scope.language = Session.retrieveObject('user').language;

	$scope.user = {};

	// Initialize a list of languages available
	$scope.languages = [{
		name: $filter('translate')('USERS.ADD.ENGLISH'),
		id: 'EN'
	}, {
		name: $filter('translate')('USERS.ADD.FRENCH'),
		id: 'FR'
	}];

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

	// Call our API service to get the current user's details
	userCollectionService.getUserDetails($scope.currentUser.serial).then(function (response) {
		$scope.user = response.data;
		$scope.roleDisabled = (OAUserId == $scope.user.serial);
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	}).catch(function(response) {
		alert($filter('translate')('USERS.EDIT.ERROR_DETAILS') + "\r\n\r\n" + response.status + " " + response.data);
	});

	// Call our API service to get the list of possible roles
	$scope.roles = [];
	userCollectionService.getRoles(OAUserId).then(function (response) {
		response.data.forEach(function(row) {
			if($scope.language.toUpperCase() === "FR")
				row.name_display = row.name_FR;
			else
				row.name_display = row.name_EN;
		});
		$scope.roles = response.data;
	}).catch(function(response) {
		alert($filter('translate')('USERS.EDIT.ERROR_ROLES') + "\r\n\r\n" + response.status + " " + response.data);
	});

	// Function that triggers when the role field is updated
	$scope.roleUpdate = function () {

		$scope.changesMade = true;
	};

	// Function that triggers when the language field is updated
	$scope.languageUpdate = function () {

		$scope.changesMade = true;
	};

	// Function to check for form completion
	$scope.checkForm = function () {
		if ($scope.changesMade)
			return true;
		else
			return false;
	};

	// Submit changes
	$scope.updateUser = function () {
		if ($scope.checkForm()) {
			var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103;

			var encrypted = {
				id: $scope.user.serial,
				language: $scope.user.language,
				roleId: $scope.newUser.role.ID,
			};
			encrypted = Encrypt.encode(JSON.stringify(encrypted), cypher);

			var data = {
				OAUserId: Session.retrieveObject('user').id,
				encrypted: encrypted,
				cypher: cypher,
			};

			// submit
			$.ajax({
				type: "POST",
				url: "user/update/user",
				data: data,
				success: function (response) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('USERS.EDIT.SUCCESS_EDIT') ;
					$scope.showBanner();
				},
				error: function(err) {
					alert($filter('translate')('USERS.EDIT.ERROR_UPDATE') + "\r\n\r\n" + err.status + " - " + err.responseText);
				},
				complete: function() {
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