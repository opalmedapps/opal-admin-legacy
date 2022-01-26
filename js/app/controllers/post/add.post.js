angular.module('opalAdmin.controllers.post.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'textAngular']).


// Function to accept/trust html (styles, classes, etc.)
filter('deliberatelyTrustAsHtml', function ($sce) {
	return function (text) {
		return $sce.trustAsHtml(text);
	};
}).
/******************************************************************************
 * Add Post Page controller
 *******************************************************************************/
controller('post.add', function ($scope, $filter, $state, $sce, $uibModal, $locale, Session, ErrorHandler) {

	// Function to go to previous page
	$scope.goBack = function () {
		window.history.back();
	};

	// Default boolean variables
	$scope.typeSection = {open:false, show:true};
	$scope.titleSection = {open:false, show:false};
	$scope.bodySection = {open:false, show:false};
	$scope.publishSection = {open:false, show:false};


	// completed steps boolean object; used for progress bar
	var steps = {
		title: { completed: false },
		body: { completed: false },
		type: { completed: false }
	};

	$scope.language = Session.retrieveObject('user').language;

	// Default count of completed steps
	$scope.numOfCompletedSteps = 0;

	// Default total number of steps
	$scope.stepTotal = 3;

	// Progress for progress bar on default steps and total
	$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

	// Initialize the list of post types
	$scope.postTypes = [
		{
			name: 'Announcement',
			name_display: $filter('translate')('POSTS.ADD.ANNOUNCEMENT'),
			icon: 'bullhorn'
		},
		{
			name: 'Treatment Team Message',
			name_display: $filter('translate')('POSTS.ADD.TREATMENT_TEAM_MESSAGE'),
			icon: 'user-md'
		},
		{
			name: 'Patients for Patients',
			name_display: $filter('translate')('POSTS.ADD.PATIENTS_FOR_PATIENTS'),
			icon: 'users'
		}
	];

	// Initialize the new post object
	$scope.newPost = {
		OAUser: Session.retrieveObject('user'),
		name_EN: null,
		name_FR: null,
		type: null,
		body_EN: null,
		body_FR: null,
	};

	/* Function for the "Processing..." dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};

	$scope.formLoaded = false;
	// Function to load form as animations
	$scope.loadForm = function () {
		$('.form-box-left').addClass('fadeInDown');
		$('.form-box-right').addClass('fadeInRight');
	};

	$scope.removeNonprintableChars = function(value) {
		return value.replace(/[\u0000-\u0008,\u000A-\u001F,\u007F-\u00A0,\u200B]+/g, "");
	};

	// Function to toggle necessary changes when updating post name
	$scope.titleUpdate = function () {
		$scope.titleSection.open = true;
		if ($scope.newPost.name_EN && $scope.newPost.name_FR) {
			$scope.bodySection.show = true;
			steps.title.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		} else {
			steps.title.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	// Function to toggle necessary changes when updating the post body
	$scope.bodyUpdate = function () {

		$scope.bodySection.open = true;
		if ($scope.newPost.body_EN && $scope.newPost.body_FR) {
			$scope.publishSection.show = true;
			steps.body.completed = true;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		} else {
			steps.body.completed = false;
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		}
	};

	// Function to toggle necessary changes when updating the post type
	$scope.typeUpdate = function (type) {
		$scope.newPost.type = type;
		steps.type.completed = true;
		$scope.titleSection.show = true;
		$scope.typeSection.open = true;
		$scope.numOfCompletedSteps = stepsCompleted(steps);
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
	};

	// Function to submit the new post
	$scope.submitPost = function () {
		if ($scope.checkForm()) {
			$scope.newPost.type = $scope.newPost.type.name;
			$scope.newPost.body_EN = $scope.newPost.body_EN.replace(/\u200B/g,'');
			$scope.newPost.body_FR = $scope.newPost.body_FR.replace(/\u200B/g,'');
			$.ajax({
				type: "POST",
				url: "post/insert/post",
				data: $scope.newPost,
				success: function () {},
				error: function(err) {
					ErrorHandler.onError(err, $filter('translate')('POSTS.ADD.ERROR_ADD'));
				},
				complete: function () {
					$state.go('post');
				}
			});
		}
	};

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

	// Function to return boolean for form completion
	$scope.checkForm = function () {
		return (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) === 100);
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

