angular.module('opalAdmin.controllers.alias.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'highcharts-ng', 'ui.grid.autoResize']).


	/******************************************************************************
	* Controller for the alias logs
	*******************************************************************************/
	controller('alias.log', function ($scope, $uibModal, $filter, aliasCollectionService, Session, $uibModalInstance) {

		// Call our API to get alias logs
		aliasCollectionService.getAliasChartLogs($scope.currentAlias.serial, $scope.currentAlias.type).then(function (response) {
			$scope.aliasChartLogs = $scope.chartConfig.series = response.data;
			angular.forEach($scope.aliasChartLogs, function(serie) {
				angular.forEach(serie.data, function(log) {
					log.x = new Date(log.x);
				});
			});
		}).catch(function(response) {
			console.error('Error occurred getting alias logs:', response.status, response.data);
		});

		var chartConfig = $scope.chartConfig = { 
		    chart: {
		        type: 'spline',
		        zoomType: 'x',
		        className: 'logChart'
		    },
		    title: {
		        text: $scope.currentAlias.type + ' logs for ' + $scope.currentAlias.name_EN + ' / ' + $scope.currentAlias.name_FR
		    },
		    subtitle: {
		        text: 'Highlight the plot area to zoom in and show detailed data'
		    },
		    xAxis: {
		        type: 'datetime',
		        title: {
		            text: 'Datetime sent'
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
		            text: 'Number of ' + $scope.currentAlias.type + ' published'
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
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApiLog = gridApi;
			},
		};

		// Add further columns based on alias type
		if ($scope.currentAlias.type == 'Appointment') {
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

			$scope.gridLogOptions.columnDefs.splice(6, 0, statusField, stateField, scheduledStartField,
				scheduledEndField, actualStartField, actualEndField, roomENField, roomFRField, checkinField);

		}
		else if ($scope.currentAlias.type == 'Document') {
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

			$scope.gridLogOptions.columnDefs.splice(6, 0, createdByField, createdTimeField, approvedByField, 
				approvedTimeField, authoredByField, dateOfServiceField, revisedField, validEntryField,
				origFileField, finalFileField, transferStatusField, transferLogField);

		}

		else if ($scope.currentAlias.type == 'Task') {
			// insert fields after source uid
			var statusField = { field: 'status', displayName: 'Status' };
			var stateField = { field: 'state', displayName: 'State' };
			var dueDateField = { field: 'due_date', displayName: 'Due Date' };
			var creationField = { field: 'creation', displayName: 'Creation Date' };
			var completedField = { field: 'completed', displayName: 'Completed Date' };

			$scope.gridLogOptions.columnDefs.splice(6, 0, statusField, stateField, dueDateField,
				creationField, completedField);
		}

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


	});