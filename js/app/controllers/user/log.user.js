angular.module('opalAdmin.controllers.user.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


	/******************************************************************************
	* Controller for the user logs
	*******************************************************************************/
	controller('user.log', function ($scope, $uibModal, $filter, userCollectionService, Session, $uibModalInstance) {

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
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'login', displayName: 'Login Time' },
						{ field: 'logout', displayName: 'Logout Time' },
						{ field: 'session_duration', displayName: 'Session Duration'}
					],
					enableFiltering: true,
					enableColumnResizing: true,
				};
			}

			if ($scope.userListLogs.alias.length) {
				$scope.gridAliasLogOptions = {
					data: $scope.userListLogs.alias,
					columnDefs: [
						{ field: 'serial', displayName: 'Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'type', displayName: 'Type' },
						{ field: 'update', displayName: 'Update Flag' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'description_EN', displayName: 'Description EN' },
						{ field: 'description_FR', displayName: 'Description FR' },
						{ field: 'educational_material', displayName: 'Educational Material Ser' },
						{ field: 'source_db', displayName: 'Database' },
						{ field: 'color', displayName: 'Color' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.aliasExpression.length) {
				$scope.gridAliasExpressionLogOptions = {
					data: $scope.userListLogs.aliasExpression,
					columnDefs: [
						{ field: 'serial', displayName: 'Alias Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'expression', displayName: 'Clinical Code' },
						{ field: 'resource_description', displayName: 'Resource Description' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.diagnosisTranslation.length) {
				$scope.gridDiagnosisTranslationLogOptions = {
					data: $scope.userListLogs.diagnosisTranslation,
					columnDefs: [
						{ field: 'serial', displayName: 'Diagnosis Translation Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'educational_material', displayName: 'Educational Material Ser' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'description_EN', displayName: 'Description EN' },
						{ field: 'description_FR', displayName: 'Description FR' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.diagnosisCode.length) {
				$scope.gridDiagnosisCodeLogOptions = {
					data: $scope.userListLogs.diagnosisCode,
					columnDefs: [
						{ field: 'serial', displayName: 'Diagnosis Translation Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'sourceuid', displayName: 'Source UID' },
						{ field: 'code', displayName: 'Diagnosis Code' },
						{ field: 'description', displayName: 'Description' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.email.length) {
				$scope.gridEmailLogOptions = {
					data: $scope.userListLogs.email,
					columnDefs: [
						{ field: 'serial', displayName: 'Email Control Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'subject_EN', displayName: 'Subject EN' },
						{ field: 'subject_FR', displayName: 'Subject FR' },
						{ field: 'body_EN', displayName: 'Body EN' },
						{ field: 'body_FR', displayName: 'Body FR' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.trigger.length) {
				$scope.gridTriggerLogOptions = {
					data: $scope.userListLogs.trigger,
					columnDefs: [
						{ field: 'control_serial', displayName: 'Control Serial' },
						{ field: 'control_table', displayName: 'Control Table' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'type', displayName: 'Trigger Type' },
						{ field: 'filterid', displayName: 'Filter Id' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.hospitalMap.length) {
				$scope.gridHospitalMapLogOptions = {
					data: $scope.userListLogs.hospitalMap,
					columnDefs: [
						{ field: 'serial', displayName: 'Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'url', displayName: 'Map URL' },
						{ field: 'qrcode', displayName: 'QR Id' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'description_EN', displayName: 'Description EN' },
						{ field: 'description_FR', displayName: 'Description FR' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.post.length) {
				$scope.gridPostLogOptions = {
					data: $scope.userListLogs.post,
					columnDefs: [
						{ field: 'control_serial', displayName: 'Control Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'type', displayName: 'Post Type' },
						{ field: 'publish', displayName: 'Publish Flag' },
						{ field: 'disabled', displayName: 'Disabled Flag' },
						{ field: 'publish_date', displayName: 'Publish Date' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'body_EN', displayName: 'Body EN' },
						{ field: 'body_FR', displayName: 'Body FR' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.notification.length) {
				$scope.gridNotificationLogOptions = {
					data: $scope.userListLogs.notification,
					columnDefs: [
						{ field: 'control_serial', displayName: 'Control Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'type', displayName: 'Notification Type' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'description_EN', displayName: 'Description EN' },
						{ field: 'description_FR', displayName: 'Description FR' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.legacyQuestionnaire.length) {
				$scope.gridLegacyQuestionnaireLogOptions = {
					data: $scope.userListLogs.legacyQuestionnaire,
					columnDefs: [
						{ field: 'control_serial', displayName: 'Control Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'db_serial', displayName: 'Questionnaire DB Serial' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'intro_EN', displayName: 'Intro EN' },
						{ field: 'intro_FR', displayName: 'Intro FR' },
						{ field: 'publish', displayName: 'Publish Flag' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.testResult.length) {
				$scope.gridTestResultLogOptions = {
					data: $scope.userListLogs.testResult,
					columnDefs: [
						{ field: 'control_serial', displayName: 'Control Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'source_db', displayName: 'Source DB' },
						{ field: 'educational_material', displayName: 'Educational Material Ser' },
						{ field: 'name_EN', displayName: 'Name EN' },
						{ field: 'name_FR', displayName: 'Name FR' },
						{ field: 'description_EN', displayName: 'Description EN' },
						{ field: 'description_FR', displayName: 'Description FR' },
						{ field: 'group_EN', displayName: 'Group EN' },
						{ field: 'group_FR', displayName: 'Group FR' },
						{ field: 'publish', displayName: 'Publish Flag' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			if ($scope.userListLogs.testResultExpression.length) {
				$scope.gridTestResultExpressionLogOptions = {
					data: $scope.userListLogs.testResultExpression,
					columnDefs: [
						{ field: 'control_serial', displayName: 'Control Serial' },
						{ field: 'revision', displayName: 'Revision No.' },
						{ field: 'sessionid', displayName: 'Session Id' },
						{ field: 'expression', displayName: 'Test Name' },
						{ field: 'mod_action', displayName: 'Action' },
						{ field: 'date_added', displayName: 'Datetime Modified' }
					],
					enableFiltering: true,
					enableColumnResizing: true,
				}
			}

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

		}).catch(function(response) {
			console.error('Error occurred getting user logs:', response.status, response.data);
		});

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});