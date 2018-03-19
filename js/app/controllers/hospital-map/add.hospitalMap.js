angular.module('opalAdmin.controllers.hospitalMap.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* New Hospital Map Page controller 
	*******************************************************************************/
	controller('hospitalMap.add', function ($scope, $filter, $state, $sce, $uibModal, hospitalMapCollectionService, Session) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default boolean 
		$scope.titleDescriptionSection = {open: false, show: true};
		$scope.qrUrlSection = {open: false, show: false};

		// completed steps boolean object; used for progress bar
		var steps = {
			title_description: { completed: false },
			qrid: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 2;

		// Progress for progress bar on default steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Function to calculate / return step progress
		function trackProgress(value, total) {
			return Math.round(100 * value / total);
		}

		// Function to return number of steps completed
		function stepsCompleted(steps) {

			var numberOfTrues = 0;
			for (var step in steps) {
				if (steps[step].completed === true) {
					numberOfTrues++;
				}
			}

			return numberOfTrues;
		}

		// Initialize the new hospital map object
		$scope.newHosMap = {
			name_EN: "",
			name_FR: "",
			description_EN: "",
			description_FR: "",
			qrid: "",
			qrcode: "",
			qrpath: "",
			url: ""
		};

		$scope.oldqrid = "";

		// Function to toggle necessary changes when updating title and description
		$scope.titleDescriptionUpdate = function () {

			$scope.titleDescriptionSection.open = true;

			if (!$scope.newHosMap.name_EN && !$scope.newHosMap.name_FR &&
				!$scope.newHosMap.description_EN && !$scope.newHosMap.description_FR) {
				$scope.titleDescriptionSection.open = false;
			}

			if ($scope.newHosMap.name_EN && $scope.newHosMap.name_FR &&
				$scope.newHosMap.description_EN && $scope.newHosMap.description_FR) {

				$scope.qrUrlSection.show = true;

				// Toggle step completion
				steps.title_description.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			
			} else {

				// Toggle step completion
				steps.title_description.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating qrid and URL
		$scope.qridUpdate = function () {

			$scope.qrUrlSection.open = true;

			if ($scope.newHosMap.qrid && $scope.newHosMap.qrcode && $scope.newHosMap.url) {
				// Toggle step completion
				steps.qrid.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle step completion
				steps.qrid.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to call api to generate qr code
		$scope.generateQRCode = function (qrid) {

			if (qrid) {
				hospitalMapCollectionService.generateQRCode(qrid, $scope.oldqrid).then(function (response) {
					$scope.newHosMap.qrcode = response.data.qrcode;
					$scope.newHosMap.qrpath = response.data.qrpath;

					$scope.oldqrid = qrid;
					$scope.qridUpdate();
				}).catch(function(response) {
					console.error('Error occurred generating QR code:', response.status, response.data);
				});
			}
			else {
				$scope.hosMap.qrcode = "";
				$scope.hosMap.qrpath = "";
			}

		};

		// Function to show map
		$scope.showMapDisplay = false;
		$scope.mapURL = "";
		$scope.showMap = function (url) {
			$scope.showMapDisplay = true;
			$scope.mapURL = url;
		};

		// Function to submit the new hospital map
		$scope.submitHosMap = function () {
			if ($scope.checkForm()) {
				// Log who created hospital map
				var currentUser = Session.retrieveObject('user');
				$scope.newHosMap.user = currentUser;
				// Submit
				$.ajax({
					type: "POST",
					url: "php/hospital-map/insert.hospital_map.php",
					data: $scope.newHosMap,
					success: function () {
						$state.go('hospital-map');
					}
				});
			}
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {
			if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
				return true;
			else
				return false;
		};

		var fixmeTop = $('.summary-fix').offset().top;
		$(window).scroll(function() {
		    var currentScroll = $(window).scrollTop();
		    if (currentScroll >= fixmeTop) {
		        $('.summary-fix').css({
		            position: 'fixed',
		            top: '0',
		          	width: '15%'
		        });
		    } else {
		        $('.summary-fix').css({
		            position: 'static',
		            width: ''
		        });
		    }
		});

		var fixMeMobile = $('.mobile-side-panel-menu').offset().top;
		$(window).scroll(function() {
		    var currentScroll = $(window).scrollTop();
		    if (currentScroll >= fixMeMobile) {
		        $('.mobile-side-panel-menu').css({
		            position: 'fixed',
		            top: '50px',
		            width: '100%',
		            zIndex: '100',
		            background: '#6f5499',
		            boxShadow: 'rgba(93, 93, 93, 0.6) 0px 3px 8px -3px'
		          	
		        });
		        $('.mobile-summary .summary-title').css({
		        	color: 'white'
		        });
		    } else {
		        $('.mobile-side-panel-menu').css({
		            position: 'static',
		            width: '',
		            background: '',
		            boxShadow: ''
		        });
		         $('.mobile-summary .summary-title').css({
		        	color: '#6f5499'
		        });
		    }
		});


	});
