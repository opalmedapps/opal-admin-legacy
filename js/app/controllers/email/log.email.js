// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.email.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the email logs
 *******************************************************************************/
controller('email.log', function ($scope, $uibModal, $filter, emailCollectionService, Session, $uibModalInstance) {

	// Call our API to get email logs
	emailCollectionService.getEmailChartLogs($scope.currentEmail.serial).then(function (response) {
		$scope.emailChartLogs = $scope.chartConfig.series = response.data;
		angular.forEach($scope.emailChartLogs, function(series) {
			angular.forEach(series.data, function(log) {
				log.x = new Date(log.x);
			});
		});
	}).catch(function(response) {
		alert($filter('translate')('EMAILS.LOG.ERROR_LOGS') + "\r\n\r\n" + response.status + " - " + response.data);
	});

	var chartConfig = $scope.chartConfig = {
		chart: {
			type: 'spline',
			zoomType: 'x',
			className: 'logChart'
		},
		title: {
			text: $scope.currentEmail.name_EN + ' / ' + $scope.currentEmail.name_FR
		},
		subtitle: {
			text: $filter('translate')('EMAILS.LOG.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			title: {
				text: $filter('translate')('EMAILS.LOG.DATETIME_SENT')
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
						emailCollectionService.getEmailListLogs(cronSerials).then(function(response){
							$scope.emailListLogs = response.data;
						});
					}
					else {
						$scope.emailListLogs = [];
						$scope.gridApiLog.grid.refresh();
					}
				}
			}
		},
		yAxis: {
			title: {
				text: $filter('translate')('EMAILS.LOG.NUMBER')
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
							emailCollectionService.getEmailListLogs(cronLogSerNum).then(function(response){
								$scope.emailListLogs = response.data;
							});
						},
						unselect: function (point) {
							$scope.emailListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			}
		},
		series: []
	};

	$scope.emailListLogs = [];
	// Table options for alias logs
	$scope.gridLogOptions = {
		data: 'emailListLogs',
		columnDefs: [
			{ field: 'control_serial', displayName: $filter('translate')('EMAILS.LOG.CONTROLSER') },
			{ field: 'revision', displayName: $filter('translate')('EMAILS.LOG.REVISION') },
			{ field: 'cron_serial', displayName: $filter('translate')('EMAILS.LOG.CRONLOGSER') },
			{ field: 'patient_serial', displayName: $filter('translate')('EMAILS.LOG.PATIENTSER') },
			{ field: 'type', displayName: $filter('translate')('EMAILS.LOG.EMAIL_TYPE') },
			{ field: 'date_added', displayName: $filter('translate')('EMAILS.LOG.DATETIME_SENT') },
			{ field: 'mod_action', displayName: $filter('translate')('EMAILS.LOG.ACTION') }
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
