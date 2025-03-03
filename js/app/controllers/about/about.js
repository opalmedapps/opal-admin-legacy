// SPDX-FileCopyrightText: Copyright (C) 2025 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.about', [])
    .controller('about', ['$scope', '$location', '$anchorScroll',
        function($scope, $location, $anchorScroll) {
            $scope.gotoDisclaimer = function() {
                // Set the location hash to the id of the element
                $location.hash('healthcare-disclaimer');
                $anchorScroll();
            };
    }]);
