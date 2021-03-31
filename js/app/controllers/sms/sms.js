angular.module('opalAdmin.controllers.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        $scope.navMenu = Session.retrieveObject('menu');
        $scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 0)) !== 0);
        $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 1)) !== 0);
        $scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 2)) !== 0);

        getSmsAppointmentList();

        var cellTemplateResourceName = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<strong><a href="">{{row.entity.resname}}</a></strong></div>';
        var cellTemplateAppointmentCode = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<strong><a href="">{{row.entity.appcode}}</a></strong></div>';

        var checkboxCellTemplate;
        if($scope.writeAccess)
            checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ng-click="grid.appScope.checkAliasUpdate(row.entity)" class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ng-checked="grid.appScope.updateVal(row.entity.state)" ng-model="row.entity.state"></div>';
        else
            checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents"><i ng-class="row.entity.state == 1 ? \'Active\' : \'Disabled\'" class="fa"></i></div>';

        // Banner
        $scope.bannerMessage = "";
        // Function to show page banner
        $scope.showBanner = function () {
            $(".bannerMessage").slideDown(function () {
                setTimeout(function () {
                    $(".bannerMessage").slideUp();
                }, 3000);
            });
        };

        // Function to set banner class
        $scope.setBannerClass = function (classname) {
            // Remove any classes starting with "alert-"
            $(".bannerMessage").removeClass(function (index, css) {
                return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
            });
            // Add class
            $(".bannerMessage").addClass('alert-' + classname);
        };

        $scope.filterSms = function (filterValue) {
            $scope.filterValue = filterValue;
            $scope.gridApi.grid.refresh();

        };

        $scope.filterOptions = function (renderableRows) {
            var matcher = new RegExp($scope.filterValue, 'i');
            console.log(renderableRows);
            renderableRows.forEach(function (row) {
                var match = false;
                ['appcode','resname','apptype',].forEach(function (field) {
                    if (row.entity[field].match(matcher)) {
                        match = true;
                    }
                });
                if (!match) {
                    row.visible = false;
                }
            });
            return renderableRows;
        };

        $scope.gridOptions = {
            data: 'smsAppointments',
            columnDefs: [
                {field:'appcode', displayName: 'Appointment Code',width: '30%',enableColumnMenu: false, cellTemplate: cellTemplateAppointmentCode},
                {
                    field: 'apptype', displayName: 'Appointment Type', width: '20%', enableColumnMenu: false, filter: {
                        type: uiGridConstants.filter.SELECT,
                        selectOptions: [{ value: 'GENERAL', label: 'GENERAL'}, { value: 'RADONC', label: 'RADONC' },
                            { value: 'TELEMED', label: 'TELEMED' },{value:'TEST_CENTRE',label:'TEST_CENTRE'},{value:'UNDEFINED',label:'UNDEFINED'}]
                    }
                },
                {field:'resname', displayName: 'Resource Name', width:'40%', enableColumnMenu: false,cellTemplate: cellTemplateResourceName},
                { field: 'state', displayName: 'Activation State', enableColumnMenu: false, width: '10%', cellTemplate: checkboxCellTemplate, enableFiltering: false },
            ],
            enableFiltering: true,
            enableColumnResizing: true,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            },
        };

        $scope.smsAppointments = [];
        $scope.smsUpdates = {
            updateList: []
        };

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
                    data: $scope.smsUpdates,
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

        function getSmsAppointmentList() {
            smsCollectionService.getsmsAppointments().then(function (response) {
                response.data.forEach(function (row){
                    switch (row.apptype){
                        case null:
                            row.apptype = 'UNDEFINED';
                    }
                })
                $scope.smsAppointments = response.data;
                console.log($scope.smsAppointments);
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
            });
        }
    });

