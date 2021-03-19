angular.module('opalAdmin.controllers.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        $scope.test = "Welcome";
        console.log($scope.test);
        $scope.navMenu = Session.retrieveObject('menu');

        function buildOperations() {
            $scope.updatedRole = JSON.parse(JSON.stringify($scope.toSubmit));
            var newSubmit = [];
            var noError = true;

            $scope.toSubmit.operations.forEach(function(entry) {

                sup = parseInt((+entry.delete + "" + +entry.write + "" + +entry.read), 2);
                if (sup !== 0 && sup !== 1 && sup !== 3 && sup !== 7)
                    noError = false;

                if(sup !== 0) {
                    newSubmit.push({"moduleId": entry.ID, "access": sup});
                }
            });
            $scope.updatedRole.operations = newSubmit;
            return noError;
        }

        $scope.updateSms = function() {
            if($scope.formReady && $scope.changesDetected) {
                var validResult = buildOperations();
                $.ajax({
                    type: "POST",
                    url: "sms/update/sms",
                    data: $scope.updatedRole,
                    success: function () {},
                    error: function (err) {
                        ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR_UPDATE'));
                    },
                    complete: function () {
                        $uibModalInstance.close();
                    }
                });
            }
        };
        get
        function getSmsAppointmentList() {
            smsCollectionService.getsmsAppointments().then(function (response) {
                $scope.smsAppoinments = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
            });
        }
    });

