angular.module('opalAdmin.controllers.masterSourceAlias.add', ['ngAnimate', 'ui.bootstrap']).
controller('masterSourceAlias.add', function ($scope, $filter, $uibModal, masterSourceCollectionService, $state, Session, ErrorHandler) {

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	$scope.aliasTypes = [
		{
			ID: "1",
			iconClass: "th-list",
			name_display: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.TASK')
		},
		{
			ID: "2",
			iconClass: "calendar",
			name_display: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.APPOINTMENT')

		},
		{
			ID: "3",
			iconClass: "folder-open",
			name_display: $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.DOCUMENT')
		},
	];

	// Function to go to previous page
	$scope.goBack = function () {
		window.history.back();
	};

	var arrValidationInsert = [
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_EXTERNAL_ID'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_CODE'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_DESCRIPTION'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_DATE'),
	];

	var arrValidationExists = [
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATION_EXTERNAL_ID')
	];

	$scope.showAssigned = false;
	$scope.hideAssigned = false;
	$scope.language = Session.retrieveObject('user').language;

	$scope.toSubmit = {
		sourceDatabaseId: {
			value: null,
		},
		type: {
			value: null,
		},
		externalId: {
			value: null,
		},
		details: {
			code: null,
			description: null,
		},
	};

	$scope.validator = {
		sourceDatabaseId: {
			completed: false,
			mandatory: true,
		},
		type: {
			completed: false,
			mandatory: true,
		},
		externalId: {
			completed: false,
			mandatory: true,
		},
		details: {
			completed: false,
			mandatory: true,
		},
	};

	$scope.leftMenu = {
		sourceDatabaseId: {
			display: true,
			open: false,
			preview: false,
		},
		type: {
			display: false,
			open: false,
			preview: false,
		},
		externalId: {
			display: false,
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

	// Call our API to ge the list of aliases
	masterSourceCollectionService.getExternalSourceDatabase().then(function (response) {
		$scope.dbList = response.data; // Assign value
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.ERROR_SOURCE'));
		$state.go('master-source/alias');
	});

	$scope.sourceUpdate = function (sourceSelected) {
		$scope.toSubmit.sourceDatabaseId.value = sourceSelected.ID;
		$scope.validator.sourceDatabaseId.completed = true;
		$scope.leftMenu.sourceDatabaseId.preview = sourceSelected.name;
		$scope.leftMenu.sourceDatabaseId.open = true;
	};

	$scope.detailsUpdate = function () {
		$scope.validator.details.completed = ($scope.toSubmit.details.code != null && $scope.toSubmit.details.description != null);
		$scope.leftMenu.details.open = ($scope.toSubmit.details.code != null || $scope.toSubmit.details.description != null);
		$scope.leftMenu.details.display = ($scope.toSubmit.details.code != null || $scope.toSubmit.details.description != null);
	};

	$scope.typeUpdate = function (typeSelected) {
		$scope.toSubmit.type.value = typeSelected.ID;
		$scope.validator.type.completed = true;
		$scope.leftMenu.type.preview = typeSelected.name_display;
		$scope.leftMenu.type.open = $scope.validator.type.completed;
		$scope.leftMenu.type.display = $scope.validator.type.completed;
	};

	$scope.externalIdUpdate = function () {
		$scope.validator.externalId.completed = $scope.toSubmit.externalId.value != null && !isNaN($scope.toSubmit.externalId.value);
		$scope.leftMenu.externalId.open = $scope.validator.externalId.completed;
		$scope.leftMenu.externalId.display = $scope.validator.externalId.completed;
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

	// Function to submit the new alias translation
	$scope.submitCustomCode = function () {
		var ready = {};
		ready[0] = {
			"type": $scope.toSubmit.type.value,
			"externalId": $scope.toSubmit.externalId.value,
			"source": $scope.toSubmit.sourceDatabaseId.value,
			"code": $scope.toSubmit.details.code,
			"description": $scope.toSubmit.details.description
		};

		masterSourceCollectionService.isMasterSourceAliasExists(ready[0].source, ready[0].externalId, ready[0].type, ready[0].code, ready[0].description).then(function (response) {
			var resultServer = response.data;
			if(response.data.code !== undefined && response.data.deleted == 0) {
				alert($filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.ALREADY_EXISTS'));
			}
			else
				submitAliasAjax(ready);
		}).catch(function(err) {
			err.responseText = err.data;
			ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.VALIDATE_ERROR'), arrValidationExists);
			$state.go('master-source/alias');
		});
	};

	function submitAliasAjax(ready) {

		console.log(ready);

		// $.ajax({
		// 	type: 'POST',
		// 	url: 'master-source/insert/aliases',
		// 	data: ready,
		// 	success: function () {},
		// 	error: function (err) {
		// 		err.responseText = JSON.parse(err.responseText)[0];
		// 		ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_ADD.ERROR_ADD'), arrValidationInsert);
		// 	},
		// 	complete: function () {
		// 		$state.go('master-source/alias');
		// 	}
		// });
	}

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
