angular.module('opalAdmin.controllers.cron', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	* Cron Page controller 
	*******************************************************************************/
	controller('cron', function ($scope, cronCollectionService) {

		$scope.bannerMessage = "";
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
			'Minutes',
			'Hours'
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
			var d = new Date();
			d.setHours(hours);
			d.setMinutes(minutes);
			$scope.cronDetails.nextCronTime = d;

			$scope.cronDetailsMod = jQuery.extend(true, {}, $scope.cronDetails); // deep copy
			var year = $scope.cronDetailsMod.nextCronDate.split("-")[0];
			var month = parseInt($scope.cronDetailsMod.nextCronDate.split("-")[1]) - 1;
			var day = parseInt($scope.cronDetailsMod.nextCronDate.split("-")[2]) + 1;
			$scope.cronDetailsMod.nextCronDate = new Date(Date.UTC(year, month, day));
		}).catch(function(response) {
			console.error('Error occurred getting cron details:', response.status, response.data);
		});

		// Ajax call when cron details are submitted
		$scope.submitCronChange = function () {

			if ($scope.checkForm()) {
				// Convert date formats of all datetime fields
				$scope.cronDetailsMod.nextCronDate = moment($scope.cronDetailsMod.nextCronDate).format("YYYY-MM-DD");
				$scope.cronDetailsMod.nextCronTime = moment($scope.cronDetailsMod.nextCronTime).format("HH:mm");

				$.ajax({
					type: "POST",
					url: "php/cron/update.cron.php",
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
						}).catch(function(response) {
							console.error('Error occurred getting cron details:', response.status, response.data);
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

	});