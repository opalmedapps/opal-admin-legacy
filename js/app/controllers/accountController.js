angular.module('opalAdmin.controllers.accountController', ['ui.bootstrap']).


	/******************************************************************************
	* Controller for the account page
	*******************************************************************************/
	controller('accountController', function($scope, $rootScope, Session) {

		// Set current user 
		$scope.currentUser = Session.retrieveObject('user');

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

		// Initialize account object
		$scope.defaultAccount = {
			user: $scope.currentUser,
			oldPassword: null,
			password: null,
			confirmPassword: null
		}
		$scope.account = jQuery.extend(true, {}, $scope.defaultAccount);

		// Function to reset the account object
		$scope.flushAccount = function () {
			$scope.account = jQuery.extend(true, {}, $scope.defaultAccount);
            $scope.validOldPassword.status = null;
            $scope.validPassword.status = null;
            $scope.validConfirmPassword.status = null;
		}

		$scope.validOldPassword = {status:null,message:null};
		$scope.validateOldPassword = function (oldPassword) {
			if (!oldPassword) {
				$scope.validOldPassword.status = null;
				return;
			} else {
				$scope.validOldPassword.status = 'valid';
			}
		}

		// Function to validate password 
        $scope.validPassword = {status:null,message:null};
        $scope.validatePassword = function (password) {

            if (!password) {
                $scope.validPassword.status = null;
                return;
            }

            if (password.length < 6) {
                $scope.validPassword.status = 'invalid';
                $scope.validPassword.message = 'Use greater than 6 characters';
                return;
            } else if ($scope.account.oldPassword && $scope.account.oldPassword == password) {
                $scope.validPassword.status = 'warning';
                $scope.validPassword.message = 'Old and new password are the same';
            }
            else {
                $scope.validPassword.status = 'valid';
                $scope.validPassword.message = null;
            }
        }

        // Function to validate confirm password
        $scope.validConfirmPassword = {status:null,message:null};
        $scope.validateConfirmPassword = function (confirmPassword) {

            if (!confirmPassword) {
                $scope.validConfirmPassword.status = null;
                return;
            }

            if ($scope.validPassword.status != 'valid' || $scope.account.password != $scope.account.confirmPassword) {
                $scope.validConfirmPassword.status = 'invalid';
                $scope.validConfirmPassword.message = 'Enter same valid password';
                return;
            } else {
                $scope.validConfirmPassword.status = 'valid';
                $scope.validConfirmPassword.message = null;
            }
        }

        // Function to check password reset form completion
        $scope.checkForm = function() {
        	if($scope.validOldPassword.status != 'valid' || $scope.validPassword.status != 'valid' ||
        		$scope.validConfirmPassword. status != 'valid') {
        		return false;
        	} else {
        		return true;
        	}
        }

        // Function to update password
        $scope.updatePassword = function (account) {

        	if($scope.checkForm()) {

        		// check if user is defined in session (they should...)
        		if(!$scope.currentUser) {
        			$scope.bannerMessage = "Your session seems to be invalid...";
        			$scope.setBannerClass('danger');
        			$scope.showBanner();
        			return;
        		}

        		// submit form 
        		$.ajax({
        			type: "POST",
        			url: "php/user/update_password.php",
        			data: $scope.account,
        			success: function (response) {
        				response = JSON.parse(response);
        				if (response.value == 1) {
        					$scope.flushAccount();
        					$scope.setBannerClass('success');
        					$scope.bannerMessage = "Password successfully changed!";
        					$scope.showBanner();
                            $scope.$apply();
        				} else {
        					var errorCode = response.error.code;
        					var errorMessage = response.error.message;
        					if (errorCode == 'old-password-incorrect') {
        						$scope.validOldPassword.status = 'warning';
        						$scope.validOldPassword.message = errorMessage;
        						$scope.setBannerClass('warning');
        						$scope.bannerMessage = errorMessage;
        						$scope.$apply();
        						$scope.showBanner();
        					} else {
        						$scope.setBannerClass('danger');
        						$scope.bannerMessage = errorMessage;
        						$scope.showBanner();
        					}

        				}

        			}
        		});


        	}
        }


	});

