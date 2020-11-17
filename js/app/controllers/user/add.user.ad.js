angular.module('opalAdmin.controllers.user.add.ad', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	 * Controller for user registration
	 *******************************************************************************/
	controller('user.add.ad', function ($scope, userCollectionService, $state, $filter, Session, ErrorHandler) {
		var OAUserId = Session.retrieveObject('user').id;

		$scope.userType = [
			{
				ID: "1",
				name_display: $filter('translate')('USERS.ADD.HUMAN')
			},
			{
				ID: "2",
				name_display: $filter('translate')('USERS.ADD.SYSTEM')
			},
		];

		$scope.toSubmit = {
			username: {
				value: "",
			},
			type: {
				value: $scope.userType[0].ID,
			},
			role: {
				value: null,
			},
			language: {
				value: null,
			}
		};

		$scope.validator = {
			username: {
				completed: false,
				valid: true,
				mandatory: true,
			},
			type: {
				completed: true,
				valid: true,
				mandatory: true,
			},
			role: {
				completed: false,
				valid: true,
				mandatory: true,
			},
			language: {
				completed: false,
				valid: true,
				mandatory: true,
			},
		};

		$scope.leftMenu = {
			username: {
				display: true,
				open: false,
				preview: false,
			},
			type: {
				display: false,
				open: true,
				preview: false,
				name: $scope.userType[0].name_display,
			},
			role: {
				display: false,
				open: false,
				preview: false,
			},
			language: {
				display: false,
				open: false,
				preview: false,
				name: null,
			},
		};

		$scope.totalSteps = 0;
		$scope.completedSteps = 0;
		$scope.formReady = false;

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
				if(value.mandatory && value.completed && value.valid)
					completedSteps++;
				else if(!value.mandatory && value.completed && value.valid)
					nonMandatoryCompleted++;
			});

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
		}, true);

		$scope.$watch('toSubmit.username.value', function() {
			$scope.validator.username.completed = !!($scope.toSubmit.username.value && $scope.toSubmit.username.value.length > 0);
			$scope.validator.username.valid = $scope.validator.username.completed;

			$scope.leftMenu.username.display = !!($scope.toSubmit.username.value && $scope.toSubmit.username.value.length > 0);
			$scope.leftMenu.username.open = !!($scope.toSubmit.username.value && $scope.toSubmit.username.value.length > 0);
			$scope.leftMenu.username.preview = !!($scope.toSubmit.username.value && $scope.toSubmit.username.value.length > 0);

		});

		$scope.$watch('toSubmit.role.value', function() {
			$scope.validator.role.completed = !!$scope.toSubmit.role.value;

			$scope.leftMenu.role.display = $scope.validator.role.completed;
			$scope.leftMenu.role.open = $scope.validator.role.completed;
			$scope.leftMenu.role.preview = $scope.validator.role.completed;
		});

		$scope.$watch('toSubmit.password', function() {
			if ($scope.toSubmit.type.value == "2") {
				var validationPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{8,}$/;
				$scope.validator.password.valid = !!$scope.toSubmit.password.value.match(validationPassword);
				$scope.validator.password.completed = ($scope.validator.password.valid && $scope.toSubmit.password.value == $scope.toSubmit.password.confirm);

				if($scope.toSubmit.password.value !== undefined && $scope.toSubmit.password.value.length > 0) {
					$scope.leftMenu.password.display = true;
					$scope.leftMenu.password.open = true;
				}

				$scope.leftMenu.password.preview = $scope.validator.password.completed;
			}
		}, true);

		$scope.$watch('toSubmit.language.value', function() {
			$scope.validator.language.completed = !!$scope.toSubmit.language.value;

			$scope.leftMenu.language.display = $scope.validator.language.completed;
			$scope.leftMenu.language.open = $scope.validator.language.completed;
			$scope.leftMenu.language.preview = $scope.validator.language.completed;

			$scope.leftMenu.language.name = ($scope.toSubmit.language.value === "FR"?$filter('translate')('USERS.ADD.FRENCH'):$filter('translate')('USERS.ADD.ENGLISH'));
		});

		$scope.$watch('toSubmit.type.value', function() {
			if($scope.toSubmit.type.value == "1") {
				delete $scope.toSubmit.password;
				delete $scope.validator.password;
				delete $scope.leftMenu.password;
				$scope.leftMenu.name = $filter('translate')('USERS.ADD.HUMAN');
			} else {
				$scope.toSubmit.password = {
					value: "",
					confirm: "",
				};
				$scope.validator.password = {
					completed: false,
					valid: true,
					mandatory: true,
				};
				$scope.leftMenu.password = {
					display: false,
					open: false,
					preview: false,
				};
				alert($filter('translate')('USERS.ADD.WARNING_USER'));
				$scope.leftMenu.name = $filter('translate')('USERS.ADD.SYSTEM');
			}
			$scope.leftMenu.type.display = true;
			$scope.leftMenu.type.open = true;
			$scope.leftMenu.type.preview = true;
		});

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// default booleans
		$scope.roleSection = {open:false, show:false};
		$scope.languageSection = {open:false, show:false};
		$scope.language = Session.retrieveObject('user').language;

		// Initialize a list of languages available
		$scope.languages = [{
			name: $filter('translate')('USERS.ADD.ENGLISH'),
			id: 'EN'
		}, {
			name: $filter('translate')('USERS.ADD.FRENCH'),
			id: 'FR'
		}];

		// Call our API service to get the list of possible roles
		$scope.roles = [];
		userCollectionService.getRoles().then(function (response) {
			response.data.forEach(function(row) {
				if($scope.language.toUpperCase() === "FR")
					row.name_display = row.name_FR;
				else
					row.name_display = row.name_EN;
			});
			$scope.roles = response.data;
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('USERS.ADD.ERROR_ROLES'));
		});

		// Function to validate username
		$scope.validateUsername = function () {

			if (!$scope.validator.username.completed) return;

			// Make request to check if username already in use
			userCollectionService.usernameAlreadyInUse($scope.toSubmit.username.value).then(function (response) {
				$scope.validator.username.valid = !response.data.count;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('USERS.ADD.ERROR_USERNAME_UNKNOWN'));
			});

		};

		// Function to register user
		$scope.registerUser = function () {
			var data = {
				username: $scope.toSubmit.username.value,
				type: $scope.toSubmit.type.value,
				language: $scope.toSubmit.language.value,
				roleId: $scope.toSubmit.role.value.ID,
			};

			if($scope.toSubmit.type.value == "2") {
				data.password = $scope.toSubmit.password.value;
				data.confirmPassword = $scope.toSubmit.password.confirm;
			}

			$.ajax({
				type: "POST",
				url: 'user/insert/user',
				data: data,
				success: function () {},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('USERS.ADD.ERROR'));
				},
				complete: function() {
					$state.go('users');
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

