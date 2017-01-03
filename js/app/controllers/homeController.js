angular.module('ATO_InterfaceApp.controllers.homeController', ['ngAnimate', 'ui.bootstrap']).


	/******************************************************************************
	* Home Page controller 
	*******************************************************************************/
	controller('homeController', function($scope, $uibModal, cronAPIservice) {


        $scope.banner = {
            message: "",
            alertClass: "alert-success"
        };
        // Function to show page banner 
        $scope.showBanner = function() {
            $(".bannerMessage").slideDown(function()  {
                setTimeout(function() {             
                    $(".bannerMessage").slideUp(); 
                }, 3000); 
            });
        }

        $scope.changesMade = false;

        $scope.setChangesMade = function() {
            $scope.changesMade = true;
        }
    
        $scope.loginDisplayed = true; // Defaults 
        $scope.formLoaded = false; // Defaults

        // Initialize login object
        $scope.login = {
            username: "",
            password: ""
        }

        // Initialize register object
        $scope.register = {
            username: "",
            password: "",
            passConfirm: ""
        }

        // Function to return boolean on completed login form
        $scope.loginFormComplete = function() {
            if( ($scope.login.username && $scope.login.password) )
                return true;
            else
                return false;
        }
            
        // Function to return boolean on completed register form
        $scope.registerFormComplete = function() {
            if( ($scope.register.username && $scope.register.password && $scope.register.passConfirm) )
                return true;
            else
                return false;
        }

        // Function to switch between login register forms + animations
        $scope.switchForm = function() {
            $scope.loginDisplayed = !$scope.loginDisplayed;
            $scope.formLoaded = true;
            $('.form-box').addClass('bounceIn');
            setTimeout(function() {
                $('.form-box').removeClass('bounceIn');
            }, 1000);
        }

     
        // Function to "shake" form container if fields are incorrect
        $scope.shakeForm = function() {
            $scope.formLoaded = true;
            $('.form-box').addClass('shake');
            setTimeout(function() {
                $('.form-box').removeClass('shake');
            }, 1000);
        } 
      
        // Function to submit login
        $scope.submitLogin = function () {
            if($scope.loginFormComplete()) {
                $.ajax({
                    type: "POST",
                    url: "php/user/checklogin.php",
                    data: $scope.login,
                    success: function(response) {
                        if (response == 0) {
                            $scope.banner.message = "Wrong username and/or password!";
                            $scope.banner.alertClass = 'alert-danger';
                            $scope.shakeForm();
                            $scope.$apply();
                            $scope.showBanner();
                        }
                        if (response == 1) {
                            location.reload();
                        }
                    }
                });
            }
        }

        // Function to submit register
        $scope.submitRegister = function () {
            if ($scope.registerFormComplete()) {
                $.ajax({
                    type: "POST",
                    url: "php/user/checkregister.php",
                    data: $scope.register,
                    success: function(response) {
                        console.log(response);
                        if (response == 0) {
                            $scope.banner.message = "Passwords are not the same! Try again.";
                            $scope.banner.alertClass = 'alert-danger';
                            $scope.shakeForm();
                            $scope.$apply();
                            $scope.showBanner();
                        }
                        if (response == 1) {
                            $scope.banner.message = "Register successful! You may now login.";
                            $scope.banner.alertClass = 'alert-success';
                            $scope.switchForm();
                            $scope.register.username = "";
                            $scope.register.password = "";
                            $scope.register.passConfirm = "";
                            $scope.$apply();
                            $scope.showBanner();
                        }

                    }
                });
            }
        }

	});

