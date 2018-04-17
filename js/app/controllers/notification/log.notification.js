angular.module('opalAdmin.controllers.notification.log', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'highcharts-ng']).


	/******************************************************************************
	* Controller for the notification page
	*******************************************************************************/
	controller('notification.log', function ($scope, $uibModal, $filter, notificationCollectionService, Session) {

		// Call our API to get notification logs
		notificationCollectionService.getNotificationLogs($scope.currentNotification.serial).then(function (response) {
			$scope.notificationLogs = $scope.chartConfig.series = response.data;
			angular.forEach($scope.notificationLogs, function(serie) {
				console.log(serie);
				angular.forEach(serie.data, function(log) {
					log.x = new Date(log.x);
				});
			});
			console.log(response.data);
		}).catch(function(response) {
			console.error('Error occurred getting notification logs:', response.status, response.data);
		});

		$scope.chartConfig = { 
		    chart: {
		        type: 'spline',
		        zoomType: 'x',
		        className: 'logChart'
		    },
		    title: {
		        text: 'Notification logs for ' + $scope.currentNotification.name_EN + ' / ' + $scope.currentNotification.name_FR
		    },
		    subtitle: {
		        text: 'Highlight the plot area to zoom in'
		    },
		    xAxis: {
		        type: 'datetime',
		        // dateTimeLabelFormats: { // don't display the dummy year
		        //     month: '%e. %b %H:%M',
		        //     year: '%b'
		        // },
		        title: {
		            text: 'Datetime sent'
		        }
		    },
		    yAxis: {
		        title: {
		            text: 'Number of notifications sent'
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
		        }
			},

		    series: []
		};


	});