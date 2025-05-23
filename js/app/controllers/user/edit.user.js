// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.user.edit', ['ui.bootstrap', 'ui.grid']).

controller('user.edit', function ($scope, $uibModal, $uibModalInstance, $filter, $rootScope, userCollectionService, Session, ErrorHandler) {
	var OAUserId = Session.retrieveObject('user').id;
	$scope.roleDisabled = false;
	// Default booleans
	$scope.changesMade = false;
	$scope.passwordChange = false;
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
		$scope.isPasswordRequired = isPasswordRequired();
		// introduce synchronous functions to run in order
		userCollectionService.getAdditionalPrivileges().then(
			function(response){
				$scope.additionalprivileges = response.data;
				userCollectionService.getUserSelectedAdditionalPrivileges($scope.user.username).then(
					function(response){
						get_selected_additional_privileges(response.data);
					}).catch(function(err) {
						ErrorHandler.onError(err, $filter('translate')('USERS.EDIT.ERROR_USER_ADDITIONAL_PRIVILEGES'));
					})
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('USERS.EDIT.ERROR_ADDITIONAL_PRIVILEGES'));
			})
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('USERS.EDIT.ERROR_DETAILS'));
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
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('USERS.EDIT.ERROR_ROLES'));
	});

	// Function to create a map for the selected additional privileges
	$scope.user.selected_additionalprivileges = [];
	function get_selected_additional_privileges(selected_additionalprivileges_list) {
		$scope.user.selected_additionalprivileges = [];
		for (const [,value] of Object.entries($scope.additionalprivileges)) {
			  if (selected_additionalprivileges_list.groups.includes(value.pk)) {
				  group_dict={}
				  group_dict.pk = value.pk;
				  group_dict.name = value.name;
				  $scope.user.selected_additionalprivileges.push(group_dict);
			  }
		}
	}

	// Function to check whether a password is required for the user
	function isPasswordRequired() {
		// if AD is enabled the password is only not required if it is a human user
		return !($rootScope.isADEnabled && $scope.user.type === 1);
	}

	// Function that triggers when the password fields are updated
	$scope.passwordUpdate = function () {
		$scope.changesMade = true;
	};
	// Function to validate password
	$scope.validPassword = { status: null, message: null };
	$scope.validatePassword = function (password) {

		$scope.passwordChange = true;
		$scope.validateConfirmPassword($scope.user.confirmPassword);

		if (!password) {
			$scope.validPassword.status = null;
			$scope.passwordUpdate();
			if (!$scope.validConfirmPassword)
				$scope.passwordChange = false;
			return;
		}

		//Password validation
		//minimum 8 characters, 1 number, 1 lower case letter, 1 upper case letter and 1 special character
		var validationPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{8,}$/;
		if(!password.match(validationPassword)) {
			$scope.validPassword.status = 'invalid';
			$scope.validPassword.message = $filter('translate')('USERS.EDIT.ERROR_PASSWORD_FORMAT');
			$scope.passwordUpdate();
			return;
		} else {
			$scope.validPassword.status = 'valid';
			$scope.validPassword.message = null;
			$scope.passwordUpdate();
			if ($scope.validConfirmPassword.status == 'valid')
				$scope.passwordChange = false;
		}
	};

	// Function to validate confirm password
	$scope.validConfirmPassword = { status: null, message: null };
	$scope.validateConfirmPassword = function (confirmPassword) {

		$scope.passwordChange = true;
		if (!confirmPassword) {
			$scope.validConfirmPassword.status = null;
			$scope.passwordUpdate();
			if (!$scope.validPassword)
				$scope.passwordChange = false;
			return;
		}

		if ($scope.validPassword.status != 'valid' || $scope.user.password != $scope.user.confirmPassword) {
			$scope.validConfirmPassword.status = 'invalid';
			$scope.validConfirmPassword.message = $filter('translate')('USERS.EDIT.ERROR_PASSWORD_INVALID');
			$scope.passwordUpdate();
			return;
		} else {
			$scope.validConfirmPassword.status = 'valid';
			$scope.validConfirmPassword.message = null;
			$scope.passwordUpdate();
			if ($scope.validPassword.status == 'valid')
				$scope.passwordChange = false;
		}
	};

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
		return ($scope.changesMade && !$scope.passwordChange) ||
			($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid');
	};

	// Submit changes
	$scope.updateUser = function () {
		if ($scope.checkForm()) {
			// set the additional privileges to the form `{'groups': [group_1.pk,...]}`
			var additionalprivileges_map = {'groups':[]};
			if ($scope.user.selected_additionalprivileges && $scope.user.selected_additionalprivileges.length > 0) {
				for (const item in $scope.user.selected_additionalprivileges) {
					additionalprivileges_map.groups.push($scope.user.selected_additionalprivileges[item].pk);
				}
			}
			$scope.user.additionalprivileges_map = additionalprivileges_map;
			var data = {
				OAUserId: Session.retrieveObject('user').id,
				id: $scope.user.serial,
				password: $scope.user.password,
				confirmPassword: $scope.user.confirmPassword,
				language: $scope.user.language,
				roleId: $scope.user.role.serial,
				selected_additionalprivileges: $scope.user.additionalprivileges_map,
				edited_username: $scope.user.username,
			};
			// submit
			$.ajax({
				type: "POST",
				url: "user/update/user",
				data: data,
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('USERS.EDIT.SUCCESS_EDIT') ;
					$scope.showBanner();
				},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('USERS.EDIT.ERROR_UPDATE'));
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
