angular.module('opalAdmin.controllers.patientRegistration', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'pascalprecht.translate']).


	/******************************************************************************
	* Patient Registration Page controller 
	*******************************************************************************/
	controller('patientRegistration', function ($scope, $filter, $sce, $state, $uibModal, patientCollectionService, $translate, $rootScope) {

		$scope.ssnHtmlInstruction = $filter('translate')('PATIENT_REGISTRATION_SEARCH_DESCRIPTION');

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Initialize firebase object
		if (!firebase.apps.length) {
			firebase.initializeApp($rootScope.firebaseConfig);
		}

		// default booleans
		$scope.emailSection = {open:false, show:false};
		$scope.passwordSection = {open:false, show:false};
		$scope.languageSection = {open:false, show:false};
		$scope.cellNumberSection = {open:false, show:false};
		$scope.securityQuestion1Section = {open:false, show:false};
		$scope.securityQuestion2Section = {open:false, show:false};
		$scope.securityQuestion3Section = {open:false, show:false};
		$scope.accessLevelSection = {open:false, show:false};
		$scope.finalCheckSection = {open:false, show: false};

		// completed registration steps in object notation
		var defaultSteps = {
			email: { completed: false },
			password: { completed: false },
			language: { completed: false },
			security1: { completed: false },
			security2: { completed: false },
			security3: { completed: false },
			access: {completed: false},
			checks: { completed: false }
		};
		var steps = jQuery.extend(true, {}, defaultSteps);

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 8;

		// Progress bar based on default completed steps and total
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

		$scope.bannerMessage = "";
		// Function to show page banner 
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 5000);
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

		// Initialize new patient object
		$scope.defaultNewPatient = {
			email: null,
			confirmEmail: null,
			password: null,
			confirmPassword: null,
			language: null,
			cellNum: null,
			securityQuestion1: { serial: null, answer: null },
			securityQuestion2: { serial: null, answer: null },
			securityQuestion3: { serial: null, answer: null },
			accessLevel: null,
			SSN: null,
			data: null
		};
		$scope.newPatient = jQuery.extend(true, {}, $scope.defaultNewPatient);


		// Function to reset newPatient object
		$scope.flushNewPatient = function () {

			if (!$scope.validSSN.SSN)
				$scope.validSSN.status = null;

			$scope.newPatient = jQuery.extend(true, {}, $scope.defaultNewPatient);
			steps = jQuery.extend(true, {}, defaultSteps);

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		};

		// Initialize list 
		$scope.securityQuestions = [];

		// Keep track of SSN status and input
		$scope.validSSN = {
			status: null,
			SSN: null,
			message: null
		};

		$scope.validateSSN = function (ssn) {
			$scope.validPatientSearch = null;
			if (!ssn) {
				$scope.validSSN.status = null;
				return;
			}

			else if (ssn.length < 12) {
				$scope.validSSN.status = 'invalid'; // input not long enough
				$scope.validSSN.message = $filter('translate')('STATUS_SSN_ERROR');
				return;
			}

			else {
				$scope.validSSN.status = 'valid';
				$scope.validSSN.message = null;

			}
		};

		$scope.accordionOpen = true;

		// Keep track of Patient ID status input
		$scope.validPatientId = {
			status: null,
			id: null,
			message: null
		};

		$scope.validatePatientId = function (id) {
			$scope.validPatientSearch = null;
			if (!id) {
				$scope.validPatientId.status = null;
				return;
			}

			else {
				$scope.validPatientId.status = 'valid';
				return;
			}
		};

		$scope.validSearchForm = function () {
			if ($scope.validSSN.status == 'valid' && $scope.validPatientId.status == 'valid') {
				return true;
			}
			else
				return false;
		};

		$scope.validPatientSearch = null;
		$scope.validatePatientSearch = function () {

			if ($scope.validSearchForm()) {
				// Call our API service to find patient 
				patientCollectionService.findPatient($scope.validSSN.SSN, $scope.validPatientId.id).then(function (response) {

					if (response.data.status == 'PatientAlreadyRegistered') {
						$scope.validSSN.status = 'warning';
						$scope.validSSN.message = $filter('translate')('STATUS_PATIENT_EXISTS');
						$scope.validPatientSearch = 'warning';
					}
					else if (response.data.status == 'PatientNotFound') {
						$scope.validSSN.status = 'invalid';
						$scope.validSSN.message = $filter('translate')('STATUS_PATIENT_NOT_FOUND');
						$scope.validPatientSearch = 'invalid';
					}
					else if (response.data.status == 'Error') {
						$scope.validSSN.status = 'invalid';
						$scope.validSSN.message = $filter('translate')('STATUS_MISC_ERROR') + ": " + response.data.message;
						$scope.validPatientSearch = 'invalid';
					}
					else {
						$scope.validSSN.status = 'valid';
						$scope.validSSN.message = $filter('translate')('STATUS_PATIENT_FOUND');
						$scope.validPatientSearch = 'valid';

						$scope.newPatient.data = response.data.data; // Assign data

						$scope.accordionOpen = false;

						// Call our API service to get security questions 
						patientCollectionService.fetchSecurityQuestions($rootScope.siteLanguage).then(function (response) {
							$scope.securityQuestions = response.data;
						}).catch(function(response) {
							console.error('Error occurred fetching security questions:', response.status, response.data);
						});

					}

				}).catch(function(response) {
					console.error('Error occurred verifying patient:', response.status, response.data);
				});
			}


		};

		// Function to validate email address
		$scope.validEmail = { status: null, message: null };
		$scope.validateEmail = function (email) {

			if (!email) {
				$scope.validEmail.status = null;
				$scope.emailUpdate();
				return;
			}
			// regex
			var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
			if (!re.test(email)) {
				$scope.validEmail.status = 'invalid';
				$scope.validEmail.message = $filter('translate')('STATUS_INVALID_EMAIL_FORMAT');
				$scope.emailUpdate();
				return;
			} else {

				// Make request to check if email already in use
				patientCollectionService.emailAlreadyInUse(email).then(function (response) {
					if (response.data == 'TRUE') {
						$scope.validEmail.status = 'warning';
						$scope.validEmail.message = $filter('translate')('STATUS_EMAIL_IN_USE');
						$scope.emailUpdate();
						return;
					} else if (response.data == 'FALSE') {
						$scope.validEmail.status = 'valid';
						$scope.validEmail.message = null;
						$scope.emailUpdate();
						return;
					} else {
						$scope.validEmail.status = 'invalid';
						$scope.validEmail.message = $filter('translate')('STATUS_MISC_ERROR');
						$scope.emailUpdate();
					}

				}).catch(function(response) {
					console.error('Error occurred verifying email:', response.status, response.data);
				});

			}
		};

		// Function to validate password 
		$scope.validPassword = { status: null, message: null };
		$scope.validatePassword = function (password) {

			// Password must be at least 6 characters long
			if (password.length < 6) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = $filter('translate')('STATUS_PASSWORD_CHARACTER_ERROR');
				$scope.passwordUpdate();
				return;
			} 

			// Password must contain at least one capital letter and one number
			var capitalRegex = /[A-Z]/;
			var numberRegex = /\d/g;
			if (!capitalRegex.test(password) || !numberRegex.test(password)) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = $filter('translate')('STATUS_PASSWORD_CAP_NUM_ERROR');
				$scope.passwordUpdate();
			 	return;
			}

			else {
				$scope.validPassword.status = 'valid';
				$scope.validPassword.message = null;
				$scope.passwordUpdate();
			}
		};

		// Function to validate confirm password
		$scope.validConfirmPassword = { status: null, message: null };
		$scope.validateConfirmPassword = function (confirmPassword) {

			if (!confirmPassword) {
				$scope.validConfirmPassword.status = null;
				$scope.passwordUpdate();
				return;
			}

			if ($scope.validPassword.status != 'valid' || $scope.newPatient.password != $scope.newPatient.confirmPassword) {
				$scope.validConfirmPassword.status = 'invalid';
				$scope.validConfirmPassword.message = $filter('translate')('STATUS_PASSWORD_CONFIRM_ERROR');
				$scope.passwordUpdate();
				return;
			} else {
				$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
				$scope.passwordUpdate();
			}
		};

		// Initialize a list of languages available
		$scope.languages = [{
			name: $filter('translate')('ENGLISH'),
			id: 'EN'
		}, {
			name: $filter('translate')('FRENCH'),
			id: 'FR'
		}];

		// Initialize a list of access levels
		$scope.accessLevels = [{
			name: $filter('translate')('ACCESS_LEVEL_1'),
			id: 1
		}, {
			name: $filter('translate')('ACCESS_LEVEL_3'),
			id: 3
		}];

		// Keep track of cellNum status 
		$scope.validCellNum = { status: null, message: null };
		$scope.validateCellNum = function (cellNum) {
			if (!cellNum) {
				$scope.validCellNum.status = null;
				return;
			}

			// regex (digits only)
			var re = /^\d+$/;
			if (!re.test(cellNum)) {
				$scope.validCellNum.status = 'invalid';
				$scope.validCellNum.message = $filter('translate')('STATUS_INVALID_FORMAT');
				return;
			}

			if (cellNum.length != 10) {
				$scope.validCellNum.status = 'invalid';
				$scope.validCellNum.message = $filter('translate')('STATUS_CELLNUM_ERROR');
				return;
			} else {
				$scope.validCellNum.status = 'valid';
				$scope.validCellNum.message = null;
			}

		};

		// Function to validate security question answer 1
		$scope.validAnswer1 = { status: null, message: null };
		$scope.validateAnswer1 = function (answer) {
			if (!answer) {
				$scope.validAnswer1.status = null;
				$scope.securityQuestion1Update();
				return;
			}

			// regex (no special characters)
			var re = /^[a-zA-Z0-9\s]*$/;
			if (!re.test(answer)) {
				$scope.validAnswer1.status = 'invalid';
				$scope.validAnswer1.message = $filter('translate')('STATUS_SECURITY_QUESTION_ERROR');
				$scope.securityQuestion1Update();
				return;
			} else {
				$scope.validAnswer1.status = 'valid';
				$scope.validAnswer1.message = null;
				$scope.securityQuestion1Update();
			}
		};

		// Function to validate security question answer 2
		$scope.validAnswer2 = { status: null, message: null };
		$scope.validateAnswer2 = function (answer) {
			if (!answer) {
				$scope.validAnswer2.status = null;
				$scope.securityQuestion2Update();
				return;
			}

			// regex (no special characters)
			var re = /^[a-zA-Z0-9\s]*$/;
			if (!re.test(answer)) {
				$scope.validAnswer2.status = 'invalid';
				$scope.validAnswer2.message = $filter('translate')('STATUS_SECURITY_QUESTION_ERROR');
				$scope.securityQuestion2Update();
				return;
			} else {
				$scope.validAnswer2.status = 'valid';
				$scope.validAnswer2.message = null;
				$scope.securityQuestion2Update();
			}
		};

		// Function to validate security question answer 3
		$scope.validAnswer3 = { status: null, message: null };
		$scope.validateAnswer3 = function (answer) {
			if (!answer) {
				$scope.validAnswer3.status = null;
				$scope.securityQuestion3Update();
				return;
			}

			// regex (no special characters)
			var re = /^[a-zA-Z0-9\s]*$/;
			if (!re.test(answer)) {
				$scope.validAnswer3.status = 'invalid';
				$scope.validAnswer3.message = $filter('translate')('STATUS_SECURITY_QUESTION_ERROR');
				$scope.securityQuestion3Update();
				return;
			} else {
				$scope.validAnswer3.status = 'valid';
				$scope.validAnswer3.message = null;
				$scope.securityQuestion3Update();
			}
		};

		// Function to filter question based on question 1 field
		$scope.filterFromQ1 = function (question) {
			return (question.serial != $scope.newPatient.securityQuestion1.serial);
		};
		// Function to filter question based on question 2 field
		$scope.filterFromQ2 = function (question) {
			//console.log($scope.newPatient.securityQuestion2)
			return (question.serial != $scope.newPatient.securityQuestion2.serial);
		};
		// Function to filter question based on question 3 field
		$scope.filterFromQ3 = function (question) {
			return (question.serial != $scope.newPatient.securityQuestion3.serial);
		};

		// Function to toggle steps when updating the email field
		$scope.emailUpdate = function () {
			if ($scope.validEmail.status == 'valid') {
				steps.email.completed = true;
				$scope.passwordSection.show = true;
			}
			else
				steps.email.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};
		// Function to toggle steps when updating the password field
		$scope.passwordUpdate = function () {
			if ($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid') {
				steps.password.completed = true;
				$scope.languageSection.show = true;
			}
			else
				steps.password.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};
		// Function to toggle steps when updating the language field
		$scope.languageUpdate = function () {
			if ($scope.newPatient.language) {
				steps.language.completed = true;
				$scope.cellNumberSection.show = true;
				$scope.securityQuestion1Section.show = true;
				$scope.securityQuestion2Section.show = true;
				$scope.securityQuestion3Section.show = true;
			}
			else
				steps.language.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};
		// Function to toggle steps when updating the security question 1 field
		$scope.securityQuestion1Update = function () {
			if ($scope.validAnswer1.status == 'valid')
				steps.security1.completed = true;
			else
				steps.security1.completed = false;

			$scope.checkAllSecurityQuestions();
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};
		// Function to toggle steps when updating the security question 2 field
		$scope.securityQuestion2Update = function () {
			if ($scope.validAnswer2.status == 'valid')
				steps.security2.completed = true;
			else
				steps.security2.completed = false;

			$scope.checkAllSecurityQuestions();
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};
		// Function to toggle steps when updating the security question 3 field
		$scope.securityQuestion3Update = function () {
			if ($scope.validAnswer3.status == 'valid')
				steps.security3.completed = true;
			else
				steps.security3.completed = false;

			$scope.checkAllSecurityQuestions();
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to check all security questions
		$scope.checkAllSecurityQuestions = function () {
			if ($scope.validAnswer1.status == 'valid' && $scope.validAnswer2.status == 'valid' &&
				$scope.validAnswer3.status == 'valid') {
				$scope.accessLevelSection.show = true;
				$scope.finalCheckSection.show = true;
			}
		}
		// Function to toggle steps when updating the access level field
		$scope.accessLevelUpdate = function () {
			if ($scope.newPatient.accessLevel) {
				steps.access.completed = true;
				$scope.finalCheckSection.show = true;
			}
			else
				steps.access.completed = false;

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to check if all final checks have been checked
		$scope.idCheck = false;
		$scope.idCheckUpdate = function () {
			$scope.idCheck = !$scope.idCheck;
			$scope.finalCheckUpdate();
		}
		$scope.consentCheck = false;
		$scope.consentCheckUpdate = function() {
			$scope.consentCheck = !$scope.consentCheck;
			$scope.finalCheckUpdate();
		}
		$scope.disclaimerCheck = false;
		$scope.disclaimerCheckUpdate = function () {
			$scope.disclaimerCheck = !$scope.disclaimerCheck;
			$scope.finalCheckUpdate();
		}
		$scope.slaCheck = false;
		$scope.slaCheckUpdate = function () {
			$scope.slaCheck = !$scope.slaCheck;
			$scope.finalCheckUpdate();
		}
		$scope.opalDocCheck = false;
		$scope.opalDocCheckUpdate = function () {
			$scope.opalDocCheck = !$scope.opalDocCheck;
			$scope.finalCheckUpdate();
		}
		$scope.finalChecks = false;
		$scope.finalCheckUpdate = function () {
			if ($scope.idCheck && $scope.consentCheck && $scope.disclaimerCheck && $scope.slaCheck && $scope.opalDocCheck) {
				steps.checks.completed = true;
				$scope.finalChecks = true;
			}
			else {
				steps.checks.completed = false;
				$scope.finalChecks = false;
			}

			$scope.numOfCompletedSteps = stepsCompleted(steps);
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to check registration form completion 
		$scope.checkRegistrationForm = function () {

			if ($scope.stepProgress == 100)
				return true;
			else
				return false;
		};


		// Function to register patient
		$scope.registerPatient = function () {

			if ($scope.checkRegistrationForm()) {

				var modalInstance = $uibModal.open({
					templateUrl: 'templates/patient/confirm.patient-registration.html',
					controller: 'patientRegistration.confirm',
					scope: $scope,
					backdrop: 'static'
				});

				// After proper credentials, proceed with registration
				modalInstance.result.then(function () {

					firebase.auth().createUserWithEmailAndPassword($scope.newPatient.email, $scope.newPatient.password).then(function (userData) {

						// on success, register to our database
						$scope.newPatient.uid = userData.uid;
						$scope.newPatient.SSN = $scope.validSSN.SSN;
						$scope.newPatient.password = CryptoJS.SHA512($scope.newPatient.password).toString();
						$scope.newPatient.securityQuestion1.answer = CryptoJS.SHA512($scope.newPatient.securityQuestion1.answer.toUpperCase()).toString();
						$scope.newPatient.securityQuestion2.answer = CryptoJS.SHA512($scope.newPatient.securityQuestion2.answer.toUpperCase()).toString();
						$scope.newPatient.securityQuestion3.answer = CryptoJS.SHA512($scope.newPatient.securityQuestion3.answer.toUpperCase()).toString();

						// submit form
						$.ajax({
							type: "POST",
							url: "php/patient/insert.patient.php",
							data: $scope.newPatient,
							success: function () {
								$state.go('home');
							}
						});

					}, function (error) {
						// Handle errors
						var errorCode = error.code;
						var errorMessage = error.message;
						if (errorCode == 'auth/email-already-in-use') {

							$scope.validEmail.status = 'warning';
							$scope.validEmail.message = $filter('translate')('STATUS_EMAIL_IN_USE');
							$scope.emailUpdate();
							$scope.$apply();

						}
						else if (errorCode == 'auth/weak-password') {

							$scope.validPassword.status = 'invalid';
							$scope.validPassword.message = $filter('translate')('STATUS_WEAK_PASSWORD');
							$scope.passwordUpdate();
							$scope.$apply();

						}

					});
				});

			}
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


