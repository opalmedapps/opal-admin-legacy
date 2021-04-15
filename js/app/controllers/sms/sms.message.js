angular.module('opalAdmin.controllers.sms.message', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms.message', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {

        // Function to go to previous page
        $scope.goBack = function () {
            window.history.back();
        };

        $scope.message = {
            en : "",
            fr : "",
        }
        $scope.selected = {
            speciality : "",
            type : "",
            event :"",
        }

        $scope.UpdateMessage = function(){
            if ($scope.changesMade && $scope.writeAccess) {
                $.ajax({
                    type: "POST",
                    url: "sms/update/smsMessage",
                    data:"",
                    success: function (response) {
                        getSmsAppointmentList();
                        response = JSON.parse(response);
                        // Show success or failure depending on response
                        if (response) {
                            $scope.setBannerClass('success');
                            $scope.bannerMessage = "success";
                            $scope.showBanner();
                        }
                        else {
                            ErrorHandler.onError(response, "error");
                        }
                    },
                    error: function(err) {
                        ErrorHandler.onError(err,"error");
                    }
                });
            }
        }

        function getSmsAppointmentList() {
            smsCollectionService.getsmsAppointments().then(function (response) {
                response.data.forEach(function (row){
                    switch (row.apptype){
                        case null:
                            row.apptype = 'UNDEFINED';
                    }
                    row.modified = 0;
                })
                $scope.smsAppointments = response.data;
                console.log($scope.smsAppointments);
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
            });
        }
    });