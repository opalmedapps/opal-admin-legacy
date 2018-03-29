angular.module('opalAdmin.controllers.hospitalMap.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* New Hospital Map Page controller 
	*******************************************************************************/
	controller('hospitalMap.add', function ($scope, $filter, $state, $sce, $uibModal, hospitalMapCollectionService, Session) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default toolbar for wysiwyg
		$scope.toolbar = [ 
			['h1', 'h2', 'h3', 'p'],
      		['bold', 'italics', 'underline', 'ul', 'ol'],
      		['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
      		['html', 'insertLink']
      	];

		// Default boolean 
		$scope.titleDescriptionSection = {open: false, show: true};
		$scope.urlSection = {open: false, show: false};
		$scope.qrSection = {open: false, show: false};

		// completed steps boolean object; used for progress bar
		var steps = {
			title_description: { completed: false },
			url: { completed: false },
			qrid: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 3;

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
			url_EN: "",
			url_FR: ""
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

				$scope.urlSection.show = true;

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

		// Function to toggle necessary changes when updating URLs
		$scope.urlUpdate = function () {

			$scope.urlSection.open = true;

			if ($scope.newHosMap.url_EN && $scope.newHosMap.url_FR) {

				$scope.qrSection.show = true;

				// Toggle step completion
				steps.url.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle step completion
				steps.url.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating qrid
		$scope.qridUpdate = function () {

			$scope.qrSection.open = true;

			if ($scope.newHosMap.qrid && $scope.newHosMap.qrcode) {
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
		$scope.showMapDisplay_EN = false;
		$scope.showMapDisplay_FR = false;
		$scope.mapURL_EN = "";
		$scope.mapURL_FR = "";
		$scope.showMap = function (url, language) {
			if (language == 'EN') {
				$scope.showMapDisplay_EN = true;
				$scope.mapURL_EN = url;
			} 
			else if (language == 'FR') {
				$scope.showMapDisplay_FR = true;
				$scope.mapURL_FR = url;
			}
		};

		// Function to submit the new hospital map
		$scope.submitHosMap = function () {
			if ($scope.checkForm()) {

				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.newHosMap.description_EN = $scope.newHosMap.description_EN.replace(/\u200B/g,'');
				$scope.newHosMap.description_FR = $scope.newHosMap.description_FR.replace(/\u200B/g,'');

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
