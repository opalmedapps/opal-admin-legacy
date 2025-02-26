angular.module('opalAdmin.controllers.about', [])
    .controller('about', ['$scope', '$location', '$anchorScroll',
        function($scope, $location, $anchorScroll) {
            $scope.gotoDisclaimer = function() {
                // Set the location hash to the id of the element
                $location.hash('healthcare-disclaimer');
                $anchorScroll();
            };
    }]);
