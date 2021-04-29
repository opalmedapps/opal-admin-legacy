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
        $scope.smsResources = null;

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

        //Filters for resources
        $scope.resourceFilter = "";
        $scope.resourceTypeFilter = 'all';
        $scope.resourceUpdate = 0;

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

        //Update Message information
        $scope.UpdateMessage = function(){
            if($scope.resourceUpdate && $scope.writeAccess){

            }
            if ($scope.checkForm() && $scope.writeAccess) {
                $.ajax({
                    type: "POST",
                    url: "sms/update/smsMessage",
                    data:{'UpdateInformation':$scope.UpdateInformation},
                    success: function (response) {
                        response = JSON.parse(response);
                        // Show success or failure depending on response
                        if (response) {
                            $scope.setBannerClass('success');
                            $scope.bannerMessage = "success";
                            $scope.showBanner();
                        }
                        else {
                            console.log("error");
                            ErrorHandler.onError(response, "error");
                        }
                        $scope.goBack();
                    },
                    error: function(err) {
                        ErrorHandler.onError(err,"error");
                    }
                });
            }
        }

        // Function to return boolean for form completion
        $scope.checkForm = function () {
            return (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) === 100);
        };

        //Filter functions
        $scope.changeResourceFilter = function (field) {
            $scope.resourceFilter = field;
        };

        $scope.searchResourcesFilter = function (Filter) {
            var keyword = new RegExp($scope.resourceFilter, 'i');
            return ((!$scope.resourceTypeFilter || keyword.test(Filter.name)) && (($scope.resourceTypeFilter == 'all') || ($scope.resourceTypeFilter == 'selected' && Filter.selected)
                || ($scope.resourceTypeFilter == 'other' && !Filter.selected)));
        };

        $scope.setResourceTypeFilter = function (filter) {
            $scope.resourceTypeFilter = filter;
        };

        //checkbox function for resource
        $scope.toggleResourceSelection = function (resource) {
            $scope.resourceUpdate = 1;
            resource.modified = 1;
            // If originally active, remove it
            if (resource.state) {
                resource.state = 0; // added parameter
            } else { // Originally not active, add it
                resource.state = 1;
            }
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
                var resourceSerList = [];
                response.data.forEach(function (row){
                    switch (row.apptype){
                        case null:
                            row.apptype = 'UNDEFINED';
                    }
                    row.modified = 0;
                    if(!resourceSerList.includes(row.ressernum)){
                        resourceSerList.push(row.ressernum);
                    }
                })
                $scope.smsResources = GenerateResourceList(resourceSerList,response.data);
                $scope.smsAppointments = response.data;
                console.log($scope.smsAppointments);
                console.log($scope.smsResources)
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });
        }

        function getSmsEventList(){
            smsCollectionService.getSmsEvents($scope.UpdateInformation.type,$scope.UpdateInformation.speciality).
            then(function (response) {
                $scope.EventList = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });
        }

        function getSmsMessage(){
            smsCollectionService.getSmsMessge($scope.UpdateInformation.speciality,
                $scope.UpdateInformation.type,$scope.UpdateInformation.event, "English").
            then(function (response) {
                $scope.UpdateInformation.message.English = response.data.message;
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });

            smsCollectionService.getSmsMessge($scope.UpdateInformation.speciality,
                $scope.UpdateInformation.type,$scope.UpdateInformation.event, "French").
            then(function (response) {
                $scope.UpdateInformation.message.French = response.data.message;
            }).catch(function(err) {
                ErrorHandler.onError(err, "error");
            });

        }

        function GenerateResourceList(codeList,Appointment){
            var resourceList = []
            codeList.forEach(function(serNum){
                var resource = {ressernum: serNum, name: "", selected: 0, state: 1,modified: 0};
                Appointment.forEach(function(appointment){
                    if(appointment.ressernum == resource.ressernum){
                        if(appointment.spec == $scope.UpdateInformation.speciality&& appointment.apptype == $scope.UpdateInformation.type){
                            resource.selected = 1;
                        }
                        if(appointment.state == 0){
                            resource.state = 0;
                        }
                        if(resource.name == ""){
                            resource.name = appointment.resname;
                        }
                    }
                });
                resourceList.push(resource);
            });
            return resourceList;
        }


    });