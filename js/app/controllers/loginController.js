angular.module('opalAdmin.controllers.loginController', ['ngAnimate', 'ui.bootstrap']).


	/******************************************************************************
	* Login controller 
	*******************************************************************************/
	controller('loginController', function($scope, $rootScope, $state, AUTH_EVENTS, AuthService, Idle) {

        // Initialize login object
        $scope.credentials = {
            username: "",
            password: ""
        };

        $scope.bannerMessage = "";
        // Function to show page banner 
        $scope.showBanner = function() {
            $(".bannerMessage").slideDown(function()  {
                setTimeout(function() {             
                    $(".bannerMessage").slideUp(); 
                }, 3000); 
            });
        };
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
        };
            
        // Function to "shake" form container if fields are incorrect
        $scope.shakeForm = function() {
            $scope.formLoaded = true;
            $('.form-box').addClass('shake');
            setTimeout(function() {
                $('.form-box').removeClass('shake');
            }, 1000);
        }; 
      
        $scope.submitLogin = function (credentials) {
            if($scope.loginFormComplete()) {
                AuthService.login(credentials).then(function (user) {
                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                    $state.go('home');
                    Idle.watch();
                }, function() {
                    $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                    $scope.bannerMessage = "Wrong username and/or password!";
                    $scope.setBannerClass('danger');
                    $scope.shakeForm();
                    $scope.showBanner();
                });
            }
        };

	});

