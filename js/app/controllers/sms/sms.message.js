angular.module('opalAdmin.controllers.sms.message', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms.message', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {

        // Function to go to previous page
        $scope.goBack = function () {
            window.history.back();
        };

        $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 1)) !== 0);
        $scope.SpecialityList = null;
        $scope.TypeList = null;
        $scope.EventList = null;
        $scope.smsAppointments = null;

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

        getSmsSpecialityList();

        $scope.SpecialityUpdate = function(element){
            $scope.UpdateInformation.speciality = element.speciality;
            steps.speciality.completed = true;
            $scope.specialitySection.open = true;
            $scope.typeSection.show = true;
            $scope.numOfCompletedSteps = stepsCompleted(steps);
            $scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
            getSmsTypeList();
        }

        $scope.TypeUpdate = function(element){
            $scope.UpdateInformation.type = element.type;
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

        var arrValidationInsert = [
            $filter('translate')('SMS.VALIDATION.TYPE'),
            $filter('translate')('SMS.VALIDATION.SPECIALITY'),
            $filter('translate')('SMS.VALIDATION.EVENT'),
            $filter('translate')('SMS.VALIDATION.MESSAGE_EN'),
            $filter('translate')('SMS.VALIDATION.MESSAGE_FR')
        ];
        //Update Message information
        $scope.UpdateMessage = function(){
            if ($scope.checkForm() && $scope.writeAccess) {
                $.ajax({
                    type: "POST",
                    url: "sms/update/sms-message",
                    data:{'UpdateInformation':$scope.UpdateInformation},
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

        // Function to return boolean for form completion
        $scope.checkForm = function () {
            return (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) === 100);
        };

        //Banner
        $scope.bannerMessage = "";

        $scope.setBannerClass = function (classname) {
            // Remove any classes starting with "alert-"
            $(".bannerMessage").removeClass(function (index, css) {
                return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
            });
            // Add class
            $(".bannerMessage").addClass('alert-' + classname);
        };

        $scope.showBanner = function () {
            $(".bannerMessage").slideDown(function () {
                setTimeout(function () {
                    $(".bannerMessage").slideUp();
                }, 3000);
            });
        };

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

        //Function to get Type from database
        function getSmsTypeList(){
            smsCollectionService.getSmsType($scope.UpdateInformation.speciality).then(function (response) {
                $scope.TypeList = response.data;
            });
        }

        //Function to get Appointments from database
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
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR'));
            });
        }

        function getSmsEventList(){
            smsCollectionService.getSmsEvents($scope.UpdateInformation.type,$scope.UpdateInformation.speciality).
            then(function (response) {
                $scope.EventList = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR'));
            });
        }

        function getSmsMessage() {
            smsCollectionService.getSmsMessge($scope.UpdateInformation.speciality,
                $scope.UpdateInformation.type, $scope.UpdateInformation.event, 2).then(function (response) {
                $scope.UpdateInformation.message.English = response.data[0].smsmessage;
            }).catch(function (err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR'));
            });

            smsCollectionService.getSmsMessge($scope.UpdateInformation.speciality,
                $scope.UpdateInformation.type, $scope.UpdateInformation.event, 1).then(function (response) {
                $scope.UpdateInformation.message.French = response.data[0].smsmessage;
                console.log($scope.UpdateInformation);
            }).catch(function (err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.MESSAGE.ERROR'));
            });

        }

    });