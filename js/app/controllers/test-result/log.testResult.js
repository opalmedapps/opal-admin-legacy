// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.testResult.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the test result logs
 *******************************************************************************/
controller('testResult.log', function ($scope, $uibModal, $filter, $uibModalInstance, testResultCollectionService, Session, ErrorHandler) {

	// Call our API to get alias logs
	testResultCollectionService.getTestResultChartLogs($scope.currentTestResult.serial).then(function (response) {
		$scope.testResultChartLogs = $scope.chartConfig.series = response.data;
		angular.forEach($scope.testResultChartLogs, function(serie) {
			angular.forEach(serie.data, function(log) {
				log.x = new Date(log.x);
			});
		});
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('TEST.LOG.ERROR_LOGS'));
		$uibModalInstance.dismiss('cancel');
	});

	var chartConfig = $scope.chartConfig = {
		chart: {
			type: 'spline',
			zoomType: 'x',
			className: 'logChart'
		},
		title: {
			text: $scope.currentTestResult.name_EN + ' / ' + $scope.currentTestResult.name_FR
		},
		subtitle: {
			text: $filter('translate')('TEST.LOG.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			title: {
				text: $filter('translate')('TEST.LOG.DATETIME_SENT')
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
				text: $filter('translate')('TEST.LOG.NUMBER')
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
			{ field: 'expression_name', displayName: $filter('translate')('TEST.LOG.TEST_NAME'), enableColumnMenu: false },
			{ field: 'revision', displayName: $filter('translate')('TEST.LOG.REVISION_NO'), enableColumnMenu: false },
			{ field: 'cron_serial', displayName: $filter('translate')('TEST.LOG.CRONLOGSER'), enableColumnMenu: false },
			{ field: 'patient_serial', displayName: $filter('translate')('TEST.LOG.PATIENTSER'), enableColumnMenu: false },
			{ field: 'source_db', displayName: $filter('translate')('TEST.LOG.DATABASE'), enableColumnMenu: false },
			{ field: 'source_uid', displayName: $filter('translate')('TEST.LOG.CLINICAL_UID'), enableColumnMenu: false },
			{ field: 'abnormal_flag', displayName: $filter('translate')('TEST.LOG.ABNORMAL_FLAG'), enableColumnMenu: false },
			{ field: 'test_date', displayName: $filter('translate')('TEST.LOG.TEST_DATE'), enableColumnMenu: false },
			{ field: 'max_norm', displayName: $filter('translate')('TEST.LOG.MAX_NORM'), enableColumnMenu: false },
			{ field: 'min_norm', displayName: $filter('translate')('TEST.LOG.MIN_NORM'), enableColumnMenu: false },
			{ field: 'test_value', displayName: $filter('translate')('TEST.LOG.TEST_VALUE'), enableColumnMenu: false },
			{ field: 'unit', displayName: $filter('translate')('TEST.LOG.UNIT'), enableColumnMenu: false },
			{ field: 'valid', displayName: $filter('translate')('TEST.LOG.VALID'), enableColumnMenu: false },
			{ field: 'read_status', displayName: $filter('translate')('TEST.LOG.READ_STATUS'), enableColumnMenu: false },
			{ field: 'date_added', displayName: $filter('translate')('TEST.LOG.DATETIME_SENT'), enableColumnMenu: false },
			{ field: 'mod_action', displayName: $filter('translate')('TEST.LOG.ACTION'), enableColumnMenu: false }
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