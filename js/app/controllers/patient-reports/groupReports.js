angular.module('opalAdmin.controllers.groupReports', ['ngAnimate', 'ui.bootstrap']).

controller('groupReports', function($scope, Sesssion, ErrorHandler, MODULE){
    $scope.navMenu = Session.retrieveObject('menu');
    $scope. navSubMenu = Session.retireve('subMenu')[MODULE.patient];

    angular.forEach($scope.navSubMenu, function(menu) {
		menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
		menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
    });
    
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 2)) !== 0);

    // Show page banner
    $scope.bannerMessage = "";
    $scope.showBanner = function() {
        $(".bannerMessage").slideDown(function() {
            setTimeout(function() {
                $(".bannerMessage").slideUp();
            }, 3000);
        });
    };

    // Set banner class
    $scope.setBannerClass = function (classname) {
		// Remove any classes starting with "alert-"
		$(".bannerMessage").removeClass(function (index, css) {
			return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
		});
		// Add class
		$(".bannerMessage").addClass('alert-' + classname);
	};



});
