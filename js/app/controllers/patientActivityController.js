angular.module('opalAdmin.controllers.patientActivityController', ['ngAnimate', 'ui.bootstrap']).


	controller('patientActivityController', function($scope, $uibModal, patientAPIservice, uiGridConstants) {
       
       $scope.bannerMessage = "";
        // Function to show page banner 
        $scope.showBanner = function() {
            $(".bannerMessage").slideDown(function()  {
                setTimeout(function() {             
                    $(".bannerMessage").slideUp(); 
                }, 3000); 
            });
        }
        // Function to set banner class
        $scope.setBannerClass = function(classname) {
            // Remove any classes starting with "alert-" 
            $(".bannerMessage").removeClass (function (index, css) {
                return (css.match (/(^|\s)alert-\S+/g) || []).join(' ');
            });
            // Add class
            $(".bannerMessage").addClass('alert-'+classname);
        };

		// Table search textbox param
		$scope.filterOptions = function(renderableRows) {
            var matcher = new RegExp($scope.filterValue, 'i');
            renderableRows.forEach( function( row ) {
                var match = false;
                [ 'patientid', 'deviceid', 'name' ].forEach(function( field ){
                    if( row.entity[field].match(matcher) ){
                        match = true;
                    }
                });
                if( !match ){
                    row.visible = false;
                }
            });

            return renderableRows;
        };
    
        $scope.filterPatient = function(filterValue) {
            $scope.filterValue = filterValue
            $scope.gridApi.grid.refresh();
            
        };

        // Table options
        $scope.gridOptions = {
        	data: 'patientActivityList',
        	columnDefs: [
        		{field:'patientid', displayName:'Patient Id', width:'200'},
        		{field:'name', displayName:'Name', width:'355'},
        		{field:'deviceid', displayName:'Device ID', width:'530'},
        		{field:'login', displayName:'Login Time'},
        		{field:'logout', displayName:'Logout Time'}
        	],
        	//useExternalFiltering: true,
            enableFiltering: true,
        	enableColumnResizing: true,
        	onRegisterApi: function(gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            },
        }

        // Initialize list to hold patient activities
        $scope.patientActivityList = [];

        // Call our API to get the list of patient activities
        patientAPIservice.getPatientActivities().success(function (response) {
        	// Assign value
        	$scope.patientActivityList = response;
        });

	});

