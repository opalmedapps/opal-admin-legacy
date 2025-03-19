angular.module('opalAdmin.controllers.cron', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Cron Page controller
	 *******************************************************************************/
	controller('cron', function ($scope, $locale, $filter, $uibModal, cronCollectionService, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.cron_log]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.cron_log]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.cron_log]) & (1 << 2)) !== 0);

		$scope.bannerMessage = "";
		$scope.readyToDisplay = true;
		// Function to show page banner
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};
		// Function to set banner class
		$scope.setBannerClass = function (classname) {
			// Remove any classes starting with "alert-"
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + classname);
		};

		$locale["DATETIME_FORMATS"]["SHORTDAY"] = [
			$filter('translate')('DATEPICKER.SUNDAY_S'),
			$filter('translate')('DATEPICKER.MONDAY_S'),
			$filter('translate')('DATEPICKER.TUESDAY_S'),
			$filter('translate')('DATEPICKER.WEDNESDAY_S'),
			$filter('translate')('DATEPICKER.THURSDAY_S'),
			$filter('translate')('DATEPICKER.FRIDAY_S'),
			$filter('translate')('DATEPICKER.SATURDAY_S')
		];

		$locale["DATETIME_FORMATS"]["MONTH"] = [
			$filter('translate')('DATEPICKER.JANUARY'),
			$filter('translate')('DATEPICKER.FEBRUARY'),
			$filter('translate')('DATEPICKER.MARCH'),
			$filter('translate')('DATEPICKER.APRIL'),
			$filter('translate')('DATEPICKER.MAY'),
			$filter('translate')('DATEPICKER.JUNE'),
			$filter('translate')('DATEPICKER.JULY'),
			$filter('translate')('DATEPICKER.AUGUST'),
			$filter('translate')('DATEPICKER.SEPTEMBER'),
			$filter('translate')('DATEPICKER.OCTOBER'),
			$filter('translate')('DATEPICKER.NOVEMBER'),
			$filter('translate')('DATEPICKER.DECEMBER')
		];


		$scope.changesMade = false;
		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		$scope.showWeeks = true; // show weeks sidebar
		$scope.toggleWeeks = function () {
			$scope.showWeeks = !$scope.showWeeks;
		};

		// set minimum date (today's date)
		$scope.toggleMin = function () {
			$scope.minDate = ($scope.minDate) ? null : new Date();
		};
		$scope.toggleMin();

		// Open popup calendar
		$scope.open = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();

			$scope.opened = true;
		};

		$scope.dateOptions = {
			'year-format': "'yy'",
			'starting-day': 1
		};

		// Date format
		$scope.format = 'yyyy-MM-dd';

		// object for cron repeat units
		$scope.repeatUnits = [
			$filter('translate')('CRON.PANEL.HOURS'),
			$filter('translate')('CRON.PANEL.MINUTES')
		];

		// Initialize object for cron details
		$scope.cronDetails = {};
		$scope.cronDetailsMod = {};

		// Call our API to get the cron details from our DB
		cronCollectionService.getCronDetails().then(function (response) {
			$scope.cronDetails = response.data; // assign value

			// Split the hours and minutes to display them in their respective text boxes
			var hours = $scope.cronDetails.nextCronTime.split(":")[0];
			var minutes = $scope.cronDetails.nextCronTime.split(":")[1];
			var minutes = $scope.cronDetails.nextCronTime.split(":")[1];
			var d = new Date();
			d.setHours(hours);
			d.setMinutes(minutes);
			$scope.cronDetails.nextCronTime = d;

			$scope.cronDetailsMod = jQuery.extend(true, {}, $scope.cronDetails); // deep copy
			var year = $scope.cronDetailsMod.nextCronDate.split("-")[0];
			var month = parseInt($scope.cronDetailsMod.nextCronDate.split("-")[1]) - 1;
			var day = parseInt($scope.cronDetailsMod.nextCronDate.split("-")[2]) + 1;
			$scope.cronDetailsMod.nextCronDate = new Date(Date.UTC(year, month, day));
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('CRON.PANEL.ERROR_CRON_DETAILS'));
		});

		// Ajax call when cron details are submitted
		$scope.submitCronChange = function () {

			if ($scope.checkForm()) {
				// Convert date formats of all datetime fields
				$scope.cronDetailsMod.nextCronDate = moment($scope.cronDetailsMod.nextCronDate).format("YYYY-MM-DD");
				$scope.cronDetailsMod.nextCronTime = moment($scope.cronDetailsMod.nextCronTime).format("HH:mm");

				$.ajax({
					type: "POST",
					url: "cron/update/cron",
					data: $scope.cronDetailsMod,
					success: function () {

						// Call our API to get the cron details from our DB
						cronCollectionService.getCronDetails().then(function (response) {
							$scope.cronDetails = response.data; // assign value

							// Split the hours and minutes to display them in their respective text boxes
							var hours = $scope.cronDetails.nextCronTime.split(":")[0];
							var minutes = $scope.cronDetails.nextCronTime.split(":")[1];
							var d = new Date();
							d.setHours(hours);
							d.setMinutes(minutes);
							$scope.cronDetails.nextCronTime = d;

							$scope.cronDetailsMod = jQuery.extend(true, {}, $scope.cronDetails); // deep copy
							var year = $scope.cronDetailsMod.nextCronDate.split("-")[0];
							var month = parseInt($scope.cronDetailsMod.nextCronDate.split("-")[1]) - 1;
							var day = parseInt($scope.cronDetailsMod.nextCronDate.split("-")[2]) + 1;
							$scope.cronDetailsMod.nextCronDate = new Date(Date.UTC(year, month, day));
						}).catch(function(err) {
							ErrorHandler.onError(err, $filter('translate')('CRON.PANEL.ERROR_CRON_DETAILS'));
						});

						$scope.bannerMessage = "Saved Cron Settings!";
						$scope.setBannerClass = "success";
						$scope.showBanner();

						$scope.changesMade = false;

					}
				});
			}
		};

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {

			if ($scope.cronDetailsMod.nextCronDate && $scope.cronDetailsMod.nextCronTime
				&& $scope.cronDetailsMod.repeatInterval && $scope.cronDetailsMod.repeatUnits
				&& $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		// Call our API to get cron logs
		cronCollectionService.getCronChartLogs().then(function (response) {
			$scope.cronChartLogs = $scope.chartConfig.series = response.data;
			angular.forEach($scope.cronChartLogs, function(serie) {
				angular.forEach(serie.data, function(log) {
					log.x = new Date(log.x);
					log.y = parseInt(log.y);
				});
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('CRON.PANEL.ERROR_CRON_LOGS'));
		});

		$scope.minSelection = new Date();
		$scope.maxSelection = new Date();

		var chartConfig = $scope.chartConfig = {
			chart: {
				zoomType: 'x',
				className: 'logChart'
			},
			title: {
				text: $filter('translate')('CRON.PANEL.CRON_LOGS')
			},
			subtitle: {
				text: $filter('translate')('CRON.PANEL.HIGHLIGHT')
			},
			xAxis: {
				type: 'datetime',
				title: {
					text: $filter('translate')('CRON.PANEL.DATETIME')
				},
				events: {
					setExtremes: function (selection) {
						if (selection.min !== undefined && selection.max !== undefined) {
							var contentNames = {};
							var allSeries = selection.target.series; // get all series
							$scope.minSelection = moment.tz(new Date(selection.min), "").format("YYYY-MM-DD, h:mm:ss a");
							$scope.maxSelection = moment.tz(new Date(selection.max), "").format("YYYY-MM-DD, h:mm:ss a");
							angular.forEach(allSeries, function (series) {
								// check if series is visible (i.e. not disabled via the legend)
								if (series.visible) {
									var name = series.name;
									var points = series.points;
									if (!(name in contentNames)) {
										contentNames[name] = new Set();
									}
									angular.forEach(points, function (point) {
										timeInMilliSeconds = point.x.getTime();
										if (timeInMilliSeconds >= selection.min && timeInMilliSeconds <= selection.max) {
											contentNames[name].add(point.cron_serial);
										}
									});
								}
							});
							for (var content in contentNames) {
								if (contentNames.hasOwnProperty(content)) {
									contentNames[content] = Array.from(contentNames[content]);
								}
							}
							if ($scope.readyToDisplay) {
								$scope.getSelectedCronLogs(contentNames);
								$scope.readyToDisplay = false;
							}
						}
					}
				}
			},
			yAxis: {
				title: {
					text: $filter('translate')('CRON.PANEL.CONTENTS')
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

		// Function for when the cron logs have ben highlighted for display
		// We open a modal
		$scope.getSelectedCronLogs = function (contentNames) {

			$scope.contentNames = contentNames;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/cron/log.cron.html',
				controller: 'cron.log',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update
			modalInstance.closed.then(function () {
				$scope.readyToDisplay = true;
				$scope.contentNames = [];
				var chartObj = chartConfig.getChartObj();
				chartObj.xAxis[0].setExtremes(undefined, undefined, true);
			});

		};

	});