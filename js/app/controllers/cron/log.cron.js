angular.module('opalAdmin.controllers.cron.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


	/******************************************************************************
	* Controller for the cron logs
	*******************************************************************************/
	controller('cron.log', function ($scope, $uibModal, $filter, cronCollectionService, Session, $uibModalInstance) {

		// Call our API to get cron logs based on highlighted section
		cronCollectionService.getSelectedCronListLogs($scope.contentNames).then(function (response) {
			$scope.cronListLogs = response.data;
		
			if ($scope.cronListLogs.appointment || $scope.cronListLogs.document || $scope.cronListLogs.task) {
				// Table options for alias logs
				var gridAliasLogOptions = {
					data: '',
					columnDefs: [
						{ field: 'expression_name', displayName: 'Clinical Code' },
						{ field: 'expression_description', displayName: 'Resource Description'},
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'source_db', displayName: 'Database' },
						{ field: 'source_uid', displayName: 'Clinical UID' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true
				};
			}

			if ($scope.cronListLogs.appointment.length) {
				$scope.gridAppointmentLogOptions = angular.copy(gridAliasLogOptions);
				$scope.gridAppointmentLogOptions.data = $scope.cronListLogs.appointment;
				// insert fields after source uid
				var statusField = { field: 'status', displayName: 'Status' };
				var stateField = { field: 'state', displayName: 'State' };
				var scheduledStartField = { field: 'scheduled_start', displayName: 'Scheduled Start' };
				var scheduledEndField = { field: 'scheduled_end', displayName: 'Scheduled End' };
				var actualStartField = { field: 'actual_start', displayName: 'Actual Start' };
				var actualEndField = { field: 'actual_end', displayName: 'Actual End' };
				var roomENField = { field: 'room_EN', displayName: 'Room Location (EN)' };
				var roomFRField = { field: 'room_FR', displayName: 'Room Location (FR)' };
				var checkinField = { field: 'checkin', displayName: 'CheckIn' };

				$scope.gridAppointmentLogOptions.columnDefs.splice(6, 0, statusField, stateField, scheduledStartField,
					scheduledEndField, actualStartField, actualEndField, roomENField, roomFRField, checkinField);
			}

			if ($scope.cronListLogs.document.length) {
				$scope.gridDocumentLogOptions = angular.copy(gridAliasLogOptions);
				$scope.gridDocumentLogOptions.data = $scope.cronListLogs.document;
				// insert fields after source uid
				var createdByField = { field: 'created_by', displayName: 'Created By' };
				var createdTimeField = {field: 'created_time', displayName: 'Created Time' };
				var approvedByField = { field: 'approved_by', displayName: 'Approved By' };
				var approvedTimeField = { field: 'approved_time', displayName: 'Approved Time' };
				var authoredByField = { field: 'authored_by', displayName: 'Authored By' };
				var dateOfServiceField = { field: 'dateofservice', displayName: 'Date Of Service' };
				var revisedField = { field: 'revised', displayName: 'Revised' };
				var validEntryField = { field: 'valid', displayName: 'Valid' };
				var origFileField = { field: 'original_file', displayName: 'Original File' };
				var finalFileField = { field: 'final_file', displayName: 'Final File' };
				var transferStatusField = {field: 'transfer', displayName: 'Transfer Status' };
				var transferLogField = { field: 'transfer_log', displayName: 'Transfer Log' }; 

				$scope.gridDocumentLogOptions.columnDefs.splice(6, 0, createdByField, createdTimeField, approvedByField, 
					approvedTimeField, authoredByField, dateOfServiceField, revisedField, validEntryField,
					origFileField, finalFileField, transferStatusField, transferLogField);
			}

			if ($scope.cronListLogs.task.length) {
				$scope.gridTaskLogOptions = angular.copy(gridAliasLogOptions);
				$scope.gridTaskLogOptions.data = $scope.cronListLogs.task;
				// insert fields after source uid
				var statusField = { field: 'status', displayName: 'Status' };
				var stateField = { field: 'state', displayName: 'State' };
				var dueDateField = { field: 'due_date', displayName: 'Due Date' };
				var creationField = { field: 'creation', displayName: 'Creation Date' };
				var completedField = { field: 'completed', displayName: 'Completed Date' };

				$scope.gridTaskLogOptions.columnDefs.splice(6, 0, statusField, stateField, dueDateField,
					creationField, completedField);
			}

			if ($scope.cronListLogs.announcement.length) {
				$scope.gridAnnouncementLogOptions = {
					data: $scope.cronListLogs.announcement,
					columnDefs: [
						{ field: 'post_control_name', displayName: 'Post' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true
				};
			}

			if ($scope.cronListLogs.txTeamMessage.length) {
				$scope.gridTxTeamMessageLogOptions = {
					data: $scope.cronListLogs.txTeamMessage,
					columnDefs: [
						{ field: 'post_control_name', displayName: 'Post' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true
				};
			}

			if ($scope.cronListLogs.pfp.length) {
				$scope.gridPFPLogOptions = {
					data: $scope.cronListLogs.pfp,
					columnDefs: [
						{ field: 'post_control_name', displayName: 'Post' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true
				};
			}

			if ($scope.cronListLogs.educationalMaterial.length) {
				$scope.gridEducationalMaterialLogOptions = {
					data: $scope.cronListLogs.educationalMaterial,
					columnDefs: [
						{ field: 'material_name', displayName: 'Name' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true,
				};
			}

			if ($scope.cronListLogs.email.length) {
				$scope.gridEmailLogOptions = {
					data: $scope.cronListLogs.email,
					columnDefs: [
						{ field: 'control_serial', displayName: 'ControlSer' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'type', displayName: 'Email Type' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true,
				};
			}

			if ($scope.cronListLogs.legacyQuestionnaire.length) {
				$scope.gridLegacyQuestionnaireLogOptions = {
					data: $scope.cronListLogs.legacyQuestionnaire,
					columnDefs: [
						{ field: 'control_name', displayName: 'Questionnaire' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'pt_questionnaire_db', displayName: 'PatientQuestionnaireDBSer' },
						{ field: 'completed', displayName: 'Completed' },
						{ field: 'completion_date', displayName: 'Completion Date' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true,
				};
			}

			if ($scope.cronListLogs.notification.length) {
				$scope.gridNotificationLogOptions = {
					data: $scope.cronListLogs.notification,
					columnDefs: [
						{ field: 'control_serial', displayName: 'ControlSer' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'type', displayName: 'Notification Type' },
						{ field: 'ref_table_serial', displayName: 'Ref Table Ser' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true,
				};
			}

			if ($scope.cronListLogs.testResult.length) {
				$scope.gridTestResultLogOptions = {
					data: $scope.cronListLogs.testResult,
					columnDefs: [
						{ field: 'expression_name', displayName: 'Test Name' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'cron_serial', displayName: 'CronLogSer' },
						{ field: 'patient_serial', displayName: 'PatientSer' },
						{ field: 'source_db', displayName: 'Database' },
						{ field: 'source_uid', displayName: 'Clinical UID' },
						{ field: 'abnormal_flag', displayName: 'Abnormal Flag' },
						{ field: 'test_date', displayName: 'Test Date' },
						{ field: 'max_norm', displayName: 'Max Norm' },
						{ field: 'min_norm', displayName: 'Min Norm' },
						{ field: 'test_value', displayName: 'Test Value' },
						{ field: 'unit', displayName: 'Unit' },
						{ field: 'valid', displayName: 'Valid' },
						{ field: 'read_status', displayName: 'Read Status' },
						{ field: 'date_added', displayName: 'Datetime Sent' },
						{ field: 'mod_action', displayName: 'Action' }
					],
					rowHeight: 30,
					useExternalFiltering: true,
					enableColumnResizing: true,
				};
			}
		}).catch(function(response) {
			console.error('Error occurred getting cron logs:', response.status, response.data);
		});


		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


	});