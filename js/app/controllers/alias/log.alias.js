angular.module('opalAdmin.controllers.alias.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the alias logs
 *******************************************************************************/
controller('alias.log', function ($scope, $uibModal, $filter, aliasCollectionService, Session, $uibModalInstance, ErrorHandler) {

	// Call our API to get alias logs
	aliasCollectionService.getAliasChartLogs($scope.currentAlias.serial, $scope.currentAlias.type).then(function (response) {
		$scope.aliasChartLogs = $scope.chartConfig.series = response.data;
		angular.forEach($scope.aliasChartLogs, function(serie) {
			angular.forEach(serie.data, function(log) {
				log.x = new Date(log.x);
			});
		});
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('ALIAS.LOG.ERROR_LOGS'));
		$uibModalInstance.dismiss('cancel');
	});

	var chartConfig = $scope.chartConfig = {
		chart: {
			type: 'spline',
			zoomType: 'x',
			className: 'logChart'
		},
		title: {
			text: $scope.currentAlias.name_EN + ' / ' + $scope.currentAlias.name_FR
		},
		subtitle: {
			text: $filter('translate')('ALIAS.LOG.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			title: {
				text: $filter('translate')('ALIAS.LOG.DATETIME_SENT')
			},
			events: {
				setExtremes: function (selection) {
					if (selection.min !== undefined && selection.max !== undefined) {
						var cronSerials = new Set();
						var allSeries = selection.target.series; // get all series
						angular.forEach(allSeries, function (series) {
							// check if series is visible (i.e. not disabled via the legend)
							if (series.visible) {
								var points = series.points;
								angular.forEach(points, function (point) {
									timeInMilliSeconds = point.x.getTime();
									if (timeInMilliSeconds >= selection.min && timeInMilliSeconds <= selection.max) {
										if (!cronSerials.has(point.cron_serial)) {
											cronSerials.add(point.cron_serial);
										}
									}
								});
							}
						});
						// convert set to array
						cronSerials = Array.from(cronSerials);
						aliasCollectionService.getAliasListLogs(cronSerials, $scope.currentAlias.type).then(function(response){
							$scope.aliasListLogs = response.data;
						});
					}
					else {
						$scope.aliasListLogs = [];
						$scope.gridApiLog.grid.refresh();

					}
				}
			}
		},
		yAxis: {
			title: {
				text: $filter('translate')('ALIAS.LOG.NUMBER')
			},
			tickInterval: 1,
			min: 0
		},
		tooltip: {
			headerFormat: '<b>{series.name}</b><br>',
			pointFormat: '{point.x:%e. %b}: {point.y:.2f} m'
		},

		plotOptions: {
			spline: {
				marker: {
					enabled: true
				}
			},
			series: {
				allowPointSelect: true,
				point: {
					events: {
						select: function(point) {
							var cronLogSerNum = [point.target.cron_serial];
							aliasCollectionService.getAliasListLogs(cronLogSerNum, $scope.currentAlias.type).then(function(response){
								$scope.aliasListLogs = response.data;
							});
						},
						unselect: function (point) {
							$scope.aliasListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			}
		},

		series: []
	};

	$scope.aliasListLogs = [];
	// Table options for alias logs
	$scope.gridLogOptions = {
		data: 'aliasListLogs',
		columnDefs: [
			{ field: 'expression_name', displayName: $filter('translate')('ALIAS.LOG.CLINICAL_CODE'), enableColumnMenu: false },
			{ field: 'expression_description', displayName: $filter('translate')('ALIAS.LOG.RESOURCE'), enableColumnMenu: false },
			{ field: 'revision', displayName: $filter('translate')('ALIAS.LOG.REVISION_NO'), enableColumnMenu: false },
			{ field: 'cron_serial', displayName: $filter('translate')('ALIAS.LOG.CRONLOGSER'), enableColumnMenu: false },
			{ field: 'patient_serial', displayName: $filter('translate')('ALIAS.LOG.PATIENTSER'), enableColumnMenu: false },
			{ field: 'source_db', displayName: $filter('translate')('ALIAS.LOG.DATABASE'), enableColumnMenu: false },
			{ field: 'source_uid', displayName: $filter('translate')('ALIAS.LOG.CLINICAL_UID'), enableColumnMenu: false },
			{ field: 'read_status', displayName: $filter('translate')('ALIAS.LOG.READ_STATUS'), enableColumnMenu: false },
			{ field: 'date_added', displayName: $filter('translate')('ALIAS.LOG.DATETIME_SENT'), enableColumnMenu: false },
			{ field: 'mod_action', displayName: $filter('translate')('ALIAS.LOG.ACTION'), enableColumnMenu: false }
		],
		rowHeight: 30,
		useExternalFiltering: true,
		enableColumnResizing: true,
		onRegisterApi: function (gridApi) {
			$scope.gridApiLog = gridApi;
		},
	};

	// Add further columns based on alias type
	if ($scope.currentAlias.type == 'Appointment') {
		// insert fields after source uid
		var statusField = { field: 'status', displayName: $filter('translate')('ALIAS.LOG.STATUS'), enableColumnMenu: false };
		var stateField = { field: 'state', displayName: $filter('translate')('ALIAS.LOG.STATE'), enableColumnMenu: false };
		var scheduledStartField = { field: 'scheduled_start', displayName: $filter('translate')('ALIAS.LOG.SCHEDULED_START'), enableColumnMenu: false };
		var scheduledEndField = { field: 'scheduled_end', displayName: $filter('translate')('ALIAS.LOG.SCHEDULED_END'), enableColumnMenu: false };
		var actualStartField = { field: 'actual_start', displayName: $filter('translate')('ALIAS.LOG.ACTUAL_START'), enableColumnMenu: false };
		var actualEndField = { field: 'actual_end', displayName: $filter('translate')('ALIAS.LOG.ACTUAL_END'), enableColumnMenu: false };
		var roomENField = { field: 'room_EN', displayName: $filter('translate')('ALIAS.LOG.ROOM_LOCATION_EN'), enableColumnMenu: false };
		var roomFRField = { field: 'room_FR', displayName: $filter('translate')('ALIAS.LOG.ROOM_LOCATION_FR'), enableColumnMenu: false };
		var checkinField = { field: 'checkin', displayName: $filter('translate')('ALIAS.LOG.CHECKIN'), enableColumnMenu: false };

		$scope.gridLogOptions.columnDefs.splice(6, 0, statusField, stateField, scheduledStartField,
			scheduledEndField, actualStartField, actualEndField, roomENField, roomFRField, checkinField);

	}
	else if ($scope.currentAlias.type == 'Document') {
		// insert fields after source uid
		var createdByField = { field: 'created_by', displayName: $filter('translate')('ALIAS.LOG.CREATED_BY'), enableColumnMenu: false };
		var createdTimeField = {field: 'created_time', displayName: $filter('translate')('ALIAS.LOG.CREATED_TIME'), enableColumnMenu: false };
		var approvedByField = { field: 'approved_by', displayName: $filter('translate')('ALIAS.LOG.APPROVED_BY'), enableColumnMenu: false };
		var approvedTimeField = { field: 'approved_time', displayName: $filter('translate')('ALIAS.LOG.APPROVED_TIME'), enableColumnMenu: false };
		var authoredByField = { field: 'authored_by', displayName: $filter('translate')('ALIAS.LOG.AUTHORED_BY'), enableColumnMenu: false };
		var dateOfServiceField = { field: 'dateofservice', displayName: $filter('translate')('ALIAS.LOG.DATE_OF_SERVICE'), enableColumnMenu: false };
		var revisedField = { field: 'revised', displayName: $filter('translate')('ALIAS.LOG.REVISED'), enableColumnMenu: false };
		var validEntryField = { field: 'valid', displayName: $filter('translate')('ALIAS.LOG.VALID'), enableColumnMenu: false };
		var origFileField = { field: 'original_file', displayName: $filter('translate')('ALIAS.LOG.ORIGINAL_FILE'), enableColumnMenu: false };
		var finalFileField = { field: 'final_file', displayName: $filter('translate')('ALIAS.LOG.FINAL_FILE'), enableColumnMenu: false };
		var transferStatusField = {field: 'transfer', displayName: $filter('translate')('ALIAS.LOG.TRANSFER_STATUS'), enableColumnMenu: false };
		var transferLogField = { field: 'transfer_log', displayName: $filter('translate')('ALIAS.LOG.TRANSFER_LOG'), enableColumnMenu: false };

		$scope.gridLogOptions.columnDefs.splice(6, 0, createdByField, createdTimeField, approvedByField,
			approvedTimeField, authoredByField, dateOfServiceField, revisedField, validEntryField,
			origFileField, finalFileField, transferStatusField, transferLogField);

	}

	else if ($scope.currentAlias.type == 'Task') {
		// insert fields after source uid
		var statusField = { field: 'status', displayName: $filter('translate')('ALIAS.LOG.STATUS'), enableColumnMenu: false };
		var stateField = { field: 'state', displayName: $filter('translate')('ALIAS.LOG.STATE'), enableColumnMenu: false };
		var dueDateField = { field: 'due_date', displayName: $filter('translate')('ALIAS.LOG.DUE_DATE'), enableColumnMenu: false };
		var creationField = { field: 'creation', displayName: $filter('translate')('ALIAS.LOG.CREATION_DATE'), enableColumnMenu: false };
		var completedField = { field: 'completed', displayName: $filter('translate')('ALIAS.LOG.COMPLETED_DATE'), enableColumnMenu: false };

		$scope.gridLogOptions.columnDefs.splice(6, 0, statusField, stateField, dueDateField,
			creationField, completedField);
	}

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};


});