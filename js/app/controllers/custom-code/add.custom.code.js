angular.module('opalAdmin.controllers.customCode.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Add Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('customCode.add', function ($scope, $filter, $uibModal, customCodeCollectionService, $state, Session, ErrorHandler) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.showAssigned = false;
		$scope.hideAssigned = false;
		$scope.language = Session.retrieveObject('user').language;

		$scope.toSubmit = {
			OAUserId: OAUserId,
			sessionid: Session.retrieveObject('user').sessionid,
			moduleId: {
				value: null,
			},
			details: {
				code: null,
				description: null,
			},
		};

		$scope.validator = {
			moduleId: {
				completed: false,
				mandatory: true,
			},
			details: {
				completed: false,
				mandatory: true,
			},
		};

		$scope.leftMenu = {
			moduleId: {
				display: true,
				open: false,
				preview: false,
			},
			details: {
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

		// Call our API to ge the list of diagnoses
		customCodeCollectionService.getAvailableModules(OAUserId).then(function (response) {
			response.data.forEach(function(entry) {
				if($scope.language.toUpperCase() === "FR")
					entry.name_display = entry.name_FR;
				else
					entry.name_display = entry.name_EN;

				entry.subModule = JSON.parse(entry.subModule);

				if (entry.subModule) {
					angular.forEach(entry.subModule, function (sub) {
						if ($scope.language.toUpperCase() === "FR")
							sub.name_display = sub.name_FR;
						else
							sub.name_display = sub.name_EN;
					});
				}
			});
			$scope.moduleList = response.data; // Assign value
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('CUSTOM_CODE.ADD.ERROR_MODULE'));
			$state.go('custom-code');
		});

		$scope.moduleUpdate = function (moduleSelected) {
			if(moduleSelected.subModule) {
				$scope.aliasTypes = moduleSelected.subModule;

				if ($scope.toSubmit.type != "undefined") {
					$scope.toSubmit.type = {
						ID: null,
					};
				}
				if ($scope.validator.type != "undefined") {
					$scope.validator.type = {
						completed: false,
						mandatory: true,
					};
				}
				if ($scope.leftMenu.type != "undefined") {
					$scope.leftMenu.type = {
						display: true,
						open: false,
						preview: false,
					};
				}
			} else {
				$scope.aliasTypes = null;
				delete $scope.toSubmit.type;
				delete $scope.validator.type;
				delete $scope.leftMenu.type;

			}
			$scope.toSubmit.moduleId.value = moduleSelected.ID;
			$scope.validator.moduleId.completed = true;
			$scope.leftMenu.moduleId.preview = moduleSelected.name_display;
			$scope.leftMenu.moduleId.open = true;
		};

		$scope.typeUpdate = function (typeSelected) {
			$scope.toSubmit.type.ID = typeSelected.ID;

			$scope.leftMenu.type.preview = typeSelected.name_display;
			$scope.leftMenu.type.open = true;
			$scope.validator.type.completed = true;
		}

		$scope.detailsUpdate = function () {
			$scope.validator.details.completed = ($scope.toSubmit.details.code != null && $scope.toSubmit.details.description != null);
			$scope.leftMenu.details.open = ($scope.toSubmit.details.code != null || $scope.toSubmit.details.description != null);
			$scope.leftMenu.details.display = ($scope.toSubmit.details.code != null || $scope.toSubmit.details.description != null);
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
				else if(!value.mandatory && value.completed)
					nonMandatoryCompleted++;
			});

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
		}, true);

		// Function to submit the new diagnosis translation
		$scope.submitCustomCode = function () {
			$.ajax({
				type: 'POST',
				url: 'custom-code/insert/custom-code',
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('CUSTOM_CODE.ADD.ERROR_ADD'));
				},
				complete: function () {
					$state.go('custom-code');
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
