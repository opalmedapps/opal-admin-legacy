angular.module('opalAdmin.controllers.role.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Add Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('role.add', function ($scope, $filter, $uibModal, $state, roleCollectionService, Session, ErrorHandler) {

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
			name: {
				name_EN: "",
				name_FR: "",
			},
			operations: []
		};

		$scope.newRole = {};

		$scope.validator = {
			name: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			operations: {
				completed: false,
				mandatory: true,
				valid: true,
			}
		};

		$scope.leftMenu = {
			name: {
				display: false,
				open: false,
			},
			operations: {
				display: false,
				open: false,
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

		// Call our API to ge the list of diagnoses
		roleCollectionService.getAvailableRoleModules(OAUserId).then(function (response) {
			var temp;
			response.data.forEach(function(entry) {
				if (parseInt(entry.operation) < 0)
					entry.operation = "0";
				if (parseInt(entry.operation) > 7)
					entry.operation = "7";

				temp = {
					"ID": entry.ID,
					canRead : ((parseInt(entry.operation) & (1 << 0)) !== 0),
					canWrite : ((parseInt(entry.operation) & (1 << 1)) !== 0),
					canDelete : ((parseInt(entry.operation) & (1 << 2)) !== 0),
					read : false,
					write : false,
					delete : false
				};

				if($scope.language.toUpperCase() === "FR")
					temp.name_display = entry.name_FR;
				else
					temp.name_display = entry.name_EN;

				$scope.toSubmit.operations.push(temp);
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('ROLE.ADD.ERROR_MODULE'));
			$state.go('role');
		});

		// Function to load form as animations
		$scope.loadForm = function () {
			$('.form-box-left').addClass('fadeInDown');
			$('.form-box-right').addClass('fadeInRight');
		};

		$scope.nameUpdate = function () {
			$scope.validator.name.completed = ($scope.toSubmit.name.name_EN !== "" && $scope.toSubmit.name.name_FR !== "");
			$scope.leftMenu.name.open = ($scope.toSubmit.name.name_EN !== "" || $scope.toSubmit.name.name_FR !== "");
			$scope.leftMenu.name.display = $scope.leftMenu.name.open;
		};

		$scope.$watch('toSubmit.operations', function(nv) {
			var atLeastOne = false;
			angular.forEach(nv, function(value) {
				if(value.read || value.write || value.delete)
					atLeastOne = true;
				if(value.write) {
					if(value.canRead) value.read = true;
				}
				if(value.delete) {
					if(value.canWrite) value.write = true;
					if(value.canRead) value.read = true;
				}
			});

			if(atLeastOne) {
				$scope.validator.operations.completed = true;
				if (!$scope.leftMenu.operations.open)
					$scope.leftMenu.operations.open = true;
			}
			else {
				$scope.validator.operations.completed = false;
				if ($scope.leftMenu.operations.open)
					$scope.leftMenu.operations.open = false;
			}

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

		function buildOperations() {
			$scope.newRole = JSON.parse(JSON.stringify($scope.toSubmit));
			var newSubmit = [];
			var noError = true;

			$scope.toSubmit.operations.forEach(function(entry) {

				sup = parseInt((+entry.delete + "" + +entry.write + "" + +entry.read), 2);
				if (sup !== 0 && sup !== 1 && sup !== 3 && sup !== 7)
					noError = false;

				if(sup !== 0) {
					newSubmit.push({"moduleId": entry.ID, "access": sup});
				}
			});
			$scope.newRole.operations = newSubmit;
			return noError;
		}

		// Function to submit the new diagnosis translation
		$scope.submitRole = function () {
			var validResult = buildOperations();
			if(validResult) {
				$.ajax({
					type: 'POST',
					url: 'role/insert/role',
					data: $scope.newRole,
					success: function () {},
					error: function (err) {
						ErrorHandler.onError(err, $filter('translate')('ROLE.ADD.ERROR_ADD'));
					},
					complete: function () {
						$state.go('role');
					}
				});
			}
			else
				alert($filter('translate')('ROLE.ADD.ERROR_INVALID_ROLE'));

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
