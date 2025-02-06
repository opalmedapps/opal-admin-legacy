// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.user.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the user logs
 *******************************************************************************/
controller('user.log', function ($scope, $uibModal, $filter, userCollectionService, Session, $uibModalInstance, ErrorHandler) {

	$scope.userListLogs = [];

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

	// Call our API to get user logs
	userCollectionService.getUserActivityLogs($scope.currentUser.serial).then(function (response) {
		$scope.userListLogs = response.data;
		if ($scope.userListLogs.login.length) {
			$scope.gridLoginLogOptions = {
				data: $scope.userListLogs.login,
				columnDefs: [
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'login', displayName: $filter('translate')('USERS.LOG.LOGIN'), enableColumnMenu: false },
					{ field: 'logout', displayName: $filter('translate')('USERS.LOG.LOGOUT'), enableColumnMenu: false },
					{ field: 'session_duration', displayName: $filter('translate')('USERS.LOG.DURATION'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.alias.length) {
			$scope.gridAliasLogOptions = {
				data: $scope.userListLogs.alias,
				columnDefs: [
					{ field: 'serial', displayName: $filter('translate')('USERS.LOG.SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'type', displayName: $filter('translate')('USERS.LOG.TYPE'), enableColumnMenu: false },
					{ field: 'update', displayName: $filter('translate')('USERS.LOG.UPDATE_FLAG'), enableColumnMenu: false },
					{ field: 'name_EN', displayName: $filter('translate')('USERS.LOG.NAME_EN'), enableColumnMenu: false },
					{ field: 'name_FR', displayName: $filter('translate')('USERS.LOG.NAME_FR'), enableColumnMenu: false },
					{ field: 'description_EN', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_EN'), enableColumnMenu: false },
					{ field: 'description_FR', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_FR'), enableColumnMenu: false },
					{ field: 'educational_material', displayName: $filter('translate')('USERS.LOG.ED_SER'), enableColumnMenu: false },
					{ field: 'source_db', displayName: $filter('translate')('USERS.LOG.DATABASE'), enableColumnMenu: false },
					{ field: 'color', displayName: $filter('translate')('USERS.LOG.COLOR'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.aliasExpression.length) {
			$scope.gridAliasExpressionLogOptions = {
				data: $scope.userListLogs.aliasExpression,
				columnDefs: [
					{ field: 'serial', displayName: $filter('translate')('USERS.LOG.ALIAS_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'expression', displayName: $filter('translate')('USERS.LOG.CLINICAL_CODE'), enableColumnMenu: false },
					{ field: 'resource_description', displayName: $filter('translate')('USERS.LOG.RESOURCE'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.diagnosisTranslation.length) {
			$scope.gridDiagnosisTranslationLogOptions = {
				data: $scope.userListLogs.diagnosisTranslation,
				columnDefs: [
					{ field: 'serial', displayName: $filter('translate')('USERS.LOG.DIAG_TRANS_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'educational_material', displayName: $filter('translate')('USERS.LOG.ED_SER'), enableColumnMenu: false },
					{ field: 'name_EN', displayName: $filter('translate')('USERS.LOG.NAME_EN'), enableColumnMenu: false },
					{ field: 'name_FR', displayName: $filter('translate')('USERS.LOG.NAME_FR'), enableColumnMenu: false },
					{ field: 'description_EN', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_EN'), enableColumnMenu: false },
					{ field: 'description_FR', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_FR'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.diagnosisCode.length) {
			$scope.gridDiagnosisCodeLogOptions = {
				data: $scope.userListLogs.diagnosisCode,
				columnDefs: [
					{ field: 'serial', displayName: $filter('translate')('USERS.LOG.DIAG_TRANS_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'sourceuid', displayName: $filter('translate')('USERS.LOG.SOURCE_UID'), enableColumnMenu: false },
					{ field: 'code', displayName: $filter('translate')('USERS.LOG.DIAGNOSIS_CODE'), enableColumnMenu: false },
					{ field: 'description', displayName: $filter('translate')('USERS.LOG.DESCRIPTION'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.email.length) {
			$scope.gridEmailLogOptions = {
				data: $scope.userListLogs.email,
				columnDefs: [
					{ field: 'serial', displayName: $filter('translate')('USERS.LOG.EMAIL_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'subject_EN', displayName: $filter('translate')('USERS.LOG.SUBJECT_EN'), enableColumnMenu: false },
					{ field: 'subject_FR', displayName: $filter('translate')('USERS.LOG.SUBJECT_FR'), enableColumnMenu: false },
					{ field: 'body_EN', displayName: $filter('translate')('USERS.LOG.BODY_EN'), enableColumnMenu: false },
					{ field: 'body_FR', displayName: $filter('translate')('USERS.LOG.BODY_FR'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.trigger.length) {
			$scope.gridTriggerLogOptions = {
				data: $scope.userListLogs.trigger,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('USERS.LOG.CONTROL_SERIAL'), enableColumnMenu: false },
					{ field: 'control_table', displayName: $filter('translate')('USERS.LOG.CONTROL_TABLE'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'type', displayName: $filter('translate')('USERS.LOG.TRIGGER_TYPE'), enableColumnMenu: false },
					{ field: 'filterid', displayName: $filter('translate')('USERS.LOG.FILTER_ID'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.hospitalMap.length) {
			$scope.gridHospitalMapLogOptions = {
				data: $scope.userListLogs.hospitalMap,
				columnDefs: [
					{ field: 'serial', displayName: $filter('translate')('USERS.LOG.SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'url', displayName: $filter('translate')('USERS.LOG.MAP_URL'), enableColumnMenu: false },
					{ field: 'qrcode', displayName: $filter('translate')('USERS.LOG.QR_ID'), enableColumnMenu: false },
					{ field: 'name_EN', displayName: $filter('translate')('USERS.LOG.NAME_EN'), enableColumnMenu: false },
					{ field: 'name_FR', displayName: $filter('translate')('USERS.LOG.NAME_FR'), enableColumnMenu: false },
					{ field: 'description_EN', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_EN'), enableColumnMenu: false },
					{ field: 'description_FR', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_FR'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.post.length) {
			$scope.gridPostLogOptions = {
				data: $scope.userListLogs.post,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('USERS.LOG.CONTROL_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'type', displayName: $filter('translate')('USERS.LOG.POST_TYPE'), enableColumnMenu: false },
					{ field: 'publish', displayName: $filter('translate')('USERS.LOG.PUBLISH_FLAG'), enableColumnMenu: false },
					{ field: 'disabled', displayName: $filter('translate')('USERS.LOG.DISABLED_FLAG'), enableColumnMenu: false },
					{ field: 'publish_date', displayName: $filter('translate')('USERS.LOG.PUBLISH_DATE'), enableColumnMenu: false },
					{ field: 'name_EN', displayName: $filter('translate')('USERS.LOG.NAME_EN'), enableColumnMenu: false },
					{ field: 'name_FR', displayName: $filter('translate')('USERS.LOG.NAME_FR'), enableColumnMenu: false },
					{ field: 'body_EN', displayName: $filter('translate')('USERS.LOG.BODY_EN'), enableColumnMenu: false },
					{ field: 'body_FR', displayName: $filter('translate')('USERS.LOG.BODY_FR'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.notification.length) {
			$scope.gridNotificationLogOptions = {
				data: $scope.userListLogs.notification,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('USERS.LOG.CONTROL_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'type', displayName: $filter('translate')('USERS.LOG.NOTIFICATION_TYPE'), enableColumnMenu: false },
					{ field: 'name_EN', displayName: $filter('translate')('USERS.LOG.NAME_EN'), enableColumnMenu: false },
					{ field: 'name_FR', displayName: $filter('translate')('USERS.LOG.NAME_FR'), enableColumnMenu: false },
					{ field: 'description_EN', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_EN'), enableColumnMenu: false },
					{ field: 'description_FR', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_FR'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.testResult.length) {
			$scope.gridTestResultLogOptions = {
				data: $scope.userListLogs.testResult,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('USERS.LOG.CONTROL_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'source_db', displayName: $filter('translate')('USERS.LOG.SOURCE_DB'), enableColumnMenu: false },
					{ field: 'educational_material', displayName: $filter('translate')('USERS.LOG.ED_SER'), enableColumnMenu: false },
					{ field: 'name_EN', displayName: $filter('translate')('USERS.LOG.NAME_EN'), enableColumnMenu: false },
					{ field: 'name_FR', displayName: $filter('translate')('USERS.LOG.NAME_FR'), enableColumnMenu: false },
					{ field: 'description_EN', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_EN'), enableColumnMenu: false },
					{ field: 'description_FR', displayName: $filter('translate')('USERS.LOG.DESCRIPTION_FR'), enableColumnMenu: false },
					{ field: 'group_EN', displayName: $filter('translate')('USERS.LOG.GROUP_EN'), enableColumnMenu: false },
					{ field: 'group_FR', displayName: $filter('translate')('USERS.LOG.GROUP_FR'), enableColumnMenu: false },
					{ field: 'publish', displayName: $filter('translate')('USERS.LOG.PUBLISH_FLAG'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.userListLogs.testResultExpression.length) {
			$scope.gridTestResultExpressionLogOptions = {
				data: $scope.userListLogs.testResultExpression,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('USERS.LOG.CONTROL_SERIAL'), enableColumnMenu: false },
					{ field: 'revision', displayName: $filter('translate')('USERS.LOG.REVISION_NO'), enableColumnMenu: false },
					{ field: 'sessionid', displayName: $filter('translate')('USERS.LOG.SESSION_ID'), enableColumnMenu: false },
					{ field: 'expression', displayName: $filter('translate')('USERS.LOG.TEST_NAME'), enableColumnMenu: false },
					{ field: 'mod_action', displayName: $filter('translate')('USERS.LOG.ACTION'), enableColumnMenu: false },
					{ field: 'date_added', displayName: $filter('translate')('USERS.LOG.DATETIME_MODIFIED'), enableColumnMenu: false }
				],
				enableFiltering: true,
				enableColumnResizing: true,
			};
		}

		processingModal.close(); // hide modal
		processingModal = null; // remove reference

	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('USERS.LOG.ERROR_LOGS'));
		$uibModalInstance.dismiss('cancel');

	});

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});