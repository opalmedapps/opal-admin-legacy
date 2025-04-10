// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.alert.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('alert.edit', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, alertCollectionService, Session, ErrorHandler) {
	const phoneNum = RegExp(/^\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{4})$/);
	const emailValid = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	$scope.toSubmit = {
		ID: "",
		message: {
			subject: "",
			body: "",
		},
		trigger: "",
		contact: {
			phone: [],
			email: [],
		}
	};

	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.validator = {
		message: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		trigger: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		contact: {
			phone: {
				completed: true,
				valid: true,
			},
			email: {
				completed: true,
				valid: true,
			},
			completed: true,
			mandatory: true,
			valid: true,
		},
	};

	$scope.language = Session.retrieveObject('user').language;

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

	// Call our API service to get the current diagnosis translation details
	alertCollectionService.getAlertDetails($scope.currentAlert.ID).then(function (response) {
		var dateArray, year, month, date;
		$scope.toSubmit.ID = response.data.ID;
		$scope.toSubmit.message.subject = response.data.subject;
		$scope.toSubmit.message.body = response.data.body;
		$scope.toSubmit.trigger = response.data.trigger;
		var contact = JSON.parse(response.data.contact);
		if(contact["phone"]) {
			angular.forEach(contact["phone"], function(phone) {
				$scope.toSubmit.contact.phone.push({"num":formatPhoneNumber(phone)});
			});
		}
		if(contact["email"]) {
			angular.forEach(contact["email"], function(email) {
				$scope.toSubmit.contact.email.push({"adr":email});
			});
		}
		$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('ALERT.EDIT.ERROR_DETAILS'));
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	$scope.messageUpdate = function () {
		$scope.validator.message.completed = ($scope.toSubmit.message.subject !== "" && $scope.toSubmit.message.body !== "");
	};

	$scope.triggerUpdate = function () {
		$scope.validator.trigger.completed = ($scope.toSubmit.trigger !== "");
	};

	$scope.$watch('toSubmit', function() {
		$scope.changesDetected = JSON.stringify($scope.toSubmit) !== JSON.stringify($scope.oldData);
	}, true);

	$scope.addPhone = function () {
		$scope.toSubmit.contact.phone.push({num: ""});
	};

	$scope.removePhone = function (order) {
		$scope.toSubmit.contact.phone.splice(order, 1);
	};

	$scope.addEmail = function () {
		$scope.toSubmit.contact.email.push({adr: ""});
	};

	$scope.removeEmail = function (order) {
		$scope.toSubmit.contact.email.splice(order, 1);
	};

	$scope.$watch('validator', function() {
		var totalsteps = 0;
		var completedSteps = 0;
		var nonMandatoryTotal = 0;
		var nonMandatoryCompleted = 0;
		angular.forEach($scope.validator, function(value) {
			if(value.mandatory)
				totalsteps++;
			else
				nonMandatoryTotal++;
			if(value.mandatory && value.completed)
				completedSteps++;
			else if(!value.mandatory) {
				if(value.completed) {
					if (value.valid)
						nonMandatoryCompleted++;
				}
				else
					nonMandatoryCompleted++;
			}

		});
		$scope.formReady = (completedSteps >= totalsteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
	}, true);

	$scope.$watch('toSubmit.contact.phone', function () {
		var isPhone = false;
		$scope.validator.contact.phone.valid = true;
		if($scope.toSubmit.contact.phone.length > 0) {
			isPhone = true;
			var anyBadPhone = false;
			angular.forEach($scope.toSubmit.contact.phone, function(phone) {
				if(!phoneNum.test(phone["num"]))
					anyBadPhone = true;
			});
			$scope.validator.contact.phone.valid = !anyBadPhone;
		}
		$scope.validator.contact.phone.completed = ($scope.validator.contact.phone.valid && isPhone);
		$scope.validator.contact.completed = ($scope.toSubmit.contact.phone.length <= 0 && $scope.toSubmit.contact.email.length <= 0) ? false : (($scope.toSubmit.contact.email.length > 0 ? $scope.validator.contact.email.valid : true) && ($scope.toSubmit.contact.phone.length > 0 ? $scope.validator.contact.phone.valid : true));
	}, true);

	$scope.$watch('toSubmit.contact.email', function () {
		$scope.validator.contact.email.valid = true;
		var isEmail = false;
		if($scope.toSubmit.contact.email.length > 0) {
			isEmail = true;
			var anyBadEmail = false;
			angular.forEach($scope.toSubmit.contact.email, function(email) {
				if(!emailValid.test(email["adr"]))
					anyBadEmail = true;
			});
			$scope.validator.contact.email.valid = !anyBadEmail;
		}
		$scope.validator.contact.email.completed = ($scope.validator.contact.email.valid && isEmail);
		$scope.validator.contact.completed = ($scope.toSubmit.contact.phone.length <= 0 && $scope.toSubmit.contact.email.length <= 0) ? false : (($scope.toSubmit.contact.email.length > 0 ? $scope.validator.contact.email.valid : true) && ($scope.toSubmit.contact.phone.length > 0 ? $scope.validator.contact.phone.valid : true));
	}, true);

	// Submit changes
	$scope.updateAlert = function() {
		if($scope.formReady && $scope.changesDetected) {
			$.ajax({
				type: "POST",
				url: "alert/update/alert",
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('ALERT.EDIT.ERROR_UPDATE'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
	};

	function formatPhoneNumber(phoneNumberString) {
		var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
		var match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/)
		if (match) {
			return match[1] + '-' + match[2] + '-' + match[3];
		}
		return null;
	}

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});
