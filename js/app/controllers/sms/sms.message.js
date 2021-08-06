angular.module('opalAdmin.controllers.sms.message', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms.message', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        // Function to go to previous page
        $scope.goBack = function () {
            window.history.back();
        };

        getSmsSpecialityList();

        $scope.SpecialityList = null;
        $scope.TypeList = null;
        $scope.EventList = null;
        $scope.smsAppointments = null;
        $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 1)) !== 0);

        $scope.UpdateInformation = {
            message: {English : "", French : "",},
            speciality : "",
            type : "",
            event :"",
        }

        // Default boolean variables
        $scope.typeSection = {open:false, show:false};
        $scope.eventSection = {open:false, show:false};
        $scope.specialitySection = {open:false, show:true};
        $scope.messageSection = {open:false, show:false};

        // completed steps boolean object; used for progress bar
        var steps = {
            speciality: { completed: false },
            type: { completed: false },
            event: { completed: false },
            message: { complete: false }
        };
        // Default count of completed steps
        $scope.numOfCompletedSteps = 0;

        // Default total number of steps
        $scope.stepTotal = 4;

        // Progress for progress bar on default steps and total
        $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

        var arrValidationInsert = [
            $filter('translate')('SMS.VALIDATION.TYPE'),
            $filter('translate')('SMS.VALIDATION.SPECIALITY'),
            $filter('translate')('SMS.VALIDATION.EVENT'),
            $filter('translate')('SMS.VALIDATION.MESSAGE_EN'),
            $filter('translate')('SMS.VALIDATION.MESSAGE_FR')
        ];

        // Function to calculate / return step progress
        function trackProgress(value, total) {
            return Math.round(100 * value / total);
        }

        // Function to return number of steps completed
        function stepsCompleted(steps) {

            var numberOfTrues = 0;
            for (var step in steps) {
                if (steps[step].completed === true) {
                    numberOfTrues++;
                }
            }
            return numberOfTrues;
        }

        //Function to get Specialities from database
        function getSmsSpecialityList(){
            smsCollectionService.getSmsSpeciality().then(function (response) {
                $scope.SpecialityList = response.data;
            });
        }

        //Function to get information from database
        function getSmsTypeList(){
            smsCollectionService.getSmsType($scope.UpdateInformation.specialityCode).then(function (response) {
                $scope.TypeList = response.data;
            });
        }

        function getSmsAppointmentList() {
            smsCollectionService.getSmsAppointments().then(function (response) {
                response.data.forEach(function (row){
                    switch (row.apptype){
                        case null:
                            row.apptype = 'UNDEFINED';
                    }
                    row.modified = 0;
                })
                $scope.smsAppointments = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR_DETAILS'));
            });
        }

        function getSmsEventList(){
            smsCollectionService.getSmsEvents($scope.UpdateInformation.type,$scope.UpdateInformation.specialityName).
            then(function (response) {
                $scope.EventList = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR_DETAILS'));
            });
        }

        function getSmsMessage() {
            smsCollectionService.getSmsMessge($scope.UpdateInformation.specialityName,
                $scope.UpdateInformation.type, $scope.UpdateInformation.event, 2).then(function (response) {
                $scope.UpdateInformation.message.English = response.data[0].smsmessage;
            }).catch(function (err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR_DETAILS'));
            });

            smsCollectionService.getSmsMessge($scope.UpdateInformation.specialityName,
                $scope.UpdateInformation.type, $scope.UpdateInformation.event, 1).then(function (response) {
                $scope.UpdateInformation.message.French = response.data[0].smsmessage;
            }).catch(function (err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR_DETAILS'));
            });

        }

        //Functions to update the information selected
        $scope.SpecialityUpdate = function(element){
            $scope.UpdateInformation.specialityCode = element.specialityCode;
            $scope.UpdateInformation.specialityName = element.specialityName;
            steps.speciality.completed = true;
            $scope.specialitySection.open = true;
            $scope.typeSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            getSmsTypeList();
        }

        $scope.TypeUpdate = function(element){
            $scope.UpdateInformation.type = element;
            steps.type.completed = true;
            $scope.typeSection.open = true;
            $scope.eventSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            getSmsEventList();
        }

        $scope.EventUpdate = function(element){
            $scope.UpdateInformation.event = element.event;
            steps.event.completed = true;
            $scope.eventSection.open = true;
            $scope.messageSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            getSmsMessage();
            getSmsAppointmentList();
        }

        $scope.CheckMessage = function(){
            if($scope.UpdateInformation.message.English && $scope.UpdateInformation.message.French) {
                $scope.messageSection.open = true;
                steps.message.completed = true;
                $scope.numOfCompletedSteps = stepsCompleted(steps);
                $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            }
        }

        // Function to return boolean for form completion
        $scope.checkForm = function () {
            return (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) === 100);
        };

        //Update Message information
        $scope.UpdateMessage = function(){
            if ($scope.checkForm() && $scope.writeAccess) {
                $.ajax({
                    type: "POST",
                    url: "sms/update/sms-message",
                    data:$scope.UpdateInformation,
                    success: function () {},
                    error: function(err) {
                        err.responseText = JSON.parse(err.responseText);
                        ErrorHandler.onError(err,$filter('translate')('SMS.MESSAGE.ERROR'),arrValidationInsert);
                    },
                    complete: function () {
                        $state.go('sms');
                    }
                });
            }
        }

    });