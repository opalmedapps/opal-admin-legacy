angular.module('opalAdmin.controllers.sms.message', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms.message', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {

        // Function to go to previous page
        $scope.goBack = function () {
            window.history.back();
        };

        $scope.UpdateInformation = {
            message: {English : "", French : "",},
            speciality : "",
            type : "",
            event :"",
        }

        $scope.eventList = [];

        $scope.UpdateMessage = function(){
            if ($scope.changesMade && $scope.writeAccess) {
                $.ajax({
                    type: "POST",
                    url: "sms/update/smsMessage",
                    data:$scope.UpdateInformation,
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
                ErrorHandler.onError(err, "error");
            });
        }

        function getSmsEvents(){
            smsCollectionService.getsmsEvents($scope.selected.type,$scope.selected.speciality).
            then(function (response) {
                $scope.eventList = response.data;
                console.log($scope.eventList);
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });
        }

        function getSmsMessage(){

            smsCollectionService.getsmsMessge($scope.UpdateInformation.speciality,
                $scope.UpdateInformation.type,$scope.UpdateInformation.event, "EN").
            then(function (response) {
                $scope.UpdateInformation.message.English = response.data;
                console.log($scope.UpdateInformation.message.English);
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });

            smsCollectionService.getsmsMessge($scope.UpdateInformation.speciality,
                $scope.UpdateInformation.type,$scope.UpdateInformation.event, "FR").
            then(function (response) {
                $scope.UpdateInformation.message.French = response.data;
                console.log($scope.UpdateInformation.message.French);
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });
        }
    });