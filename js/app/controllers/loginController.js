angular.module('ATO_InterfaceApp.controllers.loginController', ['ngAnimate', 'ui.bootstrap']).


	/******************************************************************************
	* Login controller 
	*******************************************************************************/
	controller('loginController', function($scope, $rootScope/*, AUTH_EVENTS, AuthService*/) {


        // Initialize login object
        $scope.credentials = {
            username: "",
            password: ""
        }

        $scope.bannerMessage = "";
        // Function to show page banner 
        $scope.showBanner = function() {
            $(".bannerMessage").slideDown(function()  {
                setTimeout(function() {             
                    $(".bannerMessage").slideUp(); 
                }, 3000); 
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


        // Function to return boolean on completed login form
        $scope.loginFormComplete = function() {
            if( ($scope.credentials.username && $scope.credentials.password) )
                return true;
            else
                return false;
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
        // $scope.submitLogin = function (credentials) {
        //     AuthService.login(credentials).then(function (user) {
        //         $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
        //         $scope.setCurrentUser(user);
        //     }, function() {
        //         $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
        //         $scope.shakeForm();
        //     });
        // }
        $scope.submitLogin = function (credentials) {
            if($scope.loginFormComplete()) {
                $.ajax({
                    type: "POST",
                    url: "php/user/checklogin.php",
                    data: $scope.credentials,
                    success: function(response) {
                        if (response == 0) {
                            $scope.bannerMessage = "Wrong username and/or password!";
                            $scope.setBannerClass('danger');
                            $scope.shakeForm();
                            $scope.$apply();
                            $scope.showBanner();
                        }
                        if (response == 1) {
                            window.location.href = URLPATH + 'main.php#/';
                        }
                    }
                });
            }
        }

	});

