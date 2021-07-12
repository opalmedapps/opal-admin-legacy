angular.module('opalAdmin.controllers.alert.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Add Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('alert.add', function ($scope, $filter, $uibModal, $state, $locale, Session, ErrorHandler) {

		// get current user id
		var user = Session.retrieveObject('user');

		const phoneNum = RegExp(/^\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{4})$/);
		const emailValid = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.showAssigned = false;
		$scope.hideAssigned = false;
		$scope.language = Session.retrieveObject('user').language;

		$scope.toSubmit = {
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

		$scope.validator = {
			message: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			trigger: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			contact: {
				phone: {
					completed: false,
					valid: true,
				},
				email: {
					completed: false,
					valid: true,
				},
				completed: false,
				mandatory: true,
				valid: true,
			},
		};

		$scope.leftMenu = {
			message: {
				display: false,
				open: false,
				preview: false,
			},
			trigger: {
				display: false,
				open: false,
				preview: false,
			},
			contact: {
				display: false,
				open: false,
				preview: false,
			},
		};

		$scope.totalSteps = 0;
		$scope.completedSteps = 0;
		$scope.formReady = false;

		/* Function for the "Processing..." dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};

		$scope.formLoaded = false;
		// Function to load form as animations
		$scope.loadForm = function () {
			$('.form-box-left').addClass('fadeInDown');
			$('.form-box-right').addClass('fadeInRight');
		};

		$scope.popupStart = {};
		$scope.popupEnd = {};
		$scope.openStart = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();
			$scope.popupStart['opened'] = true;
			$scope.popupEnd['opened'] = false;
		};
		$scope.openEnd = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();
			$scope.popupStart['opened'] = false;
			$scope.popupEnd['opened'] = true;
		};

		$scope.messageUpdate = function () {
			$scope.validator.message.completed = ($scope.toSubmit.message.subject !== "" && $scope.toSubmit.message.body !== "");
			$scope.leftMenu.message.open = ($scope.toSubmit.message.subject !== "" || $scope.toSubmit.message.body !== "");
			$scope.leftMenu.message.display = $scope.leftMenu.message.open;
		};

		$scope.triggerUpdate = function () {
			$scope.validator.trigger.completed = ($scope.toSubmit.trigger !== "");
			$scope.leftMenu.trigger.open = $scope.validator.trigger.completed;
			$scope.leftMenu.trigger.display = $scope.validator.trigger.completed;
		};

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
			$scope.leftMenu.contact.open = ($scope.toSubmit.contact.phone.length > 0 || $scope.toSubmit.contact.email.length > 0);
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
			$scope.leftMenu.contact.open = ($scope.toSubmit.contact.phone.length > 0 || $scope.toSubmit.contact.email.length > 0);
			$scope.validator.contact.email.completed = ($scope.validator.contact.email.valid && isEmail);
			$scope.validator.contact.completed = ($scope.toSubmit.contact.phone.length <= 0 && $scope.toSubmit.contact.email.length <= 0) ? false : (($scope.toSubmit.contact.email.length > 0 ? $scope.validator.contact.email.valid : true) && ($scope.toSubmit.contact.phone.length > 0 ? $scope.validator.contact.phone.valid : true));
		}, true);

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

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
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

		// Function to submit the new diagnosis translation
		$scope.submitAlert = function () {
			$.ajax({
				type: 'POST',
				url: 'alert/insert/alert',
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('ALERT.ADD.ERROR_ADD'));
				},
				complete: function () {
					$state.go('alert');
				}
			});
		};

		var fixmeTop = $('.summary-fix').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixmeTop) {
				$('.summary-fix').css({
					position: 'fixed',
					top: '0',
					width: '15%'
				});
			} else {
				$('.summary-fix').css({
					position: 'static',
					width: ''
				});
			}
		});

		var fixMeMobile = $('.mobile-side-panel-menu').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixMeMobile) {
				$('.mobile-side-panel-menu').css({
					position: 'fixed',
					top: '50px',
					width: '100%',
					zIndex: '100',
					background: '#6f5499',
					boxShadow: 'rgba(93, 93, 93, 0.6) 0px 3px 8px -3px'

				});
				$('.mobile-summary .summary-title').css({
					color: 'white'
				});
			} else {
				$('.mobile-side-panel-menu').css({
					position: 'static',
					width: '',
					background: '',
					boxShadow: ''
				});
				$('.mobile-summary .summary-title').css({
					color: '#6f5499'
				});
			}
		});
	});
