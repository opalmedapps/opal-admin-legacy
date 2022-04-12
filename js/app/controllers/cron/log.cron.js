angular.module('opalAdmin.controllers.cron.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the cron logs
 *******************************************************************************/
controller('cron.log', function ($scope, $filter, $uibModalInstance, cronCollectionService, Session, ErrorHandler) {

	// Call our API to get cron logs based on highlighted section
	cronCollectionService.getSelectedCronListLogs($scope.contentNames, Session.retrieveObject('user').id).then(function (response) {
		$scope.cronListLogs = response.data;

		if ($scope.cronListLogs.appointment || $scope.cronListLogs.document || $scope.cronListLogs.task) {
			// Table options for alias logs
			var gridAliasLogOptions = {
				data: '',
				columnDefs: [
					{ field: 'expression_name', displayName: $filter('translate')('CRON.DETAILS.CLINICAL_CODE'), enableColumnMenu: false } ,
					{ field: 'expression_description', displayName: $filter('translate')('CRON.DETAILS.RESOURCE'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'source_db', displayName: $filter('translate')('CRON.DETAILS.DATABASE'), enableColumnMenu: false } ,
					{ field: 'source_uid', displayName: $filter('translate')('CRON.DETAILS.CLINICAL_UID'), enableColumnMenu: false } ,
					{ field: 'read_status', displayName: $filter('translate')('CRON.DETAILS.READ_STATUS'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false }
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true
			};
		}

		if ($scope.cronListLogs.appointment && $scope.cronListLogs.appointment.length) {
			$scope.gridAppointmentLogOptions = angular.copy(gridAliasLogOptions);
			$scope.gridAppointmentLogOptions.data = $scope.cronListLogs.appointment;
			// insert fields after source uid
			var statusField = { field: 'status', displayName: $filter('translate')('CRON.DETAILS.STATUS'), enableColumnMenu: false } ;
			var stateField = { field: 'state', displayName: $filter('translate')('CRON.DETAILS.STATE'), enableColumnMenu: false } ;
			var scheduledStartField = { field: 'scheduled_start', displayName: $filter('translate')('CRON.DETAILS.SCHEDULED_START'), enableColumnMenu: false } ;
			var scheduledEndField = { field: 'scheduled_end', displayName: $filter('translate')('CRON.DETAILS.SCHEDULED_END'), enableColumnMenu: false } ;
			var actualStartField = { field: 'actual_start', displayName: $filter('translate')('CRON.DETAILS.ACTUAL_START'), enableColumnMenu: false } ;
			var actualEndField = { field: 'actual_end', displayName: $filter('translate')('CRON.DETAILS.ACTUAL_END'), enableColumnMenu: false } ;
			var roomENField = { field: 'room_EN', displayName: $filter('translate')('CRON.DETAILS.ROOM_LOCATION_EN'), enableColumnMenu: false } ;
			var roomFRField = { field: 'room_FR', displayName: $filter('translate')('CRON.DETAILS.ROOM_LOCATION_FR'), enableColumnMenu: false } ;
			var checkinField = { field: 'checkin', displayName: $filter('translate')('CRON.DETAILS.CHECKIN'), enableColumnMenu: false } ;

			$scope.gridAppointmentLogOptions.columnDefs.splice(6, 0, statusField, stateField, scheduledStartField,
				scheduledEndField, actualStartField, actualEndField, roomENField, roomFRField, checkinField);
		}

		if ($scope.cronListLogs.document && $scope.cronListLogs.document.length) {
			$scope.gridDocumentLogOptions = angular.copy(gridAliasLogOptions);
			$scope.gridDocumentLogOptions.data = $scope.cronListLogs.document;
			// insert fields after source uid
			var createdByField = { field: 'created_by', displayName: $filter('translate')('CRON.DETAILS.CREATED_BY'), enableColumnMenu: false } ;
			var createdTimeField = {field: 'created_time', displayName: $filter('translate')('CRON.DETAILS.CREATED_TIME'), enableColumnMenu: false } ;
			var approvedByField = { field: 'approved_by', displayName: $filter('translate')('CRON.DETAILS.APPROVED_BY'), enableColumnMenu: false } ;
			var approvedTimeField = { field: 'approved_time', displayName: $filter('translate')('CRON.DETAILS.APPROVED_TIME'), enableColumnMenu: false } ;
			var authoredByField = { field: 'authored_by', displayName: $filter('translate')('CRON.DETAILS.AUTHORED_BY'), enableColumnMenu: false } ;
			var dateOfServiceField = { field: 'dateofservice', displayName: $filter('translate')('CRON.DETAILS.DATE_OF_SERVICE'), enableColumnMenu: false } ;
			var revisedField = { field: 'revised', displayName: $filter('translate')('CRON.DETAILS.REVISED'), enableColumnMenu: false } ;
			var validEntryField = { field: 'valid', displayName: $filter('translate')('CRON.DETAILS.VALID'), enableColumnMenu: false } ;
			var origFileField = { field: 'original_file', displayName: $filter('translate')('CRON.DETAILS.ORIGINAL_FILE'), enableColumnMenu: false } ;
			var finalFileField = { field: 'final_file', displayName: $filter('translate')('CRON.DETAILS.FINAL_FILE'), enableColumnMenu: false } ;
			var transferStatusField = {field: 'transfer', displayName: $filter('translate')('CRON.DETAILS.TRANSFER_STATUS'), enableColumnMenu: false } ;
			var transferLogField = { field: 'transfer_log', displayName: $filter('translate')('CRON.DETAILS.TRANSFER_LOG'), enableColumnMenu: false } ;

			$scope.gridDocumentLogOptions.columnDefs.splice(6, 0, createdByField, createdTimeField, approvedByField,
				approvedTimeField, authoredByField, dateOfServiceField, revisedField, validEntryField,
				origFileField, finalFileField, transferStatusField, transferLogField);
		}

		if ($scope.cronListLogs.task && $scope.cronListLogs.task.length) {
			$scope.gridTaskLogOptions = angular.copy(gridAliasLogOptions);
			$scope.gridTaskLogOptions.data = $scope.cronListLogs.task;
			// insert fields after source uid
			var statusField = { field: 'status', displayName: $filter('translate')('CRON.DETAILS.STATUS'), enableColumnMenu: false } ;
			var stateField = { field: 'state', displayName: $filter('translate')('CRON.DETAILS.STATE'), enableColumnMenu: false } ;
			var dueDateField = { field: 'due_date', displayName: $filter('translate')('CRON.DETAILS.DUE_DATE'), enableColumnMenu: false } ;
			var creationField = { field: 'creation', displayName: $filter('translate')('CRON.DETAILS.CREATION_DATE'), enableColumnMenu: false } ;
			var completedField = { field: 'completed', displayName: $filter('translate')('CRON.DETAILS.COMPLETED_DATE'), enableColumnMenu: false } ;

			$scope.gridTaskLogOptions.columnDefs.splice(6, 0, statusField, stateField, dueDateField,
				creationField, completedField);
		}

		if ($scope.cronListLogs.announcement && $scope.cronListLogs.announcement.length) {
			$scope.gridAnnouncementLogOptions = {
				data: $scope.cronListLogs.announcement,
				columnDefs: [
					{ field: 'post_control_name', displayName: $filter('translate')('CRON.DETAILS.POST'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'read_status', displayName: $filter('translate')('CRON.DETAILS.READ_STATUS'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true
			};
		}

		if ($scope.cronListLogs.txTeamMessage && $scope.cronListLogs.txTeamMessage.length) {
			$scope.gridTxTeamMessageLogOptions = {
				data: $scope.cronListLogs.txTeamMessage,
				columnDefs: [
					{ field: 'post_control_name', displayName: $filter('translate')('CRON.DETAILS.POST'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'read_status', displayName: $filter('translate')('CRON.DETAILS.READ_STATUS'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true
			};
		}

		if ($scope.cronListLogs.educationalMaterial && $scope.cronListLogs.educationalMaterial.length) {
			$scope.gridEducationalMaterialLogOptions = {
				data: $scope.cronListLogs.educationalMaterial,
				columnDefs: [
					{ field: 'material_name', displayName: $filter('translate')('CRON.DETAILS.NAME'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'read_status', displayName: $filter('translate')('CRON.DETAILS.READ_STATUS'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.cronListLogs.email && $scope.cronListLogs.email.length) {
			$scope.gridEmailLogOptions = {
				data: $scope.cronListLogs.email,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('CRON.DETAILS.CONTROLSER'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'type', displayName: $filter('translate')('CRON.DETAILS.EMAIL_TYPE'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.cronListLogs.legacyQuestionnaire && $scope.cronListLogs.legacyQuestionnaire.length) {
			$scope.gridLegacyQuestionnaireLogOptions = {
				data: $scope.cronListLogs.legacyQuestionnaire,
				columnDefs: [
					{ field: 'control_name', displayName: $filter('translate')('CRON.DETAILS.QUESTIONNAIRE'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'pt_questionnaire_db', displayName: $filter('translate')('CRON.DETAILS.PATIENTQUESTIONNAIREDBSER'), enableColumnMenu: false } ,
					{ field: 'completed', displayName: $filter('translate')('CRON.DETAILS.COMPLETED'), enableColumnMenu: false } ,
					{ field: 'completion_date', displayName: $filter('translate')('CRON.DETAILS.PATIENTQUESTIONNAIREDBSER'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.COMPLETED_DATE'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.cronListLogs.notification && $scope.cronListLogs.notification.length) {
			$scope.gridNotificationLogOptions = {
				data: $scope.cronListLogs.notification,
				columnDefs: [
					{ field: 'control_serial', displayName: $filter('translate')('CRON.DETAILS.CONTROLSER'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'type', displayName: $filter('translate')('CRON.DETAILS.NOTIFICATION'), enableColumnMenu: false } ,
					{ field: 'ref_table_serial', displayName: $filter('translate')('CRON.DETAILS.REF_TABLE_SER'), enableColumnMenu: false } ,
					{ field: 'read_status', displayName: $filter('translate')('CRON.DETAILS.READ_STATUS'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true,
			};
		}

		if ($scope.cronListLogs.testResult && $scope.cronListLogs.testResult.length) {
			$scope.gridTestResultLogOptions = {
				data: $scope.cronListLogs.testResult,
				columnDefs: [
					{ field: 'expression_name', displayName: $filter('translate')('CRON.DETAILS.TEST_NAME'), enableColumnMenu: false } ,
					{ field: 'revision', displayName: $filter('translate')('CRON.DETAILS.REVISION_NO'), enableColumnMenu: false } ,
					{ field: 'cron_serial', displayName: $filter('translate')('CRON.DETAILS.CRONLOGSER'), enableColumnMenu: false } ,
					{ field: 'patient_serial', displayName: $filter('translate')('CRON.DETAILS.PATIENTSER'), enableColumnMenu: false } ,
					{ field: 'source_db', displayName: $filter('translate')('CRON.DETAILS.DATABASE'), enableColumnMenu: false } ,
					{ field: 'source_uid', displayName: $filter('translate')('CRON.DETAILS.CLINICAL_UID'), enableColumnMenu: false } ,
					{ field: 'abnormal_flag', displayName: $filter('translate')('CRON.DETAILS.ABNORMAL_FLAG'), enableColumnMenu: false } ,
					{ field: 'test_date', displayName: $filter('translate')('CRON.DETAILS.TEST_DATE'), enableColumnMenu: false } ,
					{ field: 'max_norm', displayName: $filter('translate')('CRON.DETAILS.MAX_NORM'), enableColumnMenu: false } ,
					{ field: 'min_norm', displayName: $filter('translate')('CRON.DETAILS.MIN_NORM'), enableColumnMenu: false } ,
					{ field: 'test_value', displayName: $filter('translate')('CRON.DETAILS.TEST_VALUE'), enableColumnMenu: false } ,
					{ field: 'unit', displayName: $filter('translate')('CRON.DETAILS.UNIT'), enableColumnMenu: false } ,
					{ field: 'valid', displayName: $filter('translate')('CRON.DETAILS.VALID'), enableColumnMenu: false } ,
					{ field: 'read_status', displayName: $filter('translate')('CRON.DETAILS.READ_STATUS'), enableColumnMenu: false } ,
					{ field: 'date_added', displayName: $filter('translate')('CRON.DETAILS.DATETIME_SENT'), enableColumnMenu: false } ,
					{ field: 'mod_action', displayName: $filter('translate')('CRON.DETAILS.ACTION'), enableColumnMenu: false } 
				],
				rowHeight: 30,
				useExternalFiltering: true,
				enableColumnResizing: true,
			};
		}
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('RON.DETAILS.ERROR_CRON_LOGS'));
		$uibModalInstance.dismiss('cancel');
	});


	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};


});