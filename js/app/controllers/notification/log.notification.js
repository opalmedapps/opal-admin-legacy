angular.module('opalAdmin.controllers.notification.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'highcharts-ng', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the notification logs
 *******************************************************************************/
controller('notification.log', function ($scope, $uibModal, $filter, notificationCollectionService, Session, $uibModalInstance, ErrorHandler) {

	// Call our API to get notification logs
	notificationCollectionService.getNotificationChartLogs($scope.currentNotification.serial).then(function (response) {
		$scope.notificationChartLogs = $scope.chartConfig.series = response.data;
		angular.forEach($scope.notificationChartLogs, function(serie) {
			angular.forEach(serie.data, function(log) {
				log.x = new Date(log.x);
			});
		});
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.LOG.ERROR'));
	});

	var chartConfig = $scope.chartConfig = {
		chart: {
			type: 'spline',
			zoomType: 'x',
			className: 'logChart'
		},
		title: {
			text: $scope.currentNotification.name_EN + ' / ' + $scope.currentNotification.name_FR
		},
		subtitle: {
			text: $filter('translate')('NOTIFICATIONS.LOG.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			// dateTimeLabelFormats: { // don't display the dummy year
			//     month: '%e. %b %H:%M',
			//     year: '%b'
			// },
			title: {
				text: $filter('translate')('NOTIFICATIONS.LOG.DATETIME_SENT')
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
						notificationCollectionService.getNotificationListLogs(cronSerials).then(function(response){
							$scope.notificationListLogs = response.data;
						});
					}
					else {
						$scope.notificationListLogs = [];
						$scope.gridApiLog.grid.refresh();

					}
				}
			}
		},
		yAxis: {
			title: {
				text: $filter('translate')('NOTIFICATIONS.LOG.NUMBER')
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
							notificationCollectionService.getNotificationListLogs(cronLogSerNum).then(function(response){
								$scope.notificationListLogs = response.data;
							});
						},
						unselect: function (point) {
							$scope.notificationListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			}
		},

		series: []
	};

	$scope.notificationListLogs = [];
	// Table options for notification logs
	$scope.gridLogOptions = {
		data: 'notificationListLogs',
		columnDefs: [
			{ field: 'control_serial', displayName: $filter('translate')('NOTIFICATIONS.LOG.CONTROLSER'), enableColumnMenu: false },
			{ field: 'revision', displayName: $filter('translate')('NOTIFICATIONS.LOG.REVISION'), enableColumnMenu: false },
			{ field: 'cron_serial', displayName: $filter('translate')('NOTIFICATIONS.LOG.CRONLOGSER'), enableColumnMenu: false },
			{ field: 'patient_serial', displayName: $filter('translate')('NOTIFICATIONS.LOG.PATIENTSER'), enableColumnMenu: false },
			{ field: 'type', displayName: $filter('translate')('NOTIFICATIONS.LOG.NOTIFICATION_TYPE'), enableColumnMenu: false },
			{ field: 'ref_table_serial', displayName: $filter('translate')('NOTIFICATIONS.LOG.REF_TABLE_SER'), enableColumnMenu: false },
			{ field: 'read_status', displayName: $filter('translate')('NOTIFICATIONS.LOG.READ_STATUS'), enableColumnMenu: false },
			{ field: 'date_added', displayName: $filter('translate')('NOTIFICATIONS.LOG.DATETIME_SENT'), enableColumnMenu: false },
			{ field: 'mod_action', displayName: $filter('translate')('NOTIFICATIONS.LOG.ACTION'), enableColumnMenu: false }
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