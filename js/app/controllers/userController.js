angular.module('opalAdmin.controllers.userController', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	* Controller for the users page
	*******************************************************************************/
	controller('userController', function($scope, $uibModal, $filter, $sce, $state, userAPIservice) {

		// Function to go to register new user
		$scope.goToAddUser = function () {
			$state.go('user-register');
		}

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

        // Templates for the users table
        var cellTemplaeOperations = '<div style="text-align:center; padding-top: 5px;">' +
            '<strong><a href="" ng-click="grid.appScope.editUser(row.entity)">Edit</a></strong> ' + 
            '- <strong><a href="" ng-click="grid.appScope.deleteUser(row.entity)">Delete</a></strong></div>';

        // user table search textbox param
		$scope.filterOptions = function(renderableRows) {
            var matcher = new RegExp($scope.filterValue, 'i');
            renderableRows.forEach( function( row ) {
                var match = false;
                [ 'username' ].forEach(function( field ){
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

        $scope.filterUser = function(filterValue) {
            $scope.filterValue = filterValue
            $scope.gridApi.grid.refresh();
            
        };

        // Table options for user
        $scope.gridOptions = {
        	data: 'userList',
        	columnDefs: [
        		{field:'username', displayName:'Username', width:'655'},
        		{field:'role', displayName:'Role', width:'300'},
        		{name:'Operations', cellTemplate:cellTemplaeOperations, sortable:false, enableFiltering:false}
        	],
        	enableColumnResizing: true,
        	enableFiltering: true,
        	onRegisterApi: function(gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            },
        }

        // Initialize list of existing users
        $scope.userList = [];

        // Call out API service to get the list of existing users
        userAPIservice.getUsers().success(function (response) {
        	$scope.userList = response;
        });





					
	});

