angular.module('opalAdmin.controllers.testResult.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'highcharts-ng', 'ui.grid.autoResize']).


	/******************************************************************************
	* Controller for the test result logs
	*******************************************************************************/
	controller('testResult.log', function ($scope, $uibModal, $filter, testResultCollectionService, Session, $uibModalInstance) {

		// Call our API to get alias logs
		testResultCollectionService.getTestResultChartLogs($scope.currentTestResult.serial).then(function (response) {
			$scope.testResultChartLogs = $scope.chartConfig.series = response.data;
			angular.forEach($scope.testResultChartLogs, function(serie) {
				angular.forEach(serie.data, function(log) {
					log.x = new Date(log.x);
				});
			});
		}).catch(function(response) {
			console.error('Error occurred getting test result logs: ', response.status, response.data);
		});

		var chartConfig = $scope.chartConfig = { 
		    chart: {
		        type: 'spline',
		        zoomType: 'x',
		        className: 'logChart'
		    },
		    title: {
		        text: 'Test result logs for ' + $scope.currentTestResult.name_EN + ' / ' + $scope.currentTestResult.name_FR
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
		     				testResultCollectionService.getTestResultListLogs(cronSerials).then(function(response){ 
	        					$scope.testResultListLogs = response.data;
	        				});
		        		}
		        		else {
		        			$scope.testResultListLogs = [];
	        				$scope.gridApiLog.grid.refresh();

		        		}
		        	}
		        }
		    },
		    yAxis: {
		        title: {
		            text: 'Number of test results published'
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
		        				testResultCollectionService.getTestResultListLogs(cronLogSerNum).then(function(response){ 
		        					$scope.testResultListLogs = response.data;
		        				});
		        			},
		        			unselect: function (point) {
		        				$scope.testResultListLogs = [];
		        				$scope.gridApiLog.grid.refresh();

		        			}
		        		}
		        	}
		        }
			},

		    series: []
		};

		$scope.testResultListLogs = [];
		// Table options for test result logs
		$scope.gridLogOptions = {
			data: 'testResultListLogs',
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
			onRegisterApi: function (gridApi) {
				$scope.gridApiLog = gridApi;
			},
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


	});