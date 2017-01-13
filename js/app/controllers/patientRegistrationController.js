angular.module('ATO_InterfaceApp.controllers.patientRegistrationController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Patient Registration Page controller 
	*******************************************************************************/
	controller('patientRegistrationController', function($scope, $filter, $sce, $uibModal, patientAPIservice) {

        // Function to go to previous page
        $scope.goBack = function() {
            window.history.back();
        }

        // completed registration steps in object notation
        var defaultSteps = {
            email: {completed: false},
            password: {completed: false},
            language: {completed: false},
            security1: {completed: false},
            security2: {completed: false},
            security3: {completed: false}
        }
        var steps = jQuery.extend(true, {}, defaultSteps);

        // Default count of completed steps
        $scope.numOfCompletedSteps = 0;

        // Default total number of steps 
        $scope.stepTotal = 6;

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
                if (steps[step].completed == true) {
                    numberOfTrues++;
                }
            }

            return numberOfTrues;
        }
    
        $scope.bannerMessage = "";
        // Function to show page banner 
        $scope.showBanner = function() {
            $(".bannerMessage").slideDown(function()  {
                setTimeout(function() {             
                    $(".bannerMessage").slideUp(); 
                }, 5000); 
            });
        }
        // Function to set banner class
        $scope.setBannerClass = function(classname) {
            // Remove any classes starting with "alert-" 
            $(".bannerMessage").removeClass (function (index, css) {
                return (css.match (/(^|\s)alert-\S+/g) || []).join(' ');
            });
            // Add class
            $(".bannerMessage").addClass('alert-'+classname);
        };

        // Initialize new patient object
        $scope.defaultNewPatient = {
            email: null,
            confirmEmail: null,
            password: null,
            confirmPassword: null,
            language: null,
            cellNum: null,
            securityQuestion1: {serial:null,answer:null},
            securityQuestion2: {serial:null,answer:null},
            securityQuestion3: {serial:null,answer:null},
            SSN: null,
            data: null
        }
        $scope.newPatient = jQuery.extend(true, {}, $scope.defaultNewPatient);


        // Function to reset newPatient object
        $scope.flushNewPatient = function() {

            $scope.newPatient = jQuery.extend(true, {}, $scope.defaultNewPatient);
            steps = jQuery.extend(true, {}, defaultSteps);

            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        
        }

        // Initialize list 
        $scope.securityQuestions = [];

        // Keep track of SSN status and input
        $scope.validSSN = {
            status: null,
            SSN: null,
            message: null
        };

        $scope.validateSSN = function(ssn) {

            if(ssn.length < 12) {
                $scope.validSSN.status = 'invalid' // input not long enough
                $scope.validSSN.message = 'Must be greater than 12 characters'
                return;
            }

            // Call our API service to find patient 
            patientAPIservice.findPatient(ssn).success(function(response) {

                if(response.status == 'PatientAlreadyRegistered') {
                    $scope.validSSN.status = 'warning';
                    $scope.validSSN.message = 'Patient is already registered!';
                }
                else if(response.status == 'PatientNotFound') {
                    $scope.validSSN.status = 'invalid';
                    $scope.validSSN.message = 'No patient found!';
                }
                else if(response.status == 'Error') {
                    $scope.validSSN.status = 'invalid';
                    $scope.validSSN.message = 'Something went wrong: ' + response.message;
                }
                else {
                    $scope.validSSN.status = 'valid';
                    $scope.validSSN.message = 'Patient found!'

                    $scope.newPatient.data = response.data; // Assign data

                    // Call our API service to get security questions 
                    patientAPIservice.fetchSecurityQuestions().success(function (response) {
                        $scope.securityQuestions = response;
                    });

                }

            });
            

        }

        // Function to validate email address
        $scope.validEmail = {status:null,message:null};
        $scope.validateEmail = function (email) {

            if (!email) {
                $scope.validEmail.status = null;
                $scope.emailUpdate();
                return;
            }
            // regex
            var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
            if(!re.test(email)){
                $scope.validEmail.status = 'invalid';
                $scope.validEmail.message = 'Invalid email format';
                $scope.emailUpdate();
                return;
            } else {
  
                // Make request to check if email already in use
                patientAPIservice.emailAlreadyInUse(email).success(function(response) {
                    if(response == 'TRUE') {
                        $scope.validEmail.status = 'warning';
                        $scope.validEmail.message = 'Email already in use';
                        $scope.emailUpdate();
                        return;
                    } else if (response == 'FALSE') {
                        $scope.validEmail.status = 'valid';
                        $scope.validEmail.message = null;
                        $scope.emailUpdate();
                        return;
                    } else {
                        $scope.validEmail.status = 'invalid';
                        $scope.validEmail.message = 'Something went wrong';
                        $scope.emailUpdate();
                    }

                });

            }
        }

        // Function to validate password 
        $scope.validPassword = {status:null,message:null};
        $scope.validatePassword = function (password) {

            if (!password) {
                $scope.validPassword.status = null;
                $scope.passwordUpdate();
                return;
            }

            if (password.length < 6) {
                $scope.validPassword.status = 'invalid';
                $scope.validPassword.message = 'Use greater than 6 characters';
                $scope.passwordUpdate();
                return;
            } else {
                $scope.validPassword.status = 'valid';
                $scope.validPassword.message = null;
                $scope.passwordUpdate();
            }
        }

        // Function to validate confirm password
        $scope.validConfirmPassword = {status:null,message:null};
        $scope.validateConfirmPassword = function (confirmPassword) {

            if (!confirmPassword) {
                $scope.validConfirmPassword.status = null;
                $scope.passwordUpdate();
                return;
            }

            if ($scope.validPassword.status != 'valid' || $scope.newPatient.password != $scope.newPatient.confirmPassword) {
                $scope.validConfirmPassword.status = 'invalid';
                $scope.validConfirmPassword.message = 'Enter same valid password';
                $scope.passwordUpdate();
                return;
            } else {
                $scope.validConfirmPassword.status = 'valid';
                $scope.validConfirmPassword.message = null;
                $scope.passwordUpdate();
            }
        }

        // Initialize a list of languages available
        $scope.languages = [{
            name: 'English',
            id: 'EN'
        }, {
            name: 'French',
            id: 'FR'
        }];

        // Keep track of cellNum status 
        $scope.validCellNum = {status: null, message:null};
        $scope.validateCellNum = function (cellNum) {
            if(!cellNum) {
                $scope.validCellNum.status = null;
                return;
            }

            // regex (digits only)
            var re = /^\d+$/;
            if (!re.test(cellNum)) {
                $scope.validCellNum.status = 'invalid';
                $scope.validCellNum.message = 'Invalid format'
                return;
            }

            if (cellNum.length != 10) {
                $scope.validCellNum.status = 'invalid';
                $scope.validCellNum.message = 'Must be 10 digits long'
                return;
            } else {
                $scope.validCellNum.status = 'valid';
                $scope.validCellNum.message = null;
            }
            
        }

        // Function to validate security question answer 1
        $scope.validAnswer1 = {status: null, message:null};
        $scope.validateAnswer1 = function(answer) {
            if (!answer) {
                $scope.validAnswer1.status = null;
                $scope.securityQuestion1Update();
                return;
            }

            // regex (no special characters)
            var re = /^[a-zA-Z0-9\s]*$/;
            if (!re.test(answer)) {
                $scope.validAnswer1.status = 'invalid';
                $scope.validAnswer1.message = 'No special characters';
                $scope.securityQuestion1Update();
                return;
            } else {
                $scope.validAnswer1.status = 'valid';
                $scope.validAnswer1.message = null;
                $scope.securityQuestion1Update();
            }
        }

         // Function to validate security question answer 2
        $scope.validAnswer2 = {status: null, message:null};
        $scope.validateAnswer2 = function(answer) {
            if (!answer) {
                $scope.validAnswer2.status = null;
                $scope.securityQuestion2Update();
                return;
            }

            // regex (no special characters)
            var re = /^[a-zA-Z0-9\s]*$/;
            if (!re.test(answer)) {
                $scope.validAnswer2.status = 'invalid';
                $scope.validAnswer2.message = 'No special characters';
                $scope.securityQuestion2Update();
                return;
            } else {
                $scope.validAnswer2.status = 'valid';
                $scope.validAnswer2.message = null;
                $scope.securityQuestion2Update();
            }
        }

         // Function to validate security question answer 3
        $scope.validAnswer3 = {status: null, message:null};
        $scope.validateAnswer3 = function(answer) {
            if (!answer) {
                $scope.validAnswer3.status = null;
                $scope.securityQuestion3Update();
                return;
            }

            // regex (no special characters)
            var re = /^[a-zA-Z0-9\s]*$/;
            if (!re.test(answer)) {
                $scope.validAnswer3.status = 'invalid';
                $scope.validAnswer3.message = 'No special characters';
                $scope.securityQuestion3Update();
                return;
            } else {
                $scope.validAnswer3.status = 'valid';
                $scope.validAnswer3.message = null;
                $scope.securityQuestion3Update();
            }
        }

        // Function to filter question based on question 1 field
        $scope.filterFromQ1 = function(question) {
            return (question.serial != $scope.newPatient.securityQuestion1.serial)
        }
        // Function to filter question based on question 2 field
        $scope.filterFromQ2 = function(question) {
            //console.log($scope.newPatient.securityQuestion2)
            return (question.serial != $scope.newPatient.securityQuestion2.serial)
        }
        // Function to filter question based on question 3 field
        $scope.filterFromQ3 = function(question) {
            return (question.serial != $scope.newPatient.securityQuestion3.serial)
        }

        // Function to toggle steps when updating the email field
        $scope.emailUpdate = function() {
            if($scope.validEmail.status == 'valid')
                steps.email.completed = true;
            else
                steps.email.completed = false;

            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }
        // Function to toggle steps when updating the password field
        $scope.passwordUpdate = function() {
            if($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid')
                steps.password.completed = true;
            else
                steps.password.completed = false;
            
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }
        // Function to toggle steps when updating the language field
        $scope.languageUpdate = function() {
            if($scope.newPatient.language)
                steps.language.completed = true;
            else
                steps.language.completed = false;
            
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }
        // Function to toggle steps when updating the security question 1 field
        $scope.securityQuestion1Update = function() {
            if($scope.validAnswer1.status == 'valid')
                steps.security1.completed = true;
            else
                steps.security1.completed = false;
            
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }
        // Function to toggle steps when updating the security question 2 field
        $scope.securityQuestion2Update = function() {
            if($scope.validAnswer2.status == 'valid')
                steps.security2.completed = true;
            else
                steps.security2.completed = false;
            
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }
        // Function to toggle steps when updating the security question 3 field
        $scope.securityQuestion3Update = function() {
            if($scope.validAnswer3.status == 'valid')
                steps.security3.completed = true;
            else
                steps.security3.completed = false;
            
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
        }

        // Function to check registration form completion 
        $scope.checkRegistrationForm = function() {

            if($scope.stepProgress == 100) 
                return true
            else
                return false
        }


        // Function to register patient
        $scope.registerPatient = function() {

            if($scope.checkRegistrationForm()){

                FB.auth().createUserWithEmailAndPassword($scope.newPatient.email,$scope.newPatient.password).then(function(userData) {

                    // on success, register to our database
                    $scope.newPatient.uid = userData.uid;
                    $scope.newPatient.SSN = $scope.validSSN.SSN;
                    $scope.newPatient.password = CryptoJS.SHA256($scope.newPatient.password).toString();
                    $scope.newPatient.securityQuestion1.answer = CryptoJS.SHA256($scope.newPatient.securityQuestion1.answer).toString();
                    $scope.newPatient.securityQuestion2.answer = CryptoJS.SHA256($scope.newPatient.securityQuestion2.answer).toString();
                    $scope.newPatient.securityQuestion3.answer = CryptoJS.SHA256($scope.newPatient.securityQuestion3.answer).toString();

                    // submit form
                    $.ajax({
                        type: "POST",
                        url: "php/patient/register_patient.php",
                        data: $scope.newPatient,
                        success: function() {
                            window.location.href = URLPATH+"main.php#/patients";
                        }
                    });


                }, function(error) {
                    // Handle errors
                    var errorCode = error.code;
                    var errorMessage = error.message;
                    if (errorCode == 'auth/email-already-in-use'){

                        $scope.validEmail.status = 'warning';
                        $scope.validEmail.message = 'Email already in use';
                        $scope.emailUpdate();
                        $scope.$apply();

                    }
                    else if (errorCode == 'auth/weak-password') {

                        $scope.validPassword.status = 'invalid';
                        $scope.validPassword.message = 'Password is too weak';
                        $scope.passwordUpdate();
                        $scope.$apply();

                    }

                    console.log(errorMessage);
               

                });

            }
        }
       
    });


