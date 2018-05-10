angular.module('opalAdmin.controllers.legacyQuestionnaire.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'highcharts-ng', 'ui.grid.autoResize']).


	/******************************************************************************
	* Controller for the legacy questionnaire logs
	*******************************************************************************/
	controller('legacyQuestionnaire.log', function ($scope, $uibModal, $filter, legacyQuestionnaireCollectionService, Session, $uibModalInstance) {

		// Call our API to get legacy questionnaire logs
		legacyQuestionnaireCollectionService.getLegacyQuestionnaireChartLogs($scope.currentLegacyQuestionnaire.serial).then(function (response) {
			$scope.legacyQuestionnaireChartLogs = $scope.chartConfig.series = response.data;
			angular.forEach($scope.legacyQuestionnaireChartLogs, function(serie) {
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
		        text: 'Legacy questionnaire logs for ' + $scope.currentLegacyQuestionnaire.name_EN + ' / ' + $scope.currentLegacyQuestionnaire.name_FR
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
		     				legacyQuestionnaireCollectionService.getLegacyQuestionnaireListLogs(cronSerials).then(function(response){ 
	        					$scope.legacyQuestionnaireListLogs = response.data;
	        				});
		        		}
		        		else {
		        			$scope.legacyQuestionnaireListLogs = [];
	        				$scope.gridApiLog.grid.refresh();

		        		}
		        	}
		        }
		    },
		    yAxis: {
		        title: {
		            text: 'Number of legacy questionnaires published'
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
		        				legacyQuestionnaireCollectionService.getLegacyQuestionnaireListLogs(cronLogSerNum).then(function(response){ 
		        					$scope.legacyQuestionnaireListLogs = response.data;
		        				});
		        			},
		        			unselect: function (point) {
		        				$scope.legacyQuestionnaireListLogs = [];
		        				$scope.gridApiLog.grid.refresh();

		        			}
		        		}
		        	}
		        }
			},

		    series: []
		};

		$scope.legacyQuestionnaireListLogs = [];
		// Table options for alias logs
		$scope.gridLogOptions = {
			data: 'legacyQuestionnaireListLogs',
			columnDefs: [
				{ field: 'control_name', displayName: 'Questionnaire' },
				{ field: 'revision', displayName: 'Revision No.' },
				{ field: 'cron_serial', displayName: 'CronLogSer' },
				{ field: 'patient_serial', displayName: 'PatientSer' },
				{ field: 'pt_questionnaire_db', displayName: 'PatientQuestionnaireDBSer' },
				{ field: 'completed', displayName: 'Completed' },
				{ field: 'completion_date', displayName: 'Completion Date' },
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