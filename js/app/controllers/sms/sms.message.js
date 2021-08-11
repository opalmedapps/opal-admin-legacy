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
            $filter('translate')('SMS.VALIDATION.MESSAGE_ID'),
            $filter('translate')('SMS.VALIDATION.MESSAGE')
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

        //Function to get reset type section
        function resetType(){
            $scope.UpdateInformation.type = "";
            steps.type.completed = false;
            $scope.typeSection.open = false;
            $scope.eventSection.show = false;
        }

        //Function to get reset event section
        function resetEvent(){
            $scope.UpdateInformation.event = "";
            steps.event.completed = false;
            $scope.eventSection.open = false;
            $scope.messageSection.show = false;
        }

        //Function to get reset message section
        function resetMessage(){
            $scope.messageSection.open = false;
            steps.message.completed = false;
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

        //Function to get event and message list from database
        function getSmsEventList(){
            smsCollectionService.getSmsMessages($scope.UpdateInformation.type,$scope.UpdateInformation.specialityCode).
            then(function (response) {
                $scope.EventList = [];
                response.data.forEach(function (row){
                    if(!$scope.EventList.includes(row.event)) $scope.EventList.push(row.event);
                });
                $scope.MessageList = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR_DETAILS'));
            });
        }

        //Function to update the speciality selected
        $scope.SpecialityUpdate = function(element){
            if (element.specialityCode != $scope.UpdateInformation.specialityCode){
                resetType();
                resetEvent();
                resetMessage();
            }
            $scope.UpdateInformation.specialityCode = element.specialityCode;
            $scope.UpdateInformation.specialityName = element.specialityName;
            steps.speciality.completed = true;
            $scope.specialitySection.open = true;
            $scope.typeSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            getSmsTypeList();
        }

        //Function to update the type selected
        $scope.TypeUpdate = function(element){
            if (element != $scope.UpdateInformation.type){
                resetEvent();
                resetMessage();
            }
            $scope.UpdateInformation.type = element;
            steps.type.completed = true;
            $scope.typeSection.open = true;
            $scope.eventSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            getSmsEventList();
        }

        //Function to update the event selected
        $scope.EventUpdate = function(element){
            if (element != $scope.UpdateInformation.event){
                resetMessage();
            }
            $scope.UpdateInformation.event = element;
            steps.event.completed = true;
            $scope.eventSection.open = true;
            $scope.messageSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            $scope.UpdateInformation.message.French = $scope.MessageList.filter(x => x.event == $scope.UpdateInformation.event && x.language == "French")[0];
            $scope.UpdateInformation.message.English = $scope.MessageList.filter(x => x.event == $scope.UpdateInformation.event && x.language == "English")[0];
        }

        //Function to check the changes on message
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
            var update = [
                {
                    messageId: $scope.UpdateInformation.message.English.messageId,
                    smsMessage: $scope.UpdateInformation.message.English.smsMessage
                },
                {
                    messageId: $scope.UpdateInformation.message.French.messageId,
                    smsMessage: $scope.UpdateInformation.message.French.smsMessage
                },
                ]
            if ($scope.checkForm() && $scope.writeAccess) {
                $.ajax({
                    type: "POST",
                    url: "sms/update/sms-message",
                    data: {updateList: update},
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