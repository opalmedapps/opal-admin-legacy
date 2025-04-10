// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.educationalMaterial.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).


/******************************************************************************
 * Controller for the educational material logs
 *******************************************************************************/
controller('educationalMaterial.log', function ($scope, $uibModal, $filter, educationalMaterialCollectionService, Session, $uibModalInstance, ErrorHandler) {
	// Call our API to get educational material logs
	educationalMaterialCollectionService.getEducationalMaterialChartLogs($scope.currentEduMat.serial).then(function (response) {
		$scope.educationalMaterialChartLogs = $scope.chartConfig.series = response.data;
		angular.forEach($scope.educationalMaterialChartLogs, function(series) {
			angular.forEach(series.data, function(log) {
				log.x = new Date(log.x);
			});
		});
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('EDUCATION.LOG.ERROR'));
	});

	var chartConfig = $scope.chartConfig = {
		chart: {
			type: 'spline',
			zoomType: 'x',
			className: 'logChart'
		},
		title: {
			text: $scope.currentEduMat.name_EN + ' / ' + $scope.currentEduMat.name_FR
		},
		subtitle: {
			text: $filter('translate')('EDUCATION.LOG.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			title: {
				text: $filter('translate')('EDUCATION.LOG.DATETIME_SENT')
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
						educationalMaterialCollectionService.getEducationalMaterialListLogs(cronSerials).then(function(response){
							$scope.educationalMaterialListLogs = response.data;
						});
					}
					else {
						$scope.educationalMaterialListLogs = [];
						$scope.gridApiLog.grid.refresh();

					}
				}
			}
		},
		yAxis: {
			title: {
				text: $filter('translate')('EDUCATION.LOG.NUMBER')
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
							educationalMaterialCollectionService.getEducationalMaterialListLogs(cronLogSerNum).then(function(response){
								$scope.educationalMaterialListLogs = response.data;
							});
						},
						unselect: function (point) {
							$scope.educationalMaterialListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			}
		},

		series: []
	};

	$scope.educationalMaterialListLogs = [];
	// Table options for educational material logs
	$scope.gridLogOptions = {
		data: 'educationalMaterialListLogs',
		columnDefs: [
			{ field: 'material_name', displayName: $filter('translate')('EDUCATION.LOG.NAME'), enableColumnMenu: false },
			{ field: 'revision', displayName: $filter('translate')('EDUCATION.LOG.REVISION'), enableColumnMenu: false },
			{ field: 'cron_serial', displayName: $filter('translate')('EDUCATION.LOG.CRONLOGSER'), enableColumnMenu: false },
			{ field: 'patient_serial', displayName: $filter('translate')('EDUCATION.LOG.PATIENTSER'), enableColumnMenu: false },
			{ field: 'read_status', displayName: $filter('translate')('EDUCATION.LOG.READ_STATUS'), enableColumnMenu: false },
			{ field: 'date_added', displayName: $filter('translate')('EDUCATION.LOG.DATETIME_SENT'), enableColumnMenu: false },
			{ field: 'mod_action', displayName: $filter('translate')('EDUCATION.LOG.ACTION'), enableColumnMenu: false }
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
