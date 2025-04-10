// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.publication.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


	/******************************************************************************
	 * Controller for the post logs
	 *******************************************************************************/
	controller('publication.log', function ($scope, $filter, publicationCollectionService, Session, $uibModalInstance, ErrorHandler) {
		if(Session.retrieveObject('user').language === "FR")
			$scope.currentPublication.module_display = $scope.currentPublication.module_FR;
		else
			$scope.currentPublication.module_display = $scope.currentPublication.module_EN;

		publicationCollectionService.getPublicationsChartLogs($scope.currentPublication.ID, $scope.currentPublication.moduleId, Session.retrieveObject('user').id).then(function (response) {
			$scope.postChartLogs = $scope.chartConfig.series = response.data;
			angular.forEach($scope.postChartLogs, function(series) {
				angular.forEach(series.data, function(log) {
					log.x = new Date(log.x);
				});
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('POSTS.LOG.ERROR'));
			$uibModalInstance.dismiss('cancel');
		});

		var chartConfig = $scope.chartConfig = {
			chart: {
				type: 'spline',
				zoomType: 'x',
				className: 'logChart'
			},
			title: {
				text: $scope.currentPublication.name_EN + ' / ' + $scope.currentPublication.name_FR
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
							/* publicationId, moduleId, OAUserId, cronIds */
							publicationCollectionService.getPublicationListLogs($scope.currentPublication.ID, $scope.currentPublication.moduleId, Session.retrieveObject('user').id, cronSerials).then(function(response){
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
								publicationCollectionService.getPublicationListLogs($scope.currentPublication.ID, $scope.currentPublication.moduleId, Session.retrieveObject('user').id, cronSerials).then(function(response){
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
				{ field: 'name', displayName: $filter('translate')('POSTS.LOG.NAME'), enableColumnMenu: false },
				{ field: 'revision', displayName: $filter('translate')('POSTS.LOG.REVISION'), enableColumnMenu: false },
				{ field: 'cron_serial', displayName: $filter('translate')('POSTS.LOG.CRONLOGSER'), enableColumnMenu: false },
				{ field: 'patient_serial', displayName: $filter('translate')('POSTS.LOG.PATIENTSER'), enableColumnMenu: false },
				{ field: 'read_status', displayName: $filter('translate')('POSTS.LOG.READ_STATUS'), enableColumnMenu: false },
				{ field: 'date_added', displayName: $filter('translate')('POSTS.LOG.DATETIME_SENT'), enableColumnMenu: false },
				{ field: 'mod_action', displayName: $filter('translate')('POSTS.LOG.ACTION'), enableColumnMenu: false }
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
