angular.module('opalAdmin.controllers.post.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'highcharts-ng', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the post logs
 *******************************************************************************/
controller('post.log', function ($scope, $uibModal, $filter, postCollectionService, Session, $uibModalInstance) {
	postCollectionService.getPostChartLogs($scope.currentPost.serial, $scope.currentPost.type).then(function (response) {
		$scope.postChartLogs = $scope.chartConfig.series = response.data;
		angular.forEach($scope.postChartLogs, function(serie) {
			angular.forEach(serie.data, function(log) {
				log.x = new Date(log.x);
			});
		});
	}).catch(function(response) {
		alert($filter('translate')('POSTS.LOG.ERROR') + "\r\n\r\n" + response.status);
		$uibModalInstance.dismiss('cancel');
	});

	var chartConfig = $scope.chartConfig = {
		chart: {
			type: 'spline',
			zoomType: 'x',
			className: 'logChart'
		},
		title: {
			text: $scope.currentPost.name_EN + ' / ' + $scope.currentPost.name_FR
		},
		subtitle: {
			text: $filter('translate')('POSTS.LOG.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			title: {
				text: $filter('translate')('POSTS.LOG.DATETIME_SENT')
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
						postCollectionService.getPostListLogs(cronSerials, $scope.currentPost.type).then(function(response){
							$scope.postListLogs = response.data;
						});
					}
					else {
						$scope.postListLogs = [];
						$scope.gridApiLog.grid.refresh();

					}
				}
			}
		},
		yAxis: {
			title: {
				text: $filter('translate')('POSTS.LOG.NUMBER')
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
							postCollectionService.getPostListLogs(cronLogSerNum, $scope.currentPost.type).then(function(response){
								$scope.postListLogs = response.data;
							});
						},
						unselect: function (point) {
							$scope.postListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			}
		},

		series: []
	};

	$scope.postListLogs = [];
	// Table options for alias logs
	$scope.gridLogOptions = {
		data: 'postListLogs',
		columnDefs: [
			{ field: 'post_control_name', displayName: $filter('translate')('POSTS.LOG.NAME') },
			{ field: 'revision', displayName: $filter('translate')('POSTS.LOG.REVISION') },
			{ field: 'cron_serial', displayName: $filter('translate')('POSTS.LOG.CRONLOGSER') },
			{ field: 'patient_serial', displayName: $filter('translate')('POSTS.LOG.PATIENTSER') },
			{ field: 'read_status', displayName: $filter('translate')('POSTS.LOG.READ_STATUS') },
			{ field: 'date_added', displayName: $filter('translate')('POSTS.LOG.DATETIME_SENT') },
			{ field: 'mod_action', displayName: $filter('translate')('POSTS.LOG.ACTION') }
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