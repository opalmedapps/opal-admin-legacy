angular.module('ATO_InterfaceApp.controllers.patientRegistrationController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Patient Registration Page controller 
	*******************************************************************************/
	controller('patientRegistrationController', function($scope, $filter, $sce, $uibModal, patientAPIservice) {

        // Function to go to previous page
        $scope.goBack = function() {
            window.history.back();
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
        $scope.newPatient = {
            email: null,
            emailConfirm: null,
            password: null,
            passwordConfirm: null,
            language: ''
        }


        // Keep track of SSN status and input
        $scope.checkedSSN = {
            status: null,
            SSN: null
        };

        $scope.checkSSN = function() {

            if($scope.checkedSSN.SSN.length < 12) {
                $scope.checkedSSN.status = 'invalid' // input not long enough
                $scope.setBannerClass('danger');
                $scope.bannerMessage = "Length of field is too small. Please enter a 12-character Medicare card number to start the registration process.";
                $scope.showBanner();
                return;
            }

            // Call our API service to find patient 
            patientAPIservice.findPatient($scope.checkedSSN.SSN).success(function(response) {

                if(response.status == 'PatientAlreadyRegistered') {
                    $scope.checkedSSN.status = 'warning';
                    $scope.setBannerClass('warning');
                    $scope.bannerMessage = "Patient already registered to use Opal!";
                    $scope.showBanner();
                }
                else if(response.status == 'PatientNotFound') {
                    $scope.checkedSSN.status = 'invalid';
                    $scope.setBannerClass('danger')
                    $scope.bannerMessage = "No patient found with SSN: " + $scope.checkedSSN.SSN;
                    $scope.showBanner();
                }
                else if(response.status == 'Error') {
                    $scope.checkedSSN.status = 'invalid';
                    $scope.setBannerClass('danger');
                    $scope.bannerMessage = response.message;
                    $scope.showBanner();
                }
                else {
                    $scope.checkedSSN.status = 'valid';

                }

            })
            

        }

        // Function to validate email address
        $scope.validEmail = false;
        $scope.validateEmail = function (email) {

            // regex
            var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
            $scope.validEmail = re.test(email);
            return re.test(email);
        }

        // Initialize a list of languages available
        $scope.languages = [{
            name: 'English',
            id: 'EN'
        }, {
            name: 'French',
            id: 'FR'
        }];

    });


